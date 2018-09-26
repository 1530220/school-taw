<?php
/**
 * @version    $Id$
 * @package    JSN_Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Handle Ajax requests related to customer account.
 *
 * @package  JSN_Framework
 * @since    2.0.3
 */
class JSNAjaxAccount extends JSNAjax
{

	const GET_TOKEN_URL = 'https://www.joomlashine.com/index.php?option=com_lightcart&view=token&task=token.gettoken';

	const GET_LICENSE_URL = 'https://www.joomlashine.com/index.php?option=com_lightcart&view=authenticationapi&task=authenticationapi.getEdition&tmpl=component';

	const TRY_PRO_URL = 'https://www.joomlashine.com/index.php?option=com_lightcart&view=authenticationapi&task=authenticationapi.createTrialOrder&tmpl=component';

	/**
	 * Get current info.
	 *
	 * @throws  Exception
	 * @return  void
	 */
	public function getInfoAction()
	{
		// Query for existing user account.
		$this->dbo->setQuery(
			$this->dbo->getQuery(true)
				->select('element, params')
				->from('#__extensions')
				->where('manifest_cache LIKE "%JoomlaShine%"')
				->where('params LIKE "%username%"')
				->where('params LIKE "%token%"'));

		foreach ($this->dbo->loadObjectList() as $tpl)
		{
			if (( $params = json_decode($tpl->params) ) && !empty($params->username))
			{
				$accounts[$params->username] = array(
					'label' => $params->username,
					'value' => $tpl->element
				);
			}
		}

		// Get extension parameters.
		if (empty($this->extension))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_PARAMETERS'));
		}

		$params = self::getExtensionParams('component', $this->extension);

