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
 * Handle Ajax requests.
 *
 * @package  JSN_Framework
 * @since    2.0.3
 */
class JSNAjax
{

	/**
	 * Joomla application object.
	 *
	 * @var  JApplicationCms
	 */
	protected $app;

	/**
	 * Joomla database object.
	 *
	 * @var  JDatabaseDriver
	 */
	protected $dbo;

	/**
	 * Input object.
	 *
	 * @var JInput
	 */
	protected $input;

	/**
	 * Affected extension.
	 *
	 * @var array
	 */
	protected $extension;

	/**
	 * Base Ajax URL.
	 *
	 * @var array
	 */
	protected $baseUrl;

	/**
	 * Response content.
	 *
	 * @var mixed
	 */
	protected $responseContent;

	/**
	 * Execute the requested Ajax action.
	 *
	 * @return  boolean
	 */
	public static function execute()
	{
		// Get Joomla's application instance.
		$app = JFactory::getApplication();

		// Prepare to execute Ajax action.
		$context = $app->input->getCmd('context', '');
		$action = $app->input->getCmd('action', 'index');
		$format = $app->input->getCmd('format', 'json');
		$extension = $app->input->getCmd('extension', null);

		if (empty($action))
		{
			throw new Exception(JText::_('JSN_EXTFW_AJAX_INVALID_PARAMETERS'));
		}

		try
		{
			// Verify token.
			if (!JSession::checkToken('get'))
			{
				throw new Exception(JText::_('JSN_EXTFW_AJAX_INVALID_TOKEN'));
			}

			// Verify user permission.
			$isAdmin = ( ( method_exists($app, 'isAdmin') && $app->isAdmin() ) ||
				 ( method_exists($app, 'isClient') && $app->isClient('administrator') ) );

			if ($isAdmin && !empty($_SERVER['HTTP_REFERER']))
			{
				$referer = explode('/index.php?', $_SERVER['HTTP_REFERER']);

				parse_str($referer[1], $referer);

				if ($referer['option'] != 'com_ajax' && !JFactory::getUser()->authorise('core.manage', $referer['option']))
				{
					// Set 403 header.
					header('HTTP/1.1 403 Forbidden');

					throw new Exception('JERROR_ALERTNOAUTHOR');
				}
			}

			// Generate context class.
			$contextClass = 'JSNAjax' . str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $context)));

			if (!class_exists($contextClass))
			{
				throw new Exception(JText::sprintf('JSN_EXTFW_AJAX_INVALID_CONTEXT', $context));
			}

			// Create a new instance of the request context.
			$contextObject = new $contextClass();

			// Generate method name.
			$method = str_replace('-', '', $action) . 'Action';

			if (method_exists($contextObject, $method))
			{
				call_user_func(array(
					$contextObject,
					$method
				));
			}
			elseif (method_exists($contextObject, 'invoke'))
			{
				call_user_func(array(
					$contextObject,
					'invoke'
				), $action);
			}
			else
			{
				throw new Exception(JText::sprintf('JSN_EXTFW_AJAX_INVALID_ACTION', $action));
			}

			// Send response back.
			if ($format != 'json')
			{
				echo $contextObject->getResponse();
			}
			else
			{
				header('Content-Type: application/json');

				echo json_encode(array(
					'type' => 'success',
					'data' => $contextObject->getResponse()
				));
			}
		}
		catch (Exception $e)
		{
			if ($format != 'json')
			{
				echo $e->getMessage();
			}
			else
			{
				header('Content-Type: application/json');

				echo json_encode(array(
					'type' => 'error',
					'data' => $e->getMessage()
				));
			}
		}

		return true;
	}

	/**
	 * Constructor.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		// Get necessary objects.
		$this->app = JFactory::getApplication();
		$this->dbo = JFactory::getDBO();
		$this->input = $this->app->input;
		$this->extension = $this->input->getCmd('extension');

		// Build base Ajax URL.
		$this->baseUrl = "index.php?option=com_ajax&plugin=jsnframework&extension={$this->extension}" . '&context=' .
			 strtolower(preg_replace('/([a-z])([A-Z])/', '\\1-\\2', substr(get_class($this), 7))) . '&format=' .
			 $this->input->getCmd('format', 'json') . '&' . JSession::getFormToken() . '=1';
	}

	/**
	 * Render widget view.
	 *
	 * @param   string  $tmpl  Template file name to render.
	 *
	 * @return  void
	 */
	protected function render($tmpl, $data = array())
	{
		$context = $this->input->getCmd('context');
		$tplFile = dirname(__FILE__) . '/tmpl/' . $context . '/' . $tmpl . '.php';

		if (!is_file($tplFile) || !is_readable($tplFile))
		{
			throw new Exception('Template file not found: ' . $tplFile);
		}

		// Extract data to seperated variables
		extract($data);

		// Start output buffer
		ob_start();

		// Load template file
		include $tplFile;

		// Send rendered content to client
		$this->responseContent = ob_get_clean();
	}

	/**
	 * Set response content.
	 *
	 * @param   mixed  $content  Content will be sent to client
	 * @return  void
	 */
	protected function setResponse($content)
	{
		$this->responseContent = $content;
	}

	/**
	 * Get response content.
	 *
	 * @return mixed
	 */
	protected function getResponse()
	{
		return $this->responseContent;
	}
}
