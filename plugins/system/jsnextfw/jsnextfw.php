<?php
/**
 * @version    $Id$
 * @package    JSN Extension Framework 2
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

// Import necessary libraries.
jimport('joomla.filesystem.file');

// Define neccessary constants.
require_once dirname(__FILE__) . '/jsnextfw.defines.php';

/**
 * Plugin class.
 *
 * @package  JSN Extension Framework 2
 * @since    1.0.0
 */
class PlgSystemJsnExtFw extends JPlugin
{

	/**
	 * Joomla application instance.
	 *
	 * @var  JApplicationCms
	 */
	protected $app;

	/**
	 * Joomla input instance.
	 *
	 * @var  JInput
	 */
	protected $input;

	/**
	 * The currently requested component.
	 *
	 * @var  string
	 */
	protected $option;

	/**
	 * The currently requested view.
	 *
	 * @var  string
	 */
	protected $view;

	/**
	 * The currently requested task.
	 *
	 * @var  string
	 */
	protected $task;

	/**
	 * Define prefix for all classes of our framework.
	 *
	 * @var  string
	 */
	protected static $prefix = 'JsnExtFw';

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $option    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @return  void
	 */
	public function __construct($subject, $option = array())
	{
		parent::__construct($subject, $option);

		// Register class auto-loader.
		spl_autoload_register(array(
			__CLASS__,
			'autoload'
		));

		// Load plugin language file.
		$this->loadLanguage();

		// Get Joomla's application instance.
		$this->app = JFactory::getApplication();

		// Get Joomla's input object.
		$this->input = $this->app->input;

		// Get common request variables.
		$this->option = $this->input->getCmd('option');
		$this->view = $this->input->getCmd('view');
		$this->task = $this->input->getCmd('task');
		$this->tmpl = $this->input->getCmd('tmpl');

		// Get ID of field to set value for and callback function.
		$this->handler = $this->app->input->getString('handler');
		$this->fieldid = $this->app->input->getString('fieldid');

		// Redirect to update page if necessary.
		if ($this->option == 'com_installer' && $this->view == 'update' && $this->task == 'update.update')
		{
			if (count($cid = (array) $this->input->getVar('cid', array())))
			{
				// Check if extension being updated depends on JSN Ext. Framework 2.
				$dbo = JFactory::getDbo();
				$ext = $dbo->setQuery(
					$dbo->getQuery(true)
						->select('element')
						->from('#__updates')
						->where("update_id = {$cid[0]}"))
					->loadResult();
				$xml = JPATH_ADMINISTRATOR . "/components/{$ext}/" . substr($ext, 4) . '.xml';

				if (JFile::exists($xml) && $xml = simplexml_load_file($xml))
				{
					if (isset($xml->group) && (string) $xml->group == 'jsnextfw')
					{
						// Redirect to the about page.
						$this->app->redirect(JRoute::_("index.php?option={$ext}&view=about", false));
					}
				}
			}
		}
	}

