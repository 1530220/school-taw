<?php
/**
 * @version    $Id$
 * @package    JSN_PowerAdmin_2
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

class plgSystemPowerAdmin2 extends JPlugin
{

	/**
	 * Path to the directory containing override files.
	 *
	 * @var  string
	 */
	protected $override;

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
	 * Joomla session object.
	 *
	 * @var  JSessionStorage
	 */
	protected $sess;

	/**
	 * Current Joomla user.
	 *
	 * @var  JUser
	 */
	protected $usr;

	/**
	 * JSN PowerAdmin config object.
	 *
	 * @var  JObject
	 */
	protected $cfg;

	/**
	 * Requested component.
	 *
	 * @var  string
	 */
	protected $option;

	/**
	 * Requested task.
	 *
	 * @var  string
	 */
	protected $task;

	/**
	 * Requested view.
	 *
	 * @var  string
	 */
	protected $view;

	/**
	 * Requested layout.
	 *
	 * @var  string
	 */
	protected $layout;

	/**
	 * Requested response template.
	 *
	 * @var  string
	 */
	protected $tmpl;

	/**
	 * Requested menu item ID.
	 *
	 * @var  integer
	 */
	protected $itemID;

	/**
	 * Whether page preview is requested?
	 *
	 * @var  integer
	 */
	protected $preview;

	/**
	 * Requested page preview mode.
	 *
	 * @var  string
	 */
	protected $mode;

	/**
	 * Whether position selector is requested?
	 *
	 * @var  integer
	 */
	protected $select;

	/**
	 * Whether to load JSN PowerAdmin React app?
	 *
	 * @var  boolean
	 */
	protected $loadApp;

	/**
	 * HTML markup for previewing module positions.
	 *
	 * @var  array
	 */
	protected $positions = array();

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @return  void
	 */
	public function __construct($subject, $option = array())
	{
		parent::__construct($subject, $option);

		// Get path to the directory containing override files.
		$this->override = dirname(__FILE__) . '/overrides';

		// Get Joomla application object.
		$this->app = JFactory::getApplication();

		// Get Joomla database object.
		$this->dbo = JFactory::getDbo();

		// Get Joomla session object.
		$this->sess = JFactory::getSession();

		// Get the current Joomla user.
		$this->usr = JFactory::getUser();

		// Get request variables.
		$this->option = $this->app->input->getCmd('option');
		$this->task = $this->app->input->getCmd('task');
		$this->view = $this->app->input->getCmd('view');
		$this->layout = $this->app->input->getCmd('layout');
		$this->tmpl = $this->app->input->getCmd('tmpl');

		$this->preview = $this->app->input->getInt('poweradmin-preview');
		$this->mode = $this->app->input->getCmd('mode');
		$this->select = $this->app->input->getInt('select-position');

		// Check if Joomla is updating?
		if ($this->option === 'com_joomlaupdate' && in_array($this->task,
			array(
				'update.download',
				'update.upload',
				'update.cleanup'
			)))
		{
			// Toggle JSN Ext. Framework gen. 2 state.
			$dbo = JFactory::getDbo();

			$dbo->setQuery(
				$dbo->getQuery(true)
					->update('#__extensions')
					->set('enabled = ' . ( $this->task === 'update.cleanup' ? 1 : 0 ))
					->where('type = "plugin"')
					->where('folder = "system"')
					->where('element = "jsnextfw"'))
				->execute();
		}

		// Check if current request is for previewing a front-end page?
		if (method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite())
		{
			// Get Joomla session object.
			$session = JFactory::getSession();

			if ($this->preview)
			{
				// Check if there is an user logged in?
				if (!JFactory::getUser()->id)
				{
					// Get an user.
					$user = $this->dbo->setQuery(
						$this->dbo->getQuery(true)
							->select('u.id')
							->from('#__users AS u')
							->innerJoin('#__user_usergroup_map AS ugm ON ugm.user_id = u.id')
							->innerJoin('#__usergroups AS g ON g.id = ugm.group_id')
							->where('u.block = 0')
							->where('g.id IN (8, 7, 6, 5, 4, 3, 2)'))
						->loadResult();

					$instance = JUser::getInstance();

					$instance->load($user);

					// Mark the user as logged in.
					$instance->guest = 0;

					// Grab the current session ID.
					$oldSessionId = $session->getId();

					// Fork the session.
					$session->fork();

					$session->set('user', $instance);
					$session->set('pa2_auto_login', 1);

					// Ensure the new session's metadata is written to the database.
					$this->app->checkSession();

					// Purge the old session.
					$query = $this->dbo->getQuery(true)
						->delete('#__session')
						->where($this->dbo->quoteName('session_id') . ' = ' . $this->dbo->quote($oldSessionId));

					try
					{
						$this->dbo->setQuery($query)->execute();
					}
					catch (RuntimeException $e)
					{
						// The old session is already invalidated, don't let this block logging in.
					}

					// Hit the user last visit field.
					$instance->setLastVisit();

					// Add "user state" cookie used for reverse caching proxies like Varnish, Nginx etc.
					$this->app->input->cookie->set('joomla_user_state', 'logged_in', 0, $this->app->get('cookie_path', '/'),
						$this->app->get('cookie_domain', ''), method_exists($this->app, 'isHttpsForced') && $this->app->isHttpsForced(),
						true);
				}
			}
			elseif ((int) $session->get('pa2_auto_login'))
			{
				// Prepare the logout options.
				$options = array(
					'clientid' => $this->app->get('shared_session', '0') ? null : 0
				);

				// Perform the log out.
				$this->app->logout(null, $options);

				$session->set('pa2_auto_login', 0);
			}
		}

		// Load language file.
		JFactory::getLanguage()->load('plg_system_poweradmin2', JPATH_ADMINISTRATOR);

		// Register neccessary JSN PowerAdmin helper classes.
		JLoader::register('JSNPowerAdmin2HistoryHelper', JPATH_ADMINISTRATOR . '/components/com_poweradmin2/helpers/history.php');
		JLoader::register('JSNPowerAdmin2Helper', JPATH_ADMINISTRATOR . '/components/com_poweradmin2/helpers/poweradmin2.php');

		// Register a shutdown function to handle redirect.
		register_shutdown_function(array(
			&$this,
			'handleRedirect'
		));
	}

	/**
	 * Initialize JSN PowerAdmin.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		// Make sure the JSN PowerAdmin component is installed.
		if (!class_exists('JSNPowerAdmin2Helper'))
		{
			return;
		}

		// Get JSN PowerAdmin config object.
		$this->cfg = JSNPowerAdmin2Helper::getConfig();

		// Check if an admin page is requested?
		if (method_exists($this->app, 'isClient') ? $this->app->isClient('administrator') : $this->app->isAdmin())
		{
			// Init history tracking.
			if (intval($this->usr->id) > 0)
			{
				JSNPowerAdmin2HistoryHelper::onAfterInitialise();
			}

			// Load JSN PowerAdmin React app if needed.
			if (@intval($this->cfg['position_chooser_enhance']) && $this->option == 'com_modules' && $this->view == 'module' &&
				 $this->layout == 'edit' && class_exists('JsnExtFwAssets'))
			{
				// Load required libraries.
				JsnExtFwAssets::loadJsnElements();

				// Generate base URL to assets folder.
				$base_url = JUri::root(true) . '/plugins/system/poweradmin2/assets';

				// Load assets of JSN PowerAdmin.
				JsnExtFwAssets::loadStylesheet("{$base_url}/css/style.css");
				JsnExtFwAssets::loadStylesheet("{$base_url}/css/custom.css");
				JsnExtFwAssets::loadScript("{$base_url}/js/poweradmin.js");
			}

			// Check referer for site search page.
			if (isset($_SERVER['HTTP_REFERER']))
			{
				if (strpos($_SERVER['HTTP_REFERER'], '&tmpl=component') === false)
				{
					if (strpos($_SERVER['HTTP_REFERER'], '&poweradmin-search=1') !== false)
					{
						if (!preg_match('/&(task=[^&]*\.?edit|[^=]*id=\d+)/', $_SERVER['REQUEST_URI']))
						{
							$this->app->input->set('poweradmin-search', '1');
						}
					}
				}
				elseif ($this->app->input->getInt('poweradmin-search'))
				{
					$this->app->input->set('poweradmin-search', '0');
				}
			}

			// Override some class if search page is requested.
			if ($this->app->input->getInt('poweradmin-search'))
			{
				// Override the articles model of content component.
				JLoader::register('ContentModelArticles',
					dirname(__FILE__) . '/overrides/administrator/components/com_content/models/articles.php');
				JLoader::load('ContentModelArticles');
			}
		}
		elseif ($this->preview)
		{
			// Disable error reporting.
			error_reporting(0);

			// Get Joomla version.
			$version = new JVersion();

			// Because the Yjsg Framework plugin always load their class overrides,
			// we need to make sure that plugins are not installed and activated before loading our class overrides.
			if (!JPluginHelper::isEnabled('system', 'yjsg'))
			{
				if (version_compare($version->getShortVersion(), '3.8', '>='))
				{
					// Override \Joomla\CMS\Helper\ModuleHelper class.
					JLoader::register('\\Joomla\\CMS\\Helper\\ModuleHelper', "{$this->override}/libraries/src/Helper/ModuleHelper.php");
					JLoader::load('\\Joomla\\CMS\\Helper\\ModuleHelper');
				}

				// Override JModuleHelper class.
				JLoader::register('JModuleHelper', "{$this->override}/libraries/cms/module/helper.php");
				JLoader::load('JModuleHelper');
			}

			// Override JDocumentRendererHtmlModules class.
			JLoader::register('JDocumentRendererHtmlModules', "{$this->override}/libraries/joomla/document/renderer/html/modules.php");
			JLoader::load('JDocumentRendererHtmlModules');

			if (!$this->select)
			{
				// Override JViewLegacy class.
				JLoader::register('JViewLegacy', "{$this->override}/libraries/legacy/view/legacy.php");
				JLoader::load('JViewLegacy');

				// Because the Yjsg Framework plugin always load its class overrides,
				// we need to make sure that plugin is not installed and activated before loading our class overrides.
				if (!JPluginHelper::isEnabled('system', 'yjsg'))
				{
					// Override JLayoutFile class.
					JLoader::register('JLayoutFile', "{$this->override}/libraries/cms/layout/file.php");
					JLoader::load('JLayoutFile');
				}
			}
		}

		// Load additional plugins for JSN PowerAdmin.
		JPluginHelper::importPlugin('poweradmin');

		// Register onAfterRoute event handler.
		$this->app->registerEvent('onAfterRoute', array(
			&$this,
			'onAfterRoute'
		));
	}

	/**
	 * Listen to onAfterRoute event to alter the execution order of onBeforeRender event handler.
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		// Make sure this event handler is executed at last order.
		if (!isset($this->onAfterRouteReordered))
		{
			$this->onAfterRouteReordered = true;

			return;
		}

		if (method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite())
		{
			// Get request variables if missing.
			if (empty($this->option))
			{
				$this->option = $this->app->input->getCmd('option');
				$this->task = $this->app->input->getCmd('task');
				$this->view = $this->app->input->getCmd('view');
				$this->layout = $this->app->input->getCmd('layout');
				$this->tmpl = $this->app->input->getCmd('tmpl');

				$this->preview = $this->app->input->getInt('poweradmin-preview');
				$this->mode = $this->app->input->getCmd('mode');
				$this->select = $this->app->input->getInt('select-position');
			}

			// Get the menu item ID of the current page.
			if ($this->app->input->exists('Itemid'))
			{
				$this->itemID = $this->app->input->getInt('Itemid');
			}
			else
			{
				$menu = $this->app->getMenu('site');

				if (!( $item = $menu->getActive() ))
				{
					$lang = JFactory::getLanguage();

					if (JLanguageMultilang::isEnabled())
					{
						$item = $menu->getDefault($lang->getTag());
					}
					else
					{
						$item = $menu->getDefault();
					}
				}

				$this->itemID = $item->id;
			}
		}

		// Register onBeforeRender event handler.
		$this->app->registerEvent('onBeforeRender', array(
			&$this,
			'onBeforeRender'
		));
	}

	/**
	 * Listen to onBeforeRenderModulePosition event to set a flag for later use.
	 *
	 * @param   string  $position  The position for rendering modules.
	 * @param   array   &$params   Associative array of values.
	 * @param   string  &$content  Current content.
	 *
	 * @return  void
	 */
	public function onBeforeRenderModulePosition($position, &$params, &$content)
	{
		// Store position that is being rendered.
		$this->positionBeingRendered = $position;
	}

	/**
	 * Listen to onAfterGetModules event to set some data for later use.
	 *
	 * The purpose of this event handler is providing support for previewing
	 * module positions of Gantry based templates.
	 *
	 * @param   string  $position  The position for retrieving modules.
	 * @param   string  &$modules  Retrieved modules.
	 *
	 * @return  void
	 */
	public function onAfterGetModules($position, &$modules)
	{
		// Work around for Gantry framework for previewing module positions.
		if (!isset($this->positionBeingRendered) && count($modules))
		{
			// Store position that might be rendered.
			$this->positionMightBeRendered = $position;
			$this->positionMightBeRenderedFirstModule = $modules[0];
			$this->positionMightBeRenderedLastModule = $modules[count($modules) - 1];
		}
	}

	/**
	 * Customize module output for previewing.
	 *
	 * @param   array  &$module   Module data.
	 * @param   array  &$attribs  Module parameters.
	 *
	 * @return  void
	 */
	public function onAfterRenderModule(&$module, &$attribs)
	{
		// Check if current request is for previewing a front-end page?
		if (( method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite() ) && $this->preview)
		{
			// Only continue if the module is being rendered in a position.
			if (!isset($this->positionBeingRendered) && !isset($this->positionMightBeRendered))
			{
				return;
			}

			elseif (isset($this->positionBeingRendered) && $this->positionBeingRendered != $module->position)
			{
				return;
			}

			elseif (!isset($this->positionBeingRendered) &&
				 ( isset($this->positionMightBeRendered) && $this->positionMightBeRendered != $module->position ))
			{
				return;
			}

			// Clear module content if this is a dummy module rendered when a position is empty?
			if ($module->title == $module->position && $module->module == "mod_{$module->position}" && !isset($module->published))
			{
				$module->content = '<!-- dummy module -->';
			}

			// Also clear module content if selecting position.
			elseif ($this->select)
			{
				$module->content = '<!-- dummy module -->';
			}

			// Otherwise, override module content for previewing.
			else
			{
				// Check if module is assigned to the current page.
				$module->assigned = '0';

				$this->dbo->setQuery("SELECT * FROM #__modules_menu WHERE moduleid = {$module->id};");

				foreach ($this->dbo->loadObjectList() as $assignment)
				{
					if ((int) $assignment->menuid == 0 || (int) $assignment->menuid == (int) $this->itemID)
					{
						$module->assigned = '1';
					}
					elseif ((int) $assignment->menuid == 0 - (int) $this->itemID)
					{
						$module->assigned = '0';

						break;
					}
				}

				// Pass module data to HTML.
				$props = array();

				foreach (get_object_vars($module) as $k => $v)
				{
					if (in_array($k, array(
						'id',
						'menuid',
						'published',
						'assigned',
						'title'
					)))
					{
						$props[] = 'data-' . $k . '="' . addslashes($v) . '"';
					}
				}

				// Check if module is visible?
				$class = 'pa-module';

				if (!intval($module->assigned) || !intval($module->published))
				{
					$class .= ' hidden';
				}

				// Generate HTML markup for previewing module.
				$module->content = '
					<li class="' . $class . '" ' . implode(' ', $props) . '>
						<i class="fa fa-ellipsis-v sortable-handler"></i>
						<span class="module-title">' . $module->title . '</span>
					</li>';
			}

			// Work around for Gantry framework for previewing module positions.
			if (!isset($this->positionBeingRendered) && isset($this->positionMightBeRendered))
			{
				if ($this->positionMightBeRenderedFirstModule->id == $module->id)
				{
					$module->content = '
					<div class="jsn-bootstrap4 pa-position' . ( $module->position == $this->app->input->getCmd('current') ? ' current' : '' ) .
						 '" data-position="' . $module->position . '">
						<h3 class="position-name">' . $module->position . '</h3>
						<ul class="position-modules">' . $module->content;
				}

				if ($this->positionMightBeRenderedLastModule->id == $module->id)
				{
					$module->content .= '
						</ul>
					</div>';

					// Clear flag.
					unset($this->positionMightBeRendered);
					unset($this->positionMightBeRenderedFirstModule);
					unset($this->positionMightBeRenderedLastModule);
				}
			}
		}
	}

	/**
	 * Listen to onAfterRenderModulePosition event to finalize output for previewing.
	 *
	 * @param   string  $position  The position for rendering modules.
	 * @param   array   &$params   Associative array of values.
	 * @param   string  &$content  Rendered content.
	 *
	 * @return  void
	 */
	public function onAfterRenderModulePosition($position, &$params, &$content)
	{
		// Check if current request is for previewing a front-end page?
		if (( method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite() ) && $this->preview)
		{
			if (strpos($content, '<li class="pa-module') === false && strpos($content, '<!-- dummy module -->') === false)
			{
				return;
			}

			if (!empty($content) || $this->select || $this->app->input->getInt('show-empty-position'))
			{
				// Generate HTML markup for previewing module position.
				$html = '
					<div class="jsn-bootstrap4 pa-position' .
					 ( $position == $this->app->input->getCmd('current') ? ' current' : '' ) . ' hidden" data-position="' . $position . '">
						<h3 class="position-name">' . $position . '</h3>';

				if (!$this->select)
				{
					$html .= '
						<ul class="position-modules">
							' . $content . '
						</ul>';
				}

				$html .= '
					</div>';

				// If preview mode is not 'module', override output.
				if ($this->mode != 'module')
				{
					$content = $html;
				}

				// Otherwise, store content to a variable for later use.
				else
				{
					$this->positions[] = $html;
				}
			}
		}

		// Clear flag.
		unset($this->positionBeingRendered);
	}

	/**
	 * Listen to onBeforeLoadTemplateFile event to prepare component output for inline editing display options.
	 *
	 * @param   array   &$path  Array of directory path to look for template file.
	 * @param   string  $file   Template file name.
	 *
	 * @return  void
	 */
	public function onBeforeLoadTemplateFile(&$path, $file)
	{
		// Generate path to override file.
		$original = $path[count($path) - 1];
		$override = str_replace(JPATH_ROOT, $this->override, $original);

		// Update array of path to look for template file if override file exists.
		if (!in_array($override, $path) && is_file($override . $file))
		{
			array_unshift($path, $override);
		}

		// Trigger an event to allow 3rd-party to hook in.
		$this->app->triggerEvent('onPowerAdminGetTemplateFilePath', array(
			&$path,
			$file
		));
	}

	/**
	 * Listen to onBeforeLoadLayoutFile event to prepare template layout for inline editing display options.
	 *
	 * @param   array   &$path  Array of directory path to look for layout file.
	 * @param   string  $raw    Raw layout file path.
	 *
	 * @return  void
	 */
	public function onBeforeLoadLayoutFile(&$path, $raw)
	{
		// Generate path to override file.
		$original = $path[count($path) - 1];
		$override = str_replace(JPATH_ROOT, $this->override, $original);

		// Update array of path to look for layout file if override file exists.
		if (!in_array($override, $path) && is_file("{$override}/{$raw}"))
		{
			array_unshift($path, $override);
		}

		// Trigger an event to allow 3rd-party to hook in.
		$this->app->triggerEvent('onPowerAdminGetLayoutFilePath', array(
			&$path,
			$raw
		));
	}

	/**
	 * Prepare front-end page for previewing in Site Manager.
	 *
	 * @return  void
	 */
	public function onBeforeRender()
	{
		// Make sure this event handler is executed at last order.
		if (!isset($this->onBeforeRenderReordered))
		{
			$this->onBeforeRenderReordered = true;

			return;
		}

		// Simply return if required class is missing.
		if (!class_exists('JsnExtFwAssets'))
		{
			return;
		}

		// Check if a front-end page is requested?
		if (method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite())
		{
			// Check if the requested front-end page has custom assets?
			if ((int) $this->cfg['custom_assets_enhance'])
			{
				foreach (array(
					'css',
					'js'
				) as $type)
				{
					if ($custom = JSNPowerAdmin2Helper::getCustomAssets($this->itemID, $type))
					{
						foreach ($custom->assets as $url => $cfg)
						{
							if (intval($cfg['loaded']))
							{
								if ($type == 'css')
								{
									JsnExtFwAssets::loadStylesheet($url);
								}
								else
								{
									JsnExtFwAssets::loadScript($url);
								}
							}
						}
					}
				}
			}

			// Check if the current request is for previewing a front-end page?
			if ($this->preview)
			{
				// If preview mode is not 'live', remove all stylesheets.
				if ($this->mode == 'module')
				{
					// Get Joomla document object.
					$doc = JFactory::getDocument();

					$doc->_styleSheets = array();
					$doc->_style = array();
				}

				// Load required libraries.
				JsnExtFwAssets::loadJsnElements();

				// Generate base URL to assets folder.
				$base_url = JUri::root(true) . '/plugins/system/poweradmin2/assets';

				// Load assets of JSN PowerAdmin.
				JsnExtFwAssets::loadStylesheet("{$base_url}/css/style.css");
				JsnExtFwAssets::loadStylesheet("{$base_url}/css/custom.css");
				JsnExtFwAssets::loadScript("{$base_url}/js/poweradmin.js");

				// Load some inline styles.
				JsnExtFwAssets::loadInlineStyle(
					'.cc-window,
					.cc_container,
					.dropdown-menu > .edit-icon {
						display: none !important;
					}');
			}
		}

		// Check if an admin page is requested?
		elseif (method_exists($this->app, 'isClient') ? $this->app->isClient('administrator') : $this->app->isAdmin())
		{
			// Load edition manager.
			if ($this->option === 'com_poweradmin2')
			{
				JsnExtFwAssets::loadEditionManager();
			}

			// Load assets for history tracking.
			if (intval($this->usr->id) > 0 && $this->tmpl != 'component' && class_exists('JsnExtFwAssets'))
			{
				// Load required libraries.
				JsnExtFwAssets::loadCookie();
				JsnExtFwAssets::loadJsnCommon();

				// Generate base URL to assets folder.
				$base_url = JUri::root(true) . '/plugins/system/poweradmin2/assets';

				// Load assets of JSN PowerAdmin.
				JsnExtFwAssets::loadScript("{$base_url}/js/history.js");

				JsnExtFwAssets::loadInlineScript(
					';jQuery(document).ready(function() {
						if (JSN.History) {
							new JSN.History({ token: "' . JSession::getFormToken() . '" });
						}
					});');
			}

			// Check if the screen for adding/editing a module is requested?
			if ($this->option == 'com_modules')
			{
				// If current screen is for selecting module type, init the filter field.
				if ($this->view == 'select' && $this->tmpl == 'component' && $this->preview)
				{
					JsnExtFwAssets::loadJsnCommon();

					JsnExtFwAssets::loadInlineScript(
						';jQuery(document).ready(function() {
							(function(api) {
								api.Event.add("h2 > input", "keyup", function(event) {
									var target = event.target;

									target.timeout && clearTimeout(target.timeout);

									target.timeout = setTimeout(function() {
										var
										search = new RegExp("(" + target.value + ")", "gi"),
										moduleTypes = event.target.parentNode.nextElementSibling.children;

										for (var i = 0; i < moduleTypes.length; i++) {
											if ( target.value == "" || moduleTypes[i].textContent.match(search) ) {
												moduleTypes[i].style.display = "";
											} else {
												moduleTypes[i].style.display = "none";
											}
										}
									}, 200);
								});
							})((JSN = window.JSN || {}));
						});');
				}
			}

			// Check if a search page is requested?
			if ($this->app->input->getInt('poweradmin-search'))
			{
				JsnExtFwAssets::loadInlineStyle(
					'body.pa-search-results #jsn-header-bar,
					body.pa-search-results #pa-adminbar,
					body.pa-search-results nav.navbar-fixed-top,
					body.pa-search-results div.navbar-fixed-bottom,
					body.pa-search-results header.header,
					body.pa-search-results a.btn-subhead,
					body.pa-search-results div.subhead-collapse,
					body.pa-search-results #system-message-container,
					body.pa-search-results #j-sidebar-container,
					body.pa-search-results .js-stools,
					body.pa-search-results .table th > input,
					body.pa-search-results .table td > input {
						display: none !important;
					}
					body.pa-search-results .container-fluid.container-main {
						padding: 0;
					}
					body.pa-search-results form {
						margin: 0;
					}
					body.pa-search-results #j-main-container {
						float: none;
						margin-left: 0;
						padding-left: 0;
						width: auto;
					}
					body.pa-search-results .table {
						margin: 0;
					}
					body.pa-search-results .jsn-page-list .jsn-table-centered .btn-micro {
						padding: 0;
					}');

				JsnExtFwAssets::loadInlineScript(
					';var href = window.location.href;
					if (href.indexOf("&poweradmin-search=1") < 0 && !href.match(/&(task=[^&]*\.?edit|[^=]*id=\d+)/)) {
						window.location.href += "&poweradmin-search=1";
					}
					document.addEventListener("DOMContentLoaded", function() {
						if (href.indexOf("&poweradmin-search=1") < 0) {
							document.body.classList.remove("pa-search-results");
						} else {
							document.body.classList.add("pa-search-results");
						}
						document.addEventListener("click", function(event) {
							var target = event.target;
							while (target && target.nodeName != "A" && target.nodeName != "BODY") {
								target = target.parentNode;
							}
							if (target.nodeName == "A") {
								var option = window.location.href.match(/option=([^&]+)/)[1];
								if (target.href.indexOf(option) > -1 && !target.href.match(/&(task=[^&]*\.?edit|[^=]*id=\d+)/)) {
									target.href += "&poweradmin-search=1";
								}
							}
						});
					});');
			}
		}

		// Register onAfterRender event handler.
		$this->app->registerEvent('onAfterRender', array(
			&$this,
			'onAfterRender'
		));
	}

	/**
	 * Initialize page preview for Site Manager.
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		// Make sure this event handler is executed at last order.
		if (!isset($this->onAfterRenderReordered))
		{
			// Check if the JCH Optimize Pro plugin is installed?
			if (JPluginHelper::isEnabled('system', 'jch_optimize') &&
				 ( method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite() ) && $this->preview)
			{
				$this->response = JResponse::getBody();
			}

			$this->onAfterRenderReordered = true;

			return;
		}

		// Get current output.
		if (empty($this->response))
		{
			$this->response = JResponse::getBody();
		}

		// Check if current request is for previewing a front-end page?
		if (method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite())
		{
			// Check if the current page is login page?
			if ($this->view == 'login')
			{
				// Check if the referer page is for previewing component or modules?
				parse_str(parse_url(base64_decode($this->app->input->getString('return')), PHP_URL_QUERY), $referer);

				if (isset($referer['poweradmin-preview']) && (int) $referer['poweradmin-preview'] && !empty($referer['parent_id']))
				{
					switch ($referer['parent_id'])
					{
						case 'component-panel':
							$msg = 'JSN_POWERADMIN_COMPONENT_PREVIEW_NOT_AVAILABLE';
						break;

						case 'preview-panel':
							if (isset($referer['mode']) && $referer['mode'] == 'module')
							{
								$msg = 'JSN_POWERADMIN_MODULES_PREVIEW_NOT_AVAILABLE';
							}
							else
							{
								$msg = 'JSN_POWERADMIN_PAGE_PREVIEW_NOT_AVAILABLE';
							}
						break;
					}

					if (isset($msg))
					{
						list($head, $body) = explode('<body', $this->response, 2);

						// Remove all assets from document head.
						if (preg_match_all('#(<link href="[^"]+" rel="stylesheet" />|<script src="[^"]+"></script>)#', $head, $matches,
							PREG_SET_ORDER))
						{
							foreach ($matches as $match)
							{
								$head = str_replace($match[0], '', $head);
							}
						}

						// Load Bootstrap 4.
						ob_start();

						JsnExtFwAssets::loadBootstrap(true);

						$head = str_replace('</head>', ob_get_contents() . '</head>', $head);

						ob_end_clean();

						// Remove all inline style and script from document head.
						$tmp = preg_split('#<(style|script)\s*(type=[\'"]text/css[\'"]|type=[\'"]text/javascript[\'"])*>#', $head);
						$head = $tmp[0];

						for ($i = 1; $i < count($tmp); $i++)
						{
							$tmp[$i] = preg_split('#</(style|script)>#', $tmp[$i], 2);
							$head .= array_pop($tmp[$i]);
						}

						$this->response = $head . '
							<body class="jsn-bootstrap4">
								<div class="alert alert-danger m-3">' . JText::_($msg) . '</div>
							</body>
						</html>';
					}
				}
			}

			// Otherwise, check if page preview is requested?
			if ($this->preview)
			{
				ob_start();

				// Generate link to get config for previewing page.
				$link = JUri::root(true) .
					 "/administrator/index.php?option=com_poweradmin2&task=ajax.getPagePreviewConfig&id={$this->itemID}";

				// If preview mode is not 'module', just add page preview element.
				if ($this->mode != 'module')
				{
					$this->response = str_replace('</body>',
						'<div id="poweradmin-preview" data-render="ComponentPagePreview" data-config="' . $link . '"></div></body>',
						$this->response);
				}

				// Otherwise, replace the entire document body.
				else
				{
					// Split document into head and body.
					list($head, $body) = explode('<body', $this->response, 2);

					// Rebuild document.
					if (count($this->positions))
					{
						// Get all modules that are assigned to 'None'.
						$modules = $this->dbo->setQuery("SELECT * FROM #__modules WHERE client_id = 0 AND position = '';")->loadObjectList();
						$content = array();

						foreach ($modules as $module)
						{
							// Check if module is assigned to the current page.
							$module->assigned = '0';

							$this->dbo->setQuery("SELECT * FROM #__modules_menu WHERE moduleid = {$module->id};");

							foreach ($this->dbo->loadObjectList() as $assignment)
							{
								if ((int) $assignment->menuid == 0 || (int) $assignment->menuid == (int) $this->itemID)
								{
									$module->assigned = '1';
								}
								elseif ((int) $assignment->menuid == 0 - (int) $this->itemID)
								{
									$module->assigned = '0';

									break;
								}
							}

							$content[] = '
								<li class="pa-module" data-id="' . $module->id . '" data-title="' . $module->title . '" data-published="' . $module->published .
								 '" data-assigned="' . $module->assigned . '">
									<i class="fa fa-ellipsis-v sortable-handler"></i>
									<span class="module-title">' . $module->title . '</span>
								</li>';
						}

						if (count($content))
						{
							// Generate HTML markup for previewing 'None' position.
							$html = '
								<div class="jsn-bootstrap4 pa-position" data-position="noposition">
									<h3 class="position-name">' . JText::_('JNONE') . '</h3>';

							if (!$this->select)
							{
								$html .= '
									<ul class="position-modules">
										' . implode($content) . '
									</ul>';
							}

							$html .= '
								</div>';

							array_unshift($this->positions, $html);
						}

						$this->response = $head . '
							<body class="jsn-bootstrap4 pa-module-preview">
								' . implode("\n\t\t\t\t\t\t", array_reverse($this->positions)) . '
								<div id="poweradmin-preview" data-render="ComponentPagePreview" data-config="' . $link . '"></div>
							</body>
						</html>';
					}
					else
					{
						$this->response = $head . '
							<body class="jsn-bootstrap4">
								<div class="alert alert-warning" role="alert">
									' . JText::_('JSN_POWERADMIN_NOT_FOUND_ANY_MODULE_POSITION') . '
								</div>
							</body>
						</html>';
					}
				}
			}
		}

		// Check if an admin page is requested?
		elseif (method_exists($this->app, 'isClient') ? $this->app->isClient('administrator') : $this->app->isAdmin())
		{
			// Init history tracking.
			if (intval($this->usr->id) > 0 && class_exists('JSNPowerAdmin2HistoryHelper'))
			{
				JSNPowerAdmin2HistoryHelper::onAfterRender();
			}

			// Check if current request is for site search screen?
			if ($this->app->input->getInt('poweradmin-search'))
			{
				$this->response = preg_replace('/data-content="([^"]+)" data-placement="top"/',
					'data-content="\\1" data-placement="bottom"', $this->response);
			}

			// Check if current request is for adding module via site manager?
			elseif ($this->option == 'com_modules')
			{
				if ($this->tmpl == 'component' && $this->view == 'select' && $this->preview)
				{
					// Add an input box to filter module type.
					$this->response = str_replace('</h2>',
						'<input placeholder="' . JText::_('JSN_POWERADMIN_SEARCH_PLACEHOLDER') . '" style="float: right;" /></h2>',
						$this->response);

					// Add 'tmpl=component' to all action links.
					$pattern = '/index\.php\?option=com_modules&(amp;)?task=module\.add&(amp;)?eid=\d+/';

					if (preg_match_all($pattern, $this->response, $matches, PREG_SET_ORDER))
					{
						foreach ($matches as $match)
						{
							$this->response = str_replace($match[0], $match[0] . '" target="_blank', $this->response);
						}
					}

					// Get target position from request.
					$position = $this->app->input->getCmd('position');

					// Then, store it to the current session.
					$this->sess->set('pa-position', $position);
				}
				elseif ($this->view == 'module' && $this->layout = 'edit')
				{
					// Get target position from the current session.
					if ($position = $this->sess->get('pa-position'))
					{
						// Pre-select target position if none is selected.
						$tmp = explode('<select id="jform_position"', $this->response);
						$tmp = explode('</select>', $tmp[1], 2);

						if (strpos($tmp[0], '<option value="" selected="selected"></option>') !== false)
						{
							$tmp[1] = str_replace(
								array(
									'<option value="" selected="selected"></option>',
									'<option value="' . $position . '">'
								),
								array(
									'<option value=""></option>',
									'<option value="' . $position . '" selected="selected">'
								), $tmp[0]);

							$this->response = str_replace($tmp[0], $tmp[1], $this->response);
						}
					}
				}
			}

			// If current screen is for editing an item, track closing action to refresh Site Manager.
			if ($this->layout === 'edit')
			{
				$this->response = str_replace('</body>',
					'<script type="text/javascript">
						var oldOnLoad = window.onload;
						window.onload = function(event) {
							var oldOnBeforeUnload = window.onbeforeunload;
							window.onbeforeunload = function(event) {
								if (window.opener && window.opener.findReactComponent) {
									var component = window.opener.findReactComponent(window.opener.document.querySelector("#site-manager [data-reactroot]"));
									if (component) {
										for (var p in component.refs) {
											var panel = window.opener.findReactComponent(component.refs[p].children[0]);
											if (panel && panel.scheduleRefresh) {
												panel.scheduleRefresh();
											}
										}
									}
									if (typeof oldOnBeforeUnload == "function") {
										return oldOnBeforeUnload(event);
									}
								}
							};
							if (typeof oldOnLoad == "function") {
								return oldOnLoad(event);
							}
						};
					</script>
					</body>', $this->response);
			}

			// If current screen is the full view for adding/editing a module, replace the position select box.
			if (@intval($this->cfg['position_chooser_enhance']) && $this->view == 'module' && $this->layout == 'edit' &&
				 $this->tmpl != 'component')
			{
				// Generate link to get all menu items.
				$config = JRoute::_(
					sprintf('index.php?option=com_poweradmin2&task=ajax.getMenuPanelConfig&%1$s=1', JSession::getFormToken()), false);

				$this->response = str_replace('</body>',
					'<script type="text/javascript">
						var
						id = "jform_position",
						field = document.getElementById(id),
						append = field.parentNode,
						button = document.createElement("button"),
						wrapper = document.createElement("div"),
						props = {
							fid: id,
							title: "' . JText::_('JSN_POWERADMIN_SELECT_MODULE_POSITION') . '",
							config: "' . $config . '",
							selector: "' . JUri::root(true) . '/index.php?poweradmin-preview=1&select-position=1",
							wrapperClass: "jsn-bootstrap4",
							render: "ComponentSelectPosition"
						};

						for (var p in props) {
							button.setAttribute("data-" + p, props[p]);
						}

						wrapper.className = append.className;
						append.className = "input-append";

						button.type = "button";
						button.className = "btn btn-default";

						append.parentNode.insertBefore(wrapper, append);
						wrapper.appendChild(append);
						append.appendChild(button);
					</script>
					<style type="text/css">
						#jform_position_chzn {
							display: inline-block !important;
						}
						#jform_position_chzn + button + .input-append {
							display: none;
						}
					</style>
				</body>', $this->response);
			}

			// If search page is requested, add an extra class to body tag.
			if ($this->app->input->getInt('poweradmin-search'))
			{
				$this->response = preg_replace('#<body\s+class="#', '<body class="pa-search-results ', $this->response);
			}
		}

		// Set new output.
		JResponse::setBody($this->response);

		// Register onAfterRespond event handler.
		$this->app->registerEvent('onAfterRespond', array(
			&$this,
			'onAfterRespond'
		));
	}

	/**
	 * Handle onAfterRespond event to remove the debug panel on preview pages.
	 *
	 * @return  void
	 */
	public function onAfterRespond()
	{
		// Make sure this event handler is executed at last order.
		if (!isset($this->onAfterRespondReordered))
		{
			$this->onAfterRespondReordered = true;

			return;
		}

		if (( method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite() ) && $this->preview)
		{
			// Capture output.
			$contents = ob_get_contents();

			ob_end_clean();

			// Clean unnecessary assets if mode is 'module'.
			if ($this->mode == 'module')
			{
				// Remove all inline style and script from document head.
				$tmp = preg_split('#<(style|script)\s*(type=[\'"]text/css[\'"]|type=[\'"]text/javascript[\'"])*>#', $contents);
				$contents = $tmp[0];

				for ($i = 1; $i < count($tmp); $i++)
				{
					$tmp[$i] = preg_split('#</(style|script)>#', $tmp[$i], 2);
					$contents .= array_pop($tmp[$i]);
				}
			}

			// Check if debugging or language debug is enabled?
			if ($this->app->get('debug') != '0' || $this->app->get('debug_lang') != '0')
			{
				$contents = current(explode('<div id="system-debug" class="profiler">', $contents)) . '</body></html>';
			}

			echo $contents;
		}
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
			foreach ($ids as $id)
			{
				// Get plugin details.
				$plugin = $this->dbo->setQuery("SELECT * FROM #__extensions WHERE extension_id = {$id}")->loadObject();

				// Prevent unpublishing the system plugin of JSN PowerAdmin.
				if ($plugin->folder === 'system' && $plugin->element === 'poweradmin2')
				{
					$this->dbo->setQuery("UPDATE #__extensions SET enabled = 1 WHERE extension_id = {$id}")->execute();

					// Load necessary language files.
					JFactory::getLanguage()->load("plg_{$plugin->folder}_{$plugin->element}", JPATH_ADMINISTRATOR);

					// Set a message to let the user know that the system plugin of JSN PowerAdmin is required.
					$this->app->enqueueMessage(
						JText::sprintf('JSN_POWERADMIN_CANNOT_UNPUBLISH_A_REQUIRED_PLUGIN', JText::_($plugin->name)), 'info');

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
		if ($context === 'com_plugins.plugin' && $table->folder === 'system' && $table->element === 'poweradmin2' && $table->enabled == 0)
		{
			// Load necessary language files.
			JFactory::getLanguage()->load("plg_{$table->folder}_{$table->element}", JPATH_ADMINISTRATOR);

			// Set a message to let the user know that the system plugin of JSN PowerAdmin is required.
			$table->setError(JText::sprintf('JSN_POWERADMIN_CANNOT_UNPUBLISH_A_REQUIRED_PLUGIN', JText::_($table->name)), 'warning');

			return false;
		}
	}

	/**
	 * Handle onExtensionBeforeInstall event to automatically uninstall JSN PowerAdmin 2 if gen. 1 is being installed.
	 *
	 * @param   string            $method     Install method.
	 * @param   string            $type       Extension type.
	 * @param   SimpleXMLElement  $manifest   Parsed manifest file.
	 * @param   int               $eid        ID of the extension being installed.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeInstall($method, $type, $manifest, $eid)
	{
		/*if (strtolower($manifest->name) === 'poweradmin' && (string) $manifest->identified_name === 'ext_poweradmin')
		 {
		 // Parse remote file URL.
		 $parts = parse_url(JUri::base() . 'index.php?option=com_installer&view=manage');
		 $secure = ( $parts['scheme'] == 'https' ) ? true : false;

		 // Open a socket connection to uninstall JSN PowerAdmin 2.
		 $fp = fsockopen(( $secure ? 'ssl://' : '' ) . $parts['host'], isset($parts['port']) ? $parts['port'] : ( $secure ? 443 : 80 ),
		 $errno, $errstr, 5);

		 if (!$fp)
		 {
		 throw new Exception(JText::_('JSN_POWERADMIN_FAILED_TO_UNINSTALL'));
		 }

		 // Look for the extension ID of JSN PowerAdmin 2.
		 $id = $this->dbo->setQuery(
		 $this->dbo->getQuery(true)
		 ->select('extension_id')
		 ->from('#__extensions')
		 ->where('type = "component"')
		 ->where('element = "com_poweradmin2"'))
		 ->loadResult();

		 // Prepare post data.
		 $parts['post'] = http_build_query(
		 array(
		 'cid' => array(
		 $id
		 ),
		 'task' => 'manage.remove',
		 JSession::getFormToken() => 1
		 ));

		 // Create request header.
		 $request = "POST {$parts['path']}" . ( empty($parts['query']) ? '' : "?{$parts['query']}" ) . " HTTP/1.0\r\n";
		 $request .= "Host: {$parts['host']}\r\n";
		 $request .= "Connection: Close\r\n";
		 $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		 $request .= 'Content-Length: ' . strlen($parts['post']) . "\r\n";

		 // Send cookies.
		 $cookies = '';

		 foreach ($_COOKIE as $k => $v)
		 {
		 $cookies .= urlencode($k) . '=' . urlencode($v) . '; ';
		 }

		 $request .= 'Cookie: ' . substr($cookies, 0, -2) . "\r\n\r\n";

		 // Send post data.
		 $request .= $parts['post'];

		 fwrite($fp, $request);

		 // Read response.
		 $resp = '';

		 while (!feof($fp))
		 {
		 $resp .= fgets($fp);
		 }

		 fclose($fp);

		 if (empty($resp))
		 {
		 $this->app->enqueueMessage(JText::_('JSN_POWERADMIN_FAILED_TO_UNINSTALL'));
		 }
		 }*/
	}

	/**
	 * Handle onExtensionAfterInstall event to automatically override some files.
	 *
	 * @param   JInstaller  $installer  Joomla installer object.
	 * @param   int         $eid        ID of the extension being installed.
	 *
	 * @return  void
	 */
	public function onExtensionAfterInstall($installer, $eid)
	{
		// Check if the 'Yjsg Framework' plugin is installed?
		$ext = $this->dbo->setQuery(
			$this->dbo->getQuery(true)
				->select('*')
				->from('#__extensions')
				->where("extension_id = {$eid}"))
			->loadObject();

		if ($ext->type == 'plugin' && $ext->folder == 'system' && $ext->element == 'jsntplframework')
		{
			// Override some files of JSN Template Framework plugin.
			foreach (array(
				'plugins/system/jsntplframework/includes/core/j3x/jsntplmodulehelper.php'
			) as $file)
			{
				// Always backup the existing file first.
				JFile::copy(JPATH_ROOT . "/{$file}", JPATH_ROOT . "/{$file}.backed-up-by-poweradmin2-at-" . date('YmdHis'));

				// Then, copy our file over.
				JFile::copy("{$this->override}/{$file}", JPATH_ROOT . "/{$file}");
			}
		}
		elseif ($ext->type == 'plugin' && $ext->folder == 'system' && $ext->element == 'yjsg')
		{
			// Override some files of the Yjsg Framework plugin.
			foreach (array(
				/*'plugins/system/yjsg/includes/yjsgcore/classes/extend/38/component/view.php',
				'plugins/system/yjsg/includes/yjsgcore/classes/extend/38/layout/file.php',*/
				'plugins/system/yjsg/includes/yjsgcore/classes/extend/38/module/helper.php'
			) as $file)
			{
				// Always backup the existing file first.
				JFile::copy(JPATH_ROOT . "/{$file}", JPATH_ROOT . "/{$file}.backed-up-by-poweradmin2-at-" . date('YmdHis'));

				// Then, copy our file over.
				JFile::copy("{$this->override}/{$file}", JPATH_ROOT . "/{$file}");
			}
		}
	}

	/**
	 * Handle onExtensionBeforeUninstall event to automatically restore the default admin menu of Joomla.
	 *
	 * @param   int  $eid  ID of the extension being uninstalled.
	 *
	 * @return  void
	 */
	public function onExtensionBeforeUninstall($eid)
	{
		// Get extension info.
		$ext = $this->dbo->setQuery("SELECT element FROM #__extensions WHERE extension_id = {$eid}")->loadResult();

		if ($ext == 'com_poweradmin2')
		{
			// Make sure the admin menu module has at least 1 instance.
			$exist = $this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->select('id')
					->from('#__modules')
					->where('client_id = 1')
					->where("position = 'menu'")
					->where("module = 'mod_menu'"))
				->loadResult();

			if (empty($exist))
			{
				// Get object to working with modules table.
				$module = JTable::getInstance('module');

				// Load module instance.
				$module->load(array(
					'module' => 'mod_menu'
				));

				// Update module instance.
				$module->title = 'Admin Menu';
				$module->access = 3;
				$module->ordering = 0;
				$module->published = 1;
				$module->client_id = 1;
				$module->module = 'mod_menu';
				$module->position = 'menu';

				// Store module instance.
				$module->store();

				// Set module instance to show in all page.
				if ((int) $module->id > 0)
				{
					try
					{
						// Remove all menu assignment records associated with this module instance.
						$this->dbo->setQuery(
							$this->dbo->getQuery(true)
								->delete('#__modules_menu')
								->where("moduleid = {$module->id}"))
							->execute();

						// Show this module instance in all page.
						$this->dbo->setQuery(
							$this->dbo->getQuery(true)
								->insert('#__modules_menu')
								->columns('moduleid, menuid')
								->values("{$module->id}, 0"))
							->execute();
					}
					catch (Exception $e)
					{
						throw $e;
					}
				}
			}

			// Toggle the default admin menu of Joomla.
			$this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->update('#__modules')
					->set('published = 1')
					->where('client_id = 1')
					->where("position = 'menu'")
					->where("module = 'mod_menu'"))
				->execute();
		}
	}

	/**
	 * Watch settings changes to toggle admin bar.
	 *
	 * @param   array   &$settings  Current component settings.
	 * @param   string  $component  Component that has settings changed.
	 *
	 * @return  void
	 */
	public function onJsnExtFwAfterSaveComponentSettings(&$settings, $component)
	{
		if ($component === 'com_poweradmin2' && array_key_exists('enable_adminbar', $settings))
		{
			// Toggle the default admin menu of Joomla.
			$this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->update('#__modules')
					->set('published = ' . ( $settings['enable_adminbar'] ? 0 : 1 ))
					->where('client_id = 1')
					->where("position = 'menu'")
					->where("module = 'mod_menu'"))
				->execute();

			// Make sure the admin bar module has at least 1 instance.
			$exist = $this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->select('id')
					->from('#__modules')
					->where('client_id = 1')
					->where("position = 'menu'")
					->where("module = 'mod_poweradminbar'"))
				->loadResult();

			if (empty($exist))
			{
				// Get object to working with modules table.
				$module = JTable::getInstance('module');

				// Load module instance.
				$module->load(array(
					'module' => 'mod_poweradminbar'
				));

				// Update module instance.
				$module->title = 'Admin Bar';
				$module->access = 3;
				$module->ordering = 0;
				$module->published = 1;
				$module->client_id = 1;
				$module->module = 'mod_poweradminbar';
				$module->position = 'menu';

				// Store module instance.
				$module->store();

				// Set module instance to show in all page.
				if ((int) $module->id > 0)
				{
					try
					{
						// Remove all menu assignment records associated with this module instance.
						$this->dbo->setQuery(
							$this->dbo->getQuery(true)
								->delete('#__modules_menu')
								->where("moduleid = {$module->id}"))
							->execute();

						// Show this module instance in all page.
						$this->dbo->setQuery(
							$this->dbo->getQuery(true)
								->insert('#__modules_menu')
								->columns('moduleid, menuid')
								->values("{$module->id}, 0"))
							->execute();
					}
					catch (Exception $e)
					{
						throw $e;
					}
				}
			}

			// Toggle the admin bar of JSN PowerAdmin.
			$this->dbo->setQuery(
				$this->dbo->getQuery(true)
					->update('#__modules')
					->set('published = ' . ( $settings['enable_adminbar'] ? 1 : 0 ))
					->where('client_id = 1')
					->where("position = 'menu'")
					->where("module = 'mod_poweradminbar'"))
				->execute();
		}
	}

	/**
	 * Register custom input controls.
	 *
	 * @param   array   &$paths  Array of path to look for custom input controls
	 *
	 * @return  void
	 */
	public function onJsnExtFwGetInputControlPath(&$paths)
	{
		$paths[dirname(__FILE__) . '/assets/js/inputs'] = JUri::root(true) . '/plugins/system/poweradmin2/assets/js/inputs';
	}

	/**
	 * Register options for customizing component output when previewing a page.
	 *
	 * @param   object  $item      A menu item object.
	 * @param   array   &$options  Option type declaration.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetComponentOutputOptions($item, &$options)
	{
		$options = array_merge($options,
			array(
				'visibility' => array(
					'singleScope' => array(
						array(
							'label' => JText::_('JSN_POWERADMIN_SHOW_THIS_ELEMENT'),
							'condition' => array(
								'visibility-value = 0'
							)
						),
						array(
							'label' => JText::_('JSN_POWERADMIN_HIDE_THIS_ELEMENT'),
							'condition' => array(
								'visibility-value = 1'
							)
						)
					),
					'multiScope' => array(
						array(
							'label' => JText::_('JSN_POWERADMIN_SHOW_THIS_ELEMENT_IN'),
							'condition' => array(
								'visibility-value = 0'
							)
						),
						array(
							'label' => JText::_('JSN_POWERADMIN_HIDE_THIS_ELEMENT_IN'),
							'condition' => array(
								'visibility-value = 1'
							)
						)
					)
				),
				'linked' => array(
					'singleScope' => array(
						array(
							'label' => JText::_('JSN_POWERADMIN_SHOW_THIS_ELEMENT_AS_LINK'),
							'condition' => array(
								'linked-value = 0'
							)
						),
						array(
							'label' => JText::_('JSN_POWERADMIN_SHOW_THIS_ELEMENT_AS_TEXT'),
							'condition' => array(
								'linked-value = 1'
							)
						)
					),
					'multiScope' => array(
						array(
							'label' => JText::_('JSN_POWERADMIN_SHOW_THIS_ELEMENT_AS_LINK_IN'),
							'condition' => array(
								'linked-value = 0'
							)
						),
						array(
							'label' => JText::_('JSN_POWERADMIN_SHOW_THIS_ELEMENT_AS_TEXT_IN'),
							'condition' => array(
								'linked-value = 1'
							)
						)
					)
				)
			));
	}

	/**
	 * Get link to edit content item associated with the specified menu item.
	 *
	 * @param   object  $item     A menu item object.
	 * @param   array   &$action  Action declaration.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetContentItemEditLink($item, &$action)
	{
		if (!$item || $item->type != 'component')
		{
			return;
		}

		// Parse item link.
		parse_str(substr($item->link, strpos($item->link, '?') + 1), $query);

		if (!isset($query['view']))
		{
			return;
		}

		// Generate edit link.
		switch ($query['option'])
		{
			case 'com_content':
				if ($query['view'] == 'article')
				{
					$action['href'] = JRoute::_("index.php?option=com_content&task=article.edit&id={$query['id']}", false);
				}
			break;

			case 'com_contact':
				if ($query['view'] == 'contact')
				{
					$action['href'] = JRoute::_("index.php?option=com_contact&task=contact.edit&id={$query['id']}", false);
				}
			break;

			case 'com_newsfeeds':
				if ($query['view'] == 'newsfeed')
				{
					$action['href'] = JRoute::_("index.php?option=com_contact&task=newsfeed.edit&id={$query['id']}", false);
				}
			break;

			case 'com_users':
				if ($query['view'] == 'profile')
				{
					$action['href'] = JRoute::_("index.php?option=com_contact&task=user.edit&id={$query['id']}", false);
				}
			break;
		}
	}

	/**
	 * Get supported search coverages.
	 *
	 * @param   string  &$coverage  The coverage to search for results.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetSearchCoverages(&$coverages)
	{
		// Define supported search coverages.
		$coverages = array_merge($coverages,
			array(
				'articles' => JText::_('JSN_POWERADMIN_COVERAGE_ARTICLES'),
				'categories' => JText::_('JSN_POWERADMIN_COVERAGE_CATEGORIES'),
				'menus' => JText::_('JSN_POWERADMIN_COVERAGE_MENUS'),
				'users' => JText::_('JSN_POWERADMIN_COVERAGE_USERS'),
				'components' => JText::_('JSN_POWERADMIN_COVERAGE_COMPONENTS'),
				'modules' => JText::_('JSN_POWERADMIN_COVERAGE_MODULES'),
				'plugins' => JText::_('JSN_POWERADMIN_COVERAGE_PLUGINS'),
				'templates' => JText::_('JSN_POWERADMIN_COVERAGE_TEMPLATES')
			));
	}

	/**
	 * Get search results for the specified coverage.
	 *
	 * @param   string  $coverage  The coverage to search for results.
	 * @param   string  $keyword   The keyword to search for results.
	 * @param   array   &$results  Array of search result.
	 */
	public function onPowerAdminGetSearchResultsForCoverage($coverage, $keyword, &$results)
	{
		// Build query to search database for results.
		$qry = $this->dbo->getQuery(true);

		switch ($coverage)
		{
			case 'articles':
				$qry->select('id, title, CONCAT(introtext, `fulltext`) AS description')
					->from('#__content')
					->where(
					'(' . implode(' OR ',
						array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
							'introtext LIKE ' . $qry->quote("%{$keyword}%"),
							'`fulltext` LIKE ' . $qry->quote("%{$keyword}%")
						)) . ')');

				// Filter trashed content?
				if (!(int) $this->cfg['search_trashed'])
				{
					$qry->where('state > -2');
				}
			break;

			case 'categories':
				$qry->select('id, title, description, extension')
					->from('#__categories')
					->where(
					'(' . implode(' OR ',
						array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
							'description LIKE ' . $qry->quote("%{$keyword}%")
						)) . ')');

				// Filter trashed content?
				if (!(int) $this->cfg['search_trashed'])
				{
					$qry->where('published > -2');
				}
			break;

			case 'menus':
				$qry->select('id, title')
					->from('#__menu')
					->where('title LIKE ' . $qry->quote("%{$keyword}%"));

				// Filter trashed content?
				if (!(int) $this->cfg['search_trashed'])
				{
					$qry->where('published > -2');
				}
			break;

			case 'users':
				$qry->select('id, name AS title')
					->from('#__users')
					->where('name LIKE ' . $qry->quote("%{$keyword}%"));
			break;

			case 'components':
				$qry->select('name AS title, element')
					->from('#__extensions')
					->where('type = "component"')
					->where('name LIKE ' . $qry->quote("%{$keyword}%"));
			break;

			case 'modules':
				$qry->select('id, title')
					->from('#__modules')
					->where('title LIKE ' . $qry->quote("%{$keyword}%"));

				// Filter trashed content?
				if (!(int) $this->cfg['search_trashed'])
				{
					$qry->where('published > -2');
				}
			break;

			case 'plugins':
				$qry->select('extension_id, name AS title')
					->from('#__extensions')
					->where('type = "plugin"')
					->where('name LIKE ' . $qry->quote("%{$keyword}%"));
			break;

			case 'templates':
				$qry->select('id, title')
					->from('#__template_styles')
					->where(
					'(' . implode(' OR ',
						array(
							'title LIKE ' . $qry->quote("%{$keyword}%"),
							'template LIKE ' . $qry->quote("%{$keyword}%")
						)) . ')');
			break;

			default:
				return;
			break;
		}

		// Set limitation.
		$this->dbo->setQuery($qry, 0, (int) $this->cfg['search_result_num']);

		// Get search results.
		$results = $this->dbo->loadObjectList();

		// Prepare results.
		foreach ($results as &$result)
		{
			switch ($coverage)
			{
				case 'articles':
					$result->description = preg_replace('/\{[^\}]+\}/', '', $result->description);
					$result->link = JRoute::_("index.php?option=com_content&task=article.edit&id={$result->id}", false);
				break;

				case 'categories':
					$result->link = JRoute::_(
						"index.php?option=com_categories&extension={$result->extension}&task=category.edit&id={$result->id}", false);
				break;

				case 'menus':
					$result->link = JRoute::_("index.php?option=com_menus&task=item.edit&id={$result->id}", false);
				break;

				case 'users':
					$result->link = JRoute::_("index.php?option=com_users&task=user.edit&id={$result->id}", false);
				break;

				case 'components':
					$result->link = JRoute::_("index.php?option={$result->element}", false);
				break;

				case 'modules':
					$result->link = JRoute::_("index.php?option=com_modules&task=module.edit&id={$result->id}", false);
				break;

				case 'plugins':
					$result->link = JRoute::_("index.php?option=com_plugins&task=plugin.edit&extension_id={$result->extension_id}", false);
				break;

				case 'templates':
					$result->link = JRoute::_("index.php?option=com_templates&task=style.edit&id={$result->id}", false);
				break;
			}
		}
	}

	/**
	 * Get search page and name of keyword input control for the specified search coverage.
	 *
	 * @param   string  $coverage  Coverage to get search page for.
	 * @param   string  &$page     Current search page.
	 * @param   string  &$name     Name of input control for entering keyword.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetSearchPageForCoverage($coverage, &$page, &$name)
	{
		switch ($coverage)
		{
			case 'articles':
				$page = JRoute::_('index.php?option=com_content', false);
				$name = 'filter[search]';
			break;

			case 'categories':
				$page = JRoute::_('index.php?option=com_categories&view=categories&extension=com_content', false);
				$name = 'filter[search]';
			break;

			case 'menus':
				$page = JRoute::_('index.php?option=com_menus&view=items', false);
				$name = 'filter[search]';
			break;

			case 'users':
				$page = JRoute::_('index.php?option=com_users&view=users', false);
				$name = 'filter[search]';
			break;

			case 'components':
				$page = JRoute::_('index.php?option=com_installer&view=manage&filter[type]=component', false);
				$name = 'filter[search]';
			break;

			case 'modules':
				$page = JRoute::_('index.php?option=com_modules', false);
				$name = 'filter[search]';
			break;

			case 'plugins':
				$page = JRoute::_('index.php?option=com_plugins', false);
				$name = 'filter[search]';
			break;

			case 'templates':
				$page = JRoute::_('index.php?option=com_templates', false);
				$name = 'filter[search]';
			break;
		}
	}

	/**
	 * Define edit to preview link mapping for built-in components of Joomla.
	 *
	 * @param   array  &$mapping  Current mapping.
	 *
	 * @return  void
	 */
	public function onPowerAdminGetEditToPreviewLinkMapping(&$mapping)
	{
		// Menu item.
		$mapping['index\.php\?option=com_menus&view=item&client_id=0&layout=edit&id=(\d+)'] = 'index.php?Itemid=$1';

		// Content article.
		$mapping['index\.php\?option=com_content&view=article&layout=edit&id=(\d+)'] = 'index.php?option=com_content&view=article&id=$1';

		// Content category.
		$mapping['index\.php\?option=com_categories&view=category&layout=edit&id=(\d+)&extension=com_content'] = 'index.php?option=com_content&view=category&id=$1';

		// Contact.
		$mapping['index\.php\?option=com_contact&view=contact&layout=edit&id=(\d+)'] = 'index.php?option=com_contact&view=contact&id=$1';

		// Contact category.
		$mapping['index\.php\?option=com_categories&view=category&layout=edit&id=(\d+)&extension=com_contact'] = 'index.php?option=com_contact&view=category&id=$1';

		// Newsfeed.
		$mapping['index\.php\?option=com_newsfeeds&view=newsfeed&layout=edit&id=(\d+)'] = 'index.php?option=com_newsfeeds&view=newsfeed&id=$1';

		// Newsfeed category.
		$mapping['index\.php\?option=com_categories&view=category&layout=edit&id=(\d+)&extension=com_newsfeeds'] = 'index.php?option=com_newsfeeds&view=category&id=$1';
	}

	/**
	 * Handle redirect.
	 *
	 * @return  void
	 */
	public function handleRedirect()
	{
		// Check if the current request is for previewing a page?
		if (( method_exists($this->app, 'isClient') ? $this->app->isClient('site') : $this->app->isSite() ) && $this->preview)
		{
			// Get the current headers.
			$headers = headers_list();

			// Process redirect header.
			foreach ($headers as $header)
			{
				if (stripos($header, 'Location: ') !== false)
				{
					// Get the new URL.
					$url = explode(':', $header);
					$url = trim($url[1]);

					// Refine URL.
					if (in_array($url, array(
						'http',
						'https'
					)))
					{
						exit();
					}
					else
					{
						// Get the current request parameters.
						parse_str(JUri::getInstance()->toString(array(
							'query'
						)), $params);

						// Remove some built-in parameters of Joomla.
						foreach ($params as $k => $v)
						{
							if (in_array($k,
								array(
									'option',
									'task',
									'view',
									'layout',
									'tmpl',
									'Itemid'
								)))
							{
								unset($params[$k]);
							}
						}

						// Append page preview related parameters to the new URL.
						$url .= ( strpos($url, '?') !== false ? '&' : '?' ) .
							 preg_replace('/^%[0-9A-F]{2,2}/', '', http_build_query($params));
					}

					// Set new header.
					header("Location: {$url}");
				}
			}
		}
	}
}
