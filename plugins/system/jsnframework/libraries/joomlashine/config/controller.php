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
 * Controller class of JSN Config library.
 *
 * To implement <b>JSNConfigController</b> class, create a controller file
 * in <b>administrator/components/com_YourComponentName/controllers</b> folder
 * then put following code into that file:
 *
 * <code>class YourComponentPrefixControllerConfig extends JSNConfigController
 * {
 * }</code>
 *
 * The <b>JSNConfigController</b> class pre-defines <b>save</b> method for
 * validating then saving configuration data. So, you <b>DO NOT NEED</b> to
 * re-define that method in your controller class.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class JSNConfigController extends JSNBaseController
{
	/**
	 * Validate then save configuration data.
	 *
	 * @return  void
	 */
	public function save()
	{
		// Check token
		JSession::checkToken() or die( 'Invalid Token' );
		// Get input object
		$input = JFactory::getApplication()->input;

		// Validate request
		$this->initializeRequest($input);

		// Initialize variables
		$this->model	= $this->getModel($input->getCmd('controller') ? $input->getCmd('controller') : $input->getCmd('view'));
		$config			= $this->model->getForm();
		$data			= $input->get('jsnconfig', array(), 'array');

		// Attempt to save the configuration
		$return = true;

		try
		{
			$this->model->save($config, $data, true);
		}
		catch (Exception $e)
		{
			$return = $e;
		}

		// Complete request
		$this->finalizeRequest($return, $input);
	}

	/**
	 * Verify Token key
	 *
	 * @return  json type
	 */
	public function verifyToken()
	{
		// Check token
		JSession::checkToken('get') or die( 'Invalid Token' );

		require_once JPATH_ROOT . '/plugins/system/jsnframework/libraries/joomlashine/client/client.php';

		// Get input object
		$input = JFactory::getApplication()->input;
		$token = $input->getCmd('token', '');

		if ($token == '')
		{
			exit(json_encode(array('message' => JText::_('JSN_EXTFW_CONFIG_TOKEN_CANNOT_BE_BLANK'), 'result' => 'error')));
		}

		$isAllowedUser = JFactory::getUser()->authorise('core.admin');

		if (!$isAllowedUser)
		{
			exit(json_encode(array('message' => JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 'result' => 'error')));
		}

		$randCode		= $this->createRandCode();
		$domain			= JURI::root();
		preg_match('@^(?:http://www\.|http://|www\.|http:|https://www\.|https://|www\.|https:)?([^/]+)@i', $domain, $domainFilter);
		$domain 		= $domainFilter[1];

		$secretKey 		= md5($randCode . $domain);

		$query 		= array();
		$query[] 	= 'rand_code=' . urlencode($randCode);
		$query[] 	= 'domain=' . urlencode($domain);
		$query[] 	= 'secret_key=' . urlencode($secretKey);
		$query[] 	= 'token=' . urlencode($token);
		$url 		= JSN_EXT_TOKEN_CHECK_URL . '&' . implode('&', $query);

		// Get results
		try
		{
			$result = JSNUtilsHttp::get($url);

			// JSON-decode the result
			$result = json_decode($result['body']);

			if (is_null($result))
			{
				exit(json_encode(array('message' => JText::_('JSN_EXTFW_CONFIG_TOKEN_CHECK_FAIL'), 'result' => 'error')));
			}

			if ((string) $result->result == 'error')
			{
				exit(json_encode(array('message' => JText::_('JSN_EXTFW_LIGHTCART_ERROR_' . $result->message), 'result' => 'error')));
			}

			try
			{
				// Post client information
				JSNClientInformation::postClientInformation($token);
			}
			catch (Exception $e)
			{
				exit(json_encode(array('message' => JText::_('JSN_EXTFW_CONFIG_TOKEN_IS_VALID'), 'result' => 'success')));
			}

			exit(json_encode(array('message' => JText::_('JSN_EXTFW_CONFIG_TOKEN_IS_VALID'), 'result' => 'success')));
		}
		catch (Exception $e)
		{
			exit(json_encode(array('message' => JText::_('JSN_EXTFW_CONFIG_TOKEN_CHECK_FAIL'), 'type' => 'error')));
		}
	}

	/**
	 * Create rand code
	 *
	 * @return string
	 */
	public function createRandCode()
	{
		$length			= 8;
		$chars		  	= 'abcdefghijklmnopqrstuvwxyz';
		$chars_length 	= (strlen($chars) - 1);
		$string			= $chars{rand(0, $chars_length)};
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			$r = $chars{rand(0, $chars_length)};
			if ($r != $string{$i - 1})
			{
				$string .= $r;
			}
		}

		$fullString = dechex(time() + mt_rand(0, 10000000)) . $string;
		$result	  = strtolower(substr($fullString, 2, 10));
		return $result;
	}

	/**
	 * Verify Token key
	 *
	 * @return  json type
	 */
	public function getToken()
	{
		// Check token
		JSession::checkToken('get') or die( 'Invalid Token' );

		// Get input object
		$input = JFactory::getApplication()->input;

		$method = $input->getMethod();

		// Checking customer information
		$username = $input->getUsername('username', '');
		$password = $input->$method->get('password', '', 'RAW');

		if ($username == '' || $password == '')
		{
			exit(json_encode(array('message' => JText::_('JSN_EXTFW_LIGHTCART_ERROR_TOKEN_ERR01'), 'result' => 'error')));
		}

		$isAllowedUser = JFactory::getUser()->authorise('core.admin');

		if (!$isAllowedUser)
		{
			exit(json_encode(array('message' => JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), 'result' => 'error')));
		}


		$randCode		= $this->createRandCode();
		$domain			= JURI::root();

		preg_match('@^(?:http://www\.|http://|www\.|http:|https://www\.|https://|www\.|https:)?([^/]+)@i', $domain, $domainFilter);
		$domain 		= $domainFilter[1];
		$secretKey 		= md5($randCode . $domain);
		$query 			= array();

		$query['rand_code'] 	= $randCode;
		$query['domain'] 		= $domain;
		$query['secret_key'] 	= $secretKey;
		$query['username'] 		= $username;
		$query['password'] 		= $password;

		$url						= JSN_EXT_GET_TOKEN_URL;
		$arguments					= array();
		$arguments["RequestMethod"] = "POST";
		$arguments["PostValues"] 	= $query;
		// Get results
		try
		{
			$result = JSNUtilsHttp::getWithOption($url, '', false, $arguments);

			// JSON-decode the result
			$result = json_decode($result['body']);

			if (is_null($result))
			{
				exit(json_encode(array('message' => JText::_('JSN_EXTFW_ERROR_FAILED_TO_CONNECT_OUR_SERVER'), 'result' => 'error')));
			}

			if ((string) $result->result == 'error')
			{
				exit(json_encode(array('message' => JText::_('JSN_EXTFW_LIGHTCART_ERROR_' . $result->message), 'result' => 'error')));
			}

			require_once JPATH_ROOT . '/plugins/system/jsnframework/libraries/joomlashine/client/client.php';

			try
			{
				// Post client information
				JSNClientInformation::postClientInformation($result->token);
			}
			catch (Exception $e)
			{
				exit(json_encode(array('message' => JText::_('JSN_EXTFW_CONFIG_TOKEN_IS_VALID'), 'result' => 'success', 'token' => $result->token)));
			}

			exit(json_encode(array('message' => JText::_('JSN_EXTFW_CONFIG_TOKEN_IS_VALID'), 'result' => 'success', 'token' => $result->token)));
		}
		catch (Exception $e)
		{
			exit(json_encode(array('message' => JText::_('JSN_EXTFW_ERROR_FAILED_TO_CONNECT_OUR_SERVER'), 'type' => 'error')));
		}
	}
}