	/**
	 * Implement onAfterInitialise event handler.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		// Get plugin parameters.
		$config = JsnExtFwHelper::getSettings('jsnextfw');

		// Check if Joomla's built-in media manager is requested?
		if ($this->option == 'com_media' && $this->view == 'images' && $this->tmpl == 'component' && $config['enable_media_selector'])
		{
			// Support only Joomla built-in component.
			parse_str(Juri::getInstance()->toString(array(
				'query'
			)), $params);

			$component = isset($params['asset']) ? $params['asset'] : ( isset($params['option']) ? $params['option'] : null );

			if ($component && in_array($component,
				array(
					'com_admin',
					'com_ajax',
					'com_associations',
					'com_banners',
					'com_cache',
					'com_categories',
					'com_checkin',
					'com_config',
					'com_contact',
					'com_content',
					'com_contenthistory',
					'com_cpanel',
					'com_fields',
					'com_finder',
					'com_mailto',
					'com_installer',
					'com_joomlaupdate',
					'com_languages',
					'com_login',
					'com_media',
					'com_menus',
					'com_messages',
					'com_modules',
					'com_newsfeeds',
					'com_plugins',
					'com_postinstall',
					'com_redirect',
					'com_search',
					'com_tags',
					'com_templates',
					'com_users',
					'com_wrapper'
				)))
			{
				// Build redirect link.
				$link = 'index.php?option=com_ajax&format=html&plugin=jsnextfw&context=media-selector&type=image&folder=images&' .
					 JSession::getFormToken() . '=1&editor=' . $this->app->input->get('e_name');

				if (!empty($this->handler))
				{
					$link .= "&handler={$this->handler}";
				}

				if (!empty($this->fieldid))
				{
					$link .= "&fieldid={$this->fieldid}";
				}

				if (!empty($this->tmpl))
				{
					$link .= "&tmpl={$this->tmpl}";
				}

				$this->app->redirect($link);
			}
		}
	}

	/**
	 * Implement onBeforeRender event handler.
	 *
	 * @return  void
	 */
	public function onBeforeRender()
	{
		// Get plugin parameters.
		$config = JsnExtFwHelper::getSettings('jsnextfw');

		// Check if media selector is enabled?
		if ($config['enable_media_selector'])
		{
			JFactory::getDocument()->addStyleDeclaration(
				'.mce-window.mce-in {
					padding: 0 !important;
				}
				.mce-foot .btn {
					float: left;
					margin: 10px;
					padding: 6px;
				}');
		}

		// Register event to prepare assets being loaded.
		$this->app->registerEvent('onAfterRender', array(
			&$this,
			'onAfterRender'
		));
	}

	/**
	 * Implement onAfterRender event handler.
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		if (!isset($this->onAfterRenderPassed))
		{
			$this->onAfterRenderPassed = true;

			return;
		}

		// Get response body.
		$html = $this->app->getBody();

		// If MooTools is loaded, fix compatibility problem with it.
		if (strpos($html, '/media/system/js/mootools-core.js') !== false)
		{
			$html = str_replace('</body>',
				'<script type="text/javascript">if (window.MooTools !== undefined) {
					Element.implement({
						hide: function() {
							return this;
						},
						show: function(v) {
							return this;
						},
						slide: function(v) {
							return this;
						}
					});
				}</script></body>', $html);
		}

		// Set response body.
		$this->app->setBody($html);
	}

	/**
	 * Handle onContentChangeState event to prevent this plugin from being unpublished.
	 *
	 * @param   string   $context  The current context.
	 * @param   integer  $ids      An array of item IDs that state are changed.
	 * @param   integer  $state    The new item state.
	 *
	 * @return  boolean
	 */
	public function onContentChangeState($context, $ids, $state)
	{
		if ($context === 'com_plugins.plugin' && $state == 0)
		{
			// Get Joomla database object.
			$dbo = JFactory::getDbo();

			foreach ($ids as $id)
			{
				// Get plugin details.
				$plugin = $dbo->setQuery("SELECT * FROM #__extensions WHERE extension_id = {$id}")->loadObject();

				// Prevent unpublishing this system plugin.
				if ($plugin->folder === 'system' && $plugin->element === 'jsnextfw')
				{
					$dbo->setQuery("UPDATE #__extensions SET enabled = 1 WHERE extension_id = {$id}")->execute();

					// Load necessary language files.
					JFactory::getLanguage()->load("plg_{$plugin->folder}_{$plugin->element}", JPATH_ADMINISTRATOR);

					// Set a message to let the user know that this system plugin is required.
					$this->app->enqueueMessage(JText::sprintf('JSN_EXTFW_CANNOT_UNPUBLISH_A_REQUIRED_PLUGIN', JText::_($plugin->name)),
						'info');

					return false;
				}
			}
		}
	}