		$this->setResponse(
			array(
				'token' => isset($params['token']) ? $params['token'] : '',
				'username' => isset($params['username']) ? $params['username'] : '',
				'accounts' => array_values($accounts),
				'license' => $this->getLicenseAction(true)
			));
	}

	public function getTokenAction()
	{
		// Get request parameters.
		$username = $this->input->getString('username');
		$password = $this->input->getString('password');

		// Verify parameters.
		if (empty($this->extension) || empty($username) || empty($password))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_PARAMETERS'));
		}

		// Prepare data.
		$domain = JUri::getInstance()->toString(array(
			'host'
		));
		$random = self::genRandomString();
		$secret = md5($random . $domain);

		// Send request.
		$http = new JHttp();
		$data = $http->post(self::GET_TOKEN_URL,
			array(
				'domain' => $domain,
				'username' => $username,
				'password' => $password,
				'rand_code' => $random,
				'secret_key' => $secret
			));

		// Parse result.
		$result = json_decode($data->body);

		if (empty($result))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_FAILED_TO_GET_RESPONSE_FROM_JSN_SERVER'));
		}

		if ((string) $result->result == 'error')
		{
			$key = 'JSN_EXTFW_LIGHTCART_' . strtoupper($result->error_code ? $result->error_code : $result->message);
			$msg = JText::_($key);

			if ($msg == $key)
			{
				$msg = $result->message;
			}

			throw new Exception($msg);
		}

		// Store token key.
		if (!self::updateExtensionParams(array(
			'username' => $username,
			'token' => $result->token
		), 'component', $this->extension))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_FAILED_TO_STORE_TOKEN_TO_DATABASE'));
		}

		// Clear cached license data.
		$cache = JFactory::getConfig()->get('tmp_path') . "/{$this->extension}/license.data";

		if (is_file($cache))
		{
			unlink($cache);
		}

		$this->setResponse($result->token);
	}

	public function copyTokenAction()
	{
		// Get request parameters.
		$from = $this->input->getString('from');

		// Verify parameters.
		if (empty($this->extension) || empty($from))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_PARAMETERS'));
		}

		// Detect extension type.
		$prefix = substr($from, 0, 4);

		if ($prefix == 'com_')
		{
			$type = 'component';
		}
		elseif ($prefix == 'mod_')
		{
			$type = 'module';
		}
		else
		{
			$type = 'template';
		}

		// Get token data from the specified extension.
		$params = self::getExtensionParams($type, $from);

		if (empty($params['username']) || empty($params['token']))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_PARAMETERS'));
		}

		// Store token key.
		if (!self::updateExtensionParams(array(
			'username' => $params['username'],
			'token' => $params['token']
		), 'component', $this->extension))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_FAILED_TO_STORE_TOKEN_TO_DATABASE'));
		}

		// Clear cached license data.
		$cache = JFactory::getConfig()->get('tmp_path') . "/{$this->extension}/license.data";

		if (is_file($cache))
		{
			unlink($cache);
		}

		$this->setResponse($params['token']);
	}

	public function getLicenseAction($return = true)
	{
		// Verify parameters.
		if (empty($this->extension))
		{
			if ($return)
			{
				return null;
			}

			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_PARAMETERS'));
		}

		// Get extension parameters.
		$params = self::getExtensionParams('component', $this->extension);

		// Get token.
		if (!empty($params['token']))
		{
			$token = $params['token'];
		}

		if (empty($token))
		{
			if ($return)
			{
				return null;
			}

			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_TOKEN_KEY'));
		}

		// Look for license data in the temporary directory first.
		$cache = JFactory::getConfig()->get('tmp_path') . "/{$this->extension}/license.data";

		if (is_file($cache) && ( time() - filemtime($cache) < 24 * 60 * 60 ) && ( $license = file_get_contents($cache) ) != '')
		{
			return $license;
		}

		// Instantiate a HTTP client.
		$http = new JHttp();
		$link = self::GET_LICENSE_URL;

		// Build URL for requesting license data.
		$link .= '&identified_name=' . JSNUtilsText::getConstant('IDENTIFIED_NAME', $this->extension);
		$link .= '&domain=' . JUri::getInstance()->toString(array(
			'host'
		));
		$link .= '&ip=' . $_SERVER['SERVER_ADDR'];
		$link .= '&token=' . $token;

		// Send a request to JoomlaShine server to get license data.
		try
		{
			$result = $http->get($link);

			// Prepare body.
			if (strpos($result->body, 'HTTP/') === 0)
			{
				$result->body = explode('{', $result->body, 2);
				$result->body = '{' . $result->body[1];
			}

			// Parse response.
			$result = json_decode($result->body);

			if ($result && $result->result == 'success')
			{
				// Cache license data to a local file.
				file_put_contents($cache, $result->message);

				if ($return)
				{
					return $result->message;
				}

				$this->setResponse($result->message);
			}
			elseif (!$result || $result->result == 'failure')
			{
				if ($return)
				{
					return null;
				}

				if ($result)
				{
					$key = 'JSN_EXTFW_LIGHTCART_' . strtoupper($result->error_code ? $result->error_code : $result->message);
					$msg = JText::_($key);

					if ($msg == $key)
					{
						$msg = $result->message;
					}
				}

				throw new Exception($result ? $msg : json_last_error_msg());
			}
		}
		catch (Exception $e)
		{
			// Reuse cache file if available.
			if (is_file($cache) && ( $license = file_get_contents($cache) ) != '')
			{
				// Refresh cache file after 1 day.
				touch($cache);

				if ($return)
				{
					return $license;
				}

				$this->setResponse($license);
			}
			else
			{
				if ($return)
				{
					return null;
				}

				throw $e;
			}
		}
	}

	public function tryProAction()
	{
		// Verify parameters.
		if (empty($this->extension))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_PARAMETERS'));
		}

		// Get extension parameters.
		$params = self::getExtensionParams('component', $this->extension);

		// Get token.
		if (!empty($params['token']))
		{
			$token = $params['token'];
		}

		if (empty($token))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_TOKEN_KEY'));
		}

		// Instantiate a HTTP client.
		$http = new JHttp();
		$link = self::TRY_PRO_URL;

		// Build URL for requesting license data.
		$link .= '&identified_name=' . JSNUtilsText::getConstant('IDENTIFIED_NAME', $this->extension);
		$link .= '&domain=' . JUri::getInstance()->toString(array(
			'host'
		));
		$link .= '&ip=' . $_SERVER['SERVER_ADDR'];
		$link .= '&token=' . $token;

		// Send a request to JoomlaShine server to register Trial license.
		$result = $http->get($link);

		// Parse response.
		$result = json_decode($result->body);

		if ($result && $result->result == 'success')
		{
			// Clear cached license data.
			$cache = JFactory::getConfig()->get('tmp_path') . "/{$this->extension}/license.data";

			if (is_file($cache))
			{
				unlink($cache);
			}

			// Get new license data.
			$this->getLicenseAction();
		}
		elseif (!$result || $result->result == 'failure')
		{
			if ($result)
			{
				$key = 'JSN_EXTFW_LIGHTCART_' . strtoupper($result->error_code ? $result->error_code : $result->message);
				$msg = JText::_($key);

				if ($msg == $key)
				{
					$msg = $result->message;
				}
			}

			throw new Exception($result ? $msg : json_last_error_msg());
		}
	}

	public function buyProAction()
	{
		// Verify parameters.
		if (empty($this->extension))
		{
			throw new Exception(JText::_('JSN_EXTFW_LIGHTCART_API_INVALID_PARAMETERS'));
		}

		// Clear cached license data.
		$cache = JFactory::getConfig()->get('tmp_path') . "/{$this->extension}/license.data";

		if (is_file($cache))
		{
			unlink($cache);
		}

		// Redirect to extension introduction page at JoomlaShine website.
		$this->app->redirect(JSNUtilsText::getConstant('BUY_LINK', $this->extension));
	}

	/**
	 * Generate a randon string.
	 *
	 * @return  string
	 */
	protected static function genRandomString()
	{
		$length = 4;
		$chars = 'abcdefghijklmnopqrstuvwxyz';
		$chars_length = ( strlen($chars) - 1 );
		$string = $chars{rand(0, $chars_length)};

		for ($i = 1; $i < $length; $i = strlen($string))
		{
			$r = $chars{rand(0, $chars_length)};

			if ($r != $string{$i - 1})
			{
				$string .= $r;
			}
		}

		$fullString = dechex(time() + mt_rand(0, 10000000)) . $string;
		$result = strtoupper(substr($fullString, 2, 10));

		return $result;
	}

	/**
	 * Get extension parameters stored in the 'extensions' table.
	 *
	 * @param   string  $type     Either 'component', 'module', 'plugin' or 'template'.
	 * @param   string  $element  Extension's element name.
	 * @param   string  $group    Plugin group, required for 'plugin'.
	 *
	 * @return  array
	 */
	protected static function getExtensionParams($type, $element, $group = '')
	{
		$dbo = JFactory::getDbo();
		$qry = $dbo->getQuery(true)
			->select('params')
			->from('#__extensions')
			->where('type = ' . $dbo->quote($type))
			->where('element = ' . $dbo->quote($element));

		if ('plugin' == $type)
		{
			$qry->where('folder = ' . $dbo->quote($group));
		}

		$dbo->setQuery($qry);

		if (!( $params = json_decode($dbo->loadResult(), true) ))
		{
			$params = array();
		}

		return $params;
	}

	/**
	 * Update extension parameters stored in the 'extensions' table.
	 *
	 * @param   array   $params   Array of parameters.
	 * @param   string  $type     Either 'component', 'module', 'plugin' or 'template'.
	 * @param   string  $element  Extension's element name.
	 * @param   string  $group    Plugin group, required for 'plugin'.
	 *
	 * @return  boolean
	 */
	protected static function updateExtensionParams($params, $type, $element, $group = '')
	{
		// Get current extension params.
		$curParams = self::getExtensionParams($type, $element, $group);

		// Then merge with new params.
		$params = array_merge($curParams, $params);

		// Store to database.
		$dbo = JFactory::getDbo();
		$qry = $dbo->getQuery(true)
			->update('#__extensions')
			->set('params = ' . $dbo->quote(json_encode($params)))
			->where('type = ' . $dbo->quote($type))
			->where('element = ' . $dbo->quote($element));

		if ('plugin' == $type)
		{
			$qry->where('folder = ' . $q->quote($group));
		}

		$dbo->setQuery($qry);

		if ($dbo->execute())
		{
			return true;
		}

		return false;
	}
}