	/**
	 * Handle onExtensionBeforeSave event to prevent this plugin from being unpublished.
	 *
	 * @param   string   $context  The current context.
	 * @param   object   $table    The current table data.
	 * @param   boolean  $new      Whether this is a new item?
	 *
	 * @return  boolean
	 */
	public function onExtensionBeforeSave($context, $table, $new)
	{
		if ($context === 'com_plugins.plugin' && $table->folder === 'system' && $table->element === 'jsnextfw' && $table->enabled == 0)
		{
			// Load necessary language files.
			JFactory::getLanguage()->load("plg_{$table->folder}_{$table->element}", JPATH_ADMINISTRATOR);

			// Set a message to let the user know that the system plugin of JSN PowerAdmin is required.
			$table->setError(JText::sprintf('JSN_EXTFW_CANNOT_UNPUBLISH_A_REQUIRED_PLUGIN', JText::_($table->name)), 'warning');

			return false;
		}
	}

	/**
	 * Handle onExtensionBeforeInstall event.
	 *
	 * @param   string            $method     Install method.
	 * @param   string            $type       Extension type.
	 * @param   SimpleXMLElement  $manifest  Parsed manifest file.
	 * @param   int               $eid        ID of the extension being installed.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeInstall($method, $type, $manifest, $eid)
	{
		// Check manifest to see if an extension that depends on JSN Ext. Framework G2 is being installed?
		if ((string) $manifest->group == 'jsnextfw')
		{
			$this->skipCheckForDependency = true;
		}
	}

	/**
	 * Handle onExtensionAfterInstall event.
	 *
	 * @param   JInstaller  $installer  Joomla installer object.
	 * @param   int         $eid        ID of the extension being installed.
	 *
	 * @return  void
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		// Get extension data.
		$dbo = JFactory::getDbo();
		$ext = $dbo->setQuery($dbo->getQuery(true)
			->select('*')
			->from('#__extensions')
			->where("extension_id = '{$eid}'"))
			->loadObject();

		if ($ext->type === 'plugin' && $ext->folder === 'system' && $ext->element === 'jsnframework')
		{
			// Reorder the JSN Ext. Framework gen. 1 to run before JSN Ext. Framework gen. 2.
			$ordering = $dbo->setQuery(
				$dbo->getQuery(true)
					->select('ordering')
					->from('#__extensions')
					->where("type = 'plugin'")
					->where("folder = 'system'")
					->where("element = 'jsnextfw'"))
				->loadResult();

			$dbo->setQuery(
				$dbo->getQuery(true)
					->update('#__extensions')
					->set('ordering = ' . ( (int) $ordering - 1 ))
					->where("extension_id = '{$eid}'"))
				->execute();
		}
	}

	/**
	 * Handle onExtensionBeforeUninstall event to check if JSN Ext. Framework can be safely uninstalled.
	 *
	 * @param   int  $eid  ID of the extension just uninstalled.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeUninstall($eid)
	{
		// Get extension data.
		$dbo = JFactory::getDbo();
		$ext = $dbo->setQuery("SELECT * FROM #__extensions WHERE extension_id = {$eid};")->loadObject();

		if ($ext->type === 'plugin' && $ext->folder === 'system' && $ext->element === 'jsnextfw')
		{
			// Get all remaining components.
			$exts = $dbo->setQuery("SELECT element FROM #__extensions WHERE type = 'component';")->loadColumn();

			// Loop thru components to find the first one that depends on JSN Ext. Framework 2.
			foreach ($exts as $ext)
			{
				// Read manifest file.
				$xml = JPATH_ADMINISTRATOR . "/components/{$ext}/" . substr($ext, 4) . '.xml';

				if (JFile::exists($xml) && $xml = simplexml_load_file($xml))
				{
					error_log(isset($xml->group) ? (string) $xml->group : null);
					if (isset($xml->group) && (string) $xml->group == 'jsnextfw')
					{
						throw new Exception(JText::_('JSN_EXTFW_CANNOT_UNINSTALL_EXTENSION_FRAMEWORK'));
					}
				}
			}
		}
	}

	/**
	 * Handle onExtensionAfterUninstall event to automatically uninstall JSN Ext. Framework 2 if not needed anymore.
	 *
	 * @param   JInstaller  $installer  Joomla installer object.
	 * @param   int         $eid        ID of the extension just uninstalled.
	 * @param   boolean     $result     Whether the uninstallation is success or not?
	 *
	 * @return  void
	 */
	public function onExtensionAfterUninstall($installer, $eid, $result)
	{
		if (isset($this->skipCheckForDependency) && $this->skipCheckForDependency)
		{
			return;
		}

		// Get Joomla database connector object.
		$dbo = JFactory::getDbo();

		// Get all remaining components.
		$exts = $dbo->setQuery("SELECT element FROM #__extensions WHERE type = 'component';")->loadColumn();

		// Loop thru components to find the first one that depends on JSN Ext. Framework 2.
		foreach ($exts as $ext)
		{
			// Read manifest file.
			$xml = JPATH_ADMINISTRATOR . "/components/{$ext}/" . substr($ext, 4) . '.xml';

			if (JFile::exists($xml) && $xml = simplexml_load_file($xml))
			{
				if (isset($xml->group) && (string) $xml->group == 'jsnextfw')
				{
					return;
				}
			}
		}

		// Not found any component that depends on JSN Ext. Framework 2, uninstall it.
		$id = $dbo->setQuery(
			$dbo->getQuery(true)
				->select('extension_id')
				->from('#__extensions')
				->where("type = 'plugin'")
				->where("folder = 'system'")
				->where("element = 'jsnextfw'"))
			->loadResult();

		if (empty($id))
		{
			return;
		}

		// Unprotect the JSN Ext. Framework 2 plugin first.
		if ($dbo->setQuery("UPDATE #__extensions SET protected = 0 WHERE extension_id = {$id}")->execute())
		{
			// Uninstall the JSN Ext. Framework 2 plugin.
			JInstaller::getInstance()->uninstall('plugin', $id);
		}
	}

	/**
	 * Handle Ajax requests.
	 *
	 * @return  void
	 */
	public function onAjaxJsnExtFw()
	{
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		JsnExtFwAjax::execute();

		// Exit immediately to prevent Joomla from processing further.
		exit();
	}

	/**
	 * Class auto-loader.
	 *
	 * @param   string $class_name Name of class to load declaration file for.
	 *
	 * @return  mixed
	 */
	public static function autoload($class_name)
	{
		// Verify class prefix.
		if (0 !== strpos($class_name, self::$prefix))
		{
			return false;
		}

		// Generate file path from class name.
		$base = dirname(__FILE__) . '/includes';
		$path = strtolower(preg_replace('/([A-Z])/', '/\\1', substr($class_name, strlen(self::$prefix))));

		// Find class declaration file.
		$p1 = $path . '.php';
		$p2 = $path . '/' . basename($path) . '.php';

		while (true)
		{
			// Check if file exists in standard path.
			if (@JFile::exists($base . $p1))
			{
				$exists = $p1;

				break;
			}

			// Check if file exists in alternative path.
			if (@JFile::exists($base . $p2))
			{
				$exists = $p2;

				break;
			}

			// If there is no more alternative path, quit the loop.
			if (false === strrpos($p1, '/') || 0 === strrpos($p1, '/'))
			{
				break;
			}

			// Generate more alternative path.
			$p1 = preg_replace('#/([^/]+)$#', '-\\1', $p1);
			$p2 = dirname($p1) . '/' . substr(basename($p1), 0, -4) . '/' . basename($p1);
		}

		// If class declaration file is found, include it.
		if (isset($exists))
		{
			return include_once $base . $exists;
		}

		return false;
	}
}
