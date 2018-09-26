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

// Import necessary Joomla libraries
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * System plugin for initializing JSN Framework.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class PlgSystemJSNFramework extends JPlugin
{
	/**
	 * @var JApplication
	 */
	private static $_app = null;

	/**
	 * Register JSN Framework initialization.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		// Initialize JSN Framework
		require_once dirname(__FILE__) . '/libraries/loader.php';
		require_once dirname(__FILE__) . '/jsnframework.defines.php';

		// Get application object
		self::$_app = JFactory::getApplication();

		// Get requested component, view and task
		$this->option	= self::$_app->input->getCmd('option');
		$this->view		= self::$_app->input->getCmd('view');
		$this->task		= self::$_app->input->getCmd('task');

		// Redirect to update page if necessary
		if ($this->option == 'com_installer' AND $this->view == 'update' AND $this->task == 'update.update' AND count($cid = (array) self::$_app->input->getVar('cid', array())))
		{
			// Check if extension to updated is JoomlaShine product
			$db	= JFactory::getDbo();
			$q	= $db->getQuery(true);

			$q->select('element');
			$q->from('#__updates');
			$q->where('update_id = ' . (int) $cid[0]);

			$db->setQuery($q);
			$ext = $db->loadResult();

			if (in_array($ext, JSNVersion::$products))
			{
				return self::$_app->redirect('index.php?option=' . $ext . '&view=update');
			}
		}

		// Get active language
		$lang = JFactory::getLanguage();

		// Check if language file exists for active language
		if ( ! file_exists(JPATH_ROOT . '/administrator/language/' . $lang->get('tag') . '/' . $lang->get('tag') . '.plg_system_jsnframework.ini'))
		{
			// If requested component has the language file, install then load it
			if (file_exists(JPATH_ROOT . '/administrator/components/' . $this->option . '/language/admin/' . $lang->get('tag') . '/' . $lang->get('tag') . '.plg_system_jsnframework.ini'))
			{
				JSNLanguageHelper::install((array) $lang->get('tag'), false, true);
				$lang->load('plg_system_jsnframework', JPATH_ADMINISTRATOR, null, true);
			}
			// Otherwise, try to load language file from plugin directory
			else
			{
				$lang->load('plg_system_jsnframework', JSN_PATH_FRAMEWORK, null, true);
			}
		}
		else
		{
			$lang->load('plg_system_jsnframework', JPATH_ADMINISTRATOR, null, true);
		}

		// Disable notice and warning by default for our products.
		// The reason for doing this is if any notice or warning appeared then handling JSON string will fail in our code.
		if (function_exists('error_reporting') AND in_array($this->option, JSNVersion::$products))
		{
			error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
		}

		// Set event handlers to detect and update dependency installation / removal
		self::$_app->registerEvent('onExtensionAfterInstall',		'jsnExtFrameworkUpdateDependencyAfterInstallExtension');
		self::$_app->registerEvent('onExtensionBeforeUninstall',	'jsnExtFrameworkUpdateDependencyBeforeUninstallExtension');

	}

	/**
	 * Event handler to re-parse request URI.
	 *
	 * @return  void
	 */
	public function onAfterRoute()
	{
		// Get installed Joomla version
		$JVersion 	= new JVersion;
		$JVersion 	= $JVersion->getShortVersion();
		$option 	= trim((string) $this->option);

		if (self::$_app->isAdmin() && version_compare($JVersion, '3.0', '>=') && in_array($option, JSNVersion::$products))
		{
			$manifestFile = JPATH_ADMINISTRATOR . '/components/' . $option . '/' . str_replace('com_', '', $option) . '.xml';
			if (file_exists($manifestFile))
			{
				$xml 	= JSNUtilsXml::load($manifestFile);
				$attr 	= $xml->attributes();

				if (count($attr))
				{
					if (isset($attr['version']) && (string) $attr['version'] != '')
					{
						$version = (string) $attr['version'];

						if ($option == 'com_imageshow')
						{
							$version = str_replace('.x', '.0', $version);
						}

						if (version_compare($version, '3.0', '<'))
						{
							// Check if all JSN Extensions are compatible with Joomla 3.x, if not, redirect to index.php and show a warning message
							self::$_app->enqueueMessage(JText::sprintf('You are running a Joomla 2.5 version of %1$s on Joomla 3.x. Please download %1$s for Joomla 3.x and reinstall via Joomla! Installer to fix the problem.', 'JSN ' . ucfirst(str_replace('com_', '', $option))), 'warning');
							self::$_app->redirect('index.php');
							return false;
						}
					}
				}
			}
		}

		// Make sure our onAfterRender event handler is the last one executed
		self::$_app->registerEvent('onAfterRender', 'jsnExtFrameworkFinalize');
	}

	/**
	 * Proceed positions rendering
	 *
	 * Remove default tp=1 layout, replace by jsn style to
	 * show page positions
	 *
	 * @return  void
	 */
	public function onAfterDispatch()
	{
		if ( ! JSNVersion::isJoomlaCompatible(JSN_FRAMEWORK_REQUIRED_JOOMLA_VER))
		{
			return;
		}

		// Keep this for joomla 2.5. Will be deprecated.
		if (JSNVersion::isJoomlaCompatible('2.5') AND ! JSNVersion::isJoomlaCompatible('3.0'))
		{
			if (self::$_app->isAdmin()
				AND self::$_app->input->getVar('format', '') != 'raw'
				AND self::$_app->input->getVar('option', '') == 'com_poweradmin'
				AND self::$_app->input->getVar('view') != 'update'
				AND self::$_app->input->getVar('view') != 'changeposition')
			{
				$version = PoweradminHelper::getVersion();

				if (version_compare($version, '1.1.3', '>='))
				{
					$JSNMedia = JSNFactory::getMedia();
					$JSNMedia->addMedia();
				}
			}
		}

		if (self::$_app->input->getCmd('poweradmin', 0) == 1)
		{
			$jsnHelper = JSNPositionsModel::_getInstance();
			$jsnHelper->renderEmptyComponent();
			$jsnHelper->renderEmptyModule();
		}
	}

	/**
	 * Before render needs using this function to make format of HTML of modules
	 *
	 * @return  Changed HTML format
	 */
	public function onBeforeRender()
	{
		if ( ! JSNVersion::isJoomlaCompatible(JSN_FRAMEWORK_REQUIRED_JOOMLA_VER))
		{
			return;
		}

		if (self::$_app->isAdmin())
		{
			// Ask user to review JoomlaShine product on JED
			$this->askForReview();

			/* Initialize edition manager.
			JSNHtmlHelper::loadEditionManager($this->option, 'jsn/test-edition');*/
		}
		elseif (JSNVersion::isJoomlaCompatible('3.0') AND self::$_app->input->getCmd('poweradmin', 0) == 1)
		{
			$jsnHelper = JSNPositionsModel::_getInstance();
			$jsnHelper->renderEmptyModule();
		}
	}

	/**
	 * Do some output manipulation.
	 *
	 * Auto-inject <b>jsn-master tmpl-nameOfDefaultTemplate</b> into the class
	 * attribute of <b>&lt;body&gt;</b> tag if not already exists. This
	 * automation only affects backend page.
	 *
	 * @return  void
	 */
	public static function onAfterRender()
	{
		// Make sure the remaining process is executed in last order
		if ( ! defined('JSN_EXTFW_LAST_EXECUTION'))
		{
			return;
		}

		// Get active component
		$option = self::$_app->input->getCmd('option');

		// Get the rendered HTML code
		$html = JResponse::getBody();

		if (self::$_app->input->getVar('poweradmin'))
		{
			if (preg_match_all('#<a[^\>]*href\s*=\s*[\'"]([^"]*[^"]+)[\'"]#i', $html, $ms, PREG_SET_ORDER))
			{
				foreach ($ms as $m)
				{
					$html = str_replace($m[0], str_replace($m[1], 'javascript:void(0)', $m[0]), $html);
				}
			}
		}

		// Do some fixes if this is admin page
		if (self::$_app->isAdmin())
		{
			// Fix asset links for Joomla 2.5
			if (JSNVersion::isJoomlaCompatible('2.5') AND ! JSNVersion::isJoomlaCompatible('3.0') AND strpos($html, JSN_URL_ASSETS) !== false)
			{
				// Get asset link
				if (preg_match_all('#<(link|script)([^\>]*)(href|src)="([^"]*' . JSN_URL_ASSETS . '[^"]+)"#i', $html, $matches, PREG_SET_ORDER))
				{
					foreach ($matches AS $match)
					{
						// Do replace
						$html = str_replace(
							$match[0],
							'<' . $match[1] . $match[2] . $match[3] . '="' . dirname(dirname($match[4])) . '/' . str_replace('.', '-', basename(dirname($match[4]))) . '/' . basename($match[4]) . '"',
							$html
						);
					}
				}
			}

			// Remove our extensions from the Joomla 3.0's global config page
			if ($option == 'com_config' AND JSNVersion::isJoomlaCompatible('3.0'))
			{
				$html = preg_replace(
					'#<li>[\r\n]+\t+<a href="index.php\?option=com_config&view=component&component=(' . implode('|', JSNVersion::$products) . ')">[^<]+</a>[\r\n]+\t+</li>#',
					'',
					$html
				);
			}

			// Alter body tag
			if (preg_match('/<body[^>]*>/i', $html, $match) AND strpos($match[0], 'jsn-master tmpl-' . self::$_app->getTemplate()) === false)
			{
				if (strpos($match[0], 'class=') === false)
				{
					$match[1] = substr($match[0], 0, -1) . ' class=" jsn-master tmpl-' . self::$_app->getTemplate() . ' ">';
				}
				else
				{
					$match[1] = str_replace('class="', 'class=" jsn-master tmpl-' . self::$_app->getTemplate() . ' ', $match[0]);
				}

				$html = str_replace($match[0], $match[1], $html);
			}

			if (JSNVersion::isJoomlaCompatible('3.2'))
			{
				// Clean-up HTML5 fall-back script if running on Joomla 3.2
				if (in_array($option, JSNVersion::$products)
					AND preg_match('#[\r\n][\s\t]+<script src="[^"]*/media/system/js/html5fallback(-uncompressed)?\.js"[^>]+></script>#', $html, $match))
				{
					$html = str_replace($match[0], '', $html);
				}

				// Temporary fix jQuery version conflict on Joomla 3.2
				$pos = strpos($html, JSN_URL_ASSETS . '/3rd-party/jquery/jquery.min.js');

				if ($pos !== false AND preg_match('#<script[^>]+src="' . JSN_URL_ASSETS . '/3rd-party/jquery/jquery.min.js"[^>]*></script>#', $html, $match))
				{
					$html = explode($match[0], $html);

					// Do some tricks on multiple jQuery instances
					$script = '<script type="text/javascript">'
						. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
						. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQueryBackup = jQuery));'
						. "\n\t" . '</script>'
						. "\n\t" . $match[0];

					// Update document header
					$html[0] .= $script;

					// Truncate content
					$html[2] = substr($html[1], strpos($html[1], '</head>'));
					$html[1] = substr($html[1], 0, strpos($html[1], '</head>'));

					if (preg_match('#<script[^>]+src="[^"]*/media/jui/js/jquery(\.min)?\.js(\?(.*))?"[^>]*></script>#', $html[1], $match))
					{
						$script = '<script type="text/javascript">'
							. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
							. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQuery = jQuery));'
							. "\n\t" . '</script>'
							. "\n\t" . $match[0];

						// Update document header
						$html[1] = str_replace($match[0], $script, $html[1]);
					}
					elseif (preg_match('#<script[^>]+src="[^"]*/js/template\.js[^"]*"[^>]*></script>#', $html[1], $match))
					{
						$script = '<script type="text/javascript">'
							. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
							. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQuery = jQuery));'
							. "\n\t\t" . '(!JoomlaShine.jQueryBackup || (jQuery = JoomlaShine.jQueryBackup));'
							. "\n\t" . '</script>'
							. "\n\t" . $match[0];

						// Update document header
						$html[1] = str_replace($match[0], $script, $html[1]);
					}
					elseif (preg_match('#<script type="text/javascript">#', $html[1], $match))
					{
						$script = '<script type="text/javascript">'
							. "\n\t\t" . '(JoomlaShine = window.JoomlaShine || {});'
							. "\n\t\t" . '(!window.jQuery || (JoomlaShine.jQuery = jQuery));'
							. "\n\t\t" . '(!JoomlaShine.jQueryBackup || (jQuery = JoomlaShine.jQueryBackup));';

						// Update document header
						$html[1] = str_replace($match[0], $script, $html[1]);
					}

					$html = implode($html);

					// Fix for (function($) { ... })(jQuery);
					$tmp = preg_split('/\}[\s\t\r\n]*\)*\([^\r\n]*jQuery[^\r\n]*\)\s*;?/', $html);
					$html = '';
					$i = 0;

					foreach ($tmp AS $part)
					{
						$i++;

						if ($i == count($tmp))
						{
							$html .= $part;
						}
						else
						{
							$parts = preg_split('/\(\s*function\s*\(\s*\$\s*\)\s*\{/', $part, 2);

							if (count($parts) < 2)
							{
								$html .= $part;
							}
							elseif (stripos($parts[1], 'jsn') !== false)
							{
								$html .= "{$parts[0]}(function($) {{$parts[1]}})((window.JoomlaShine && JoomlaShine.jQuery) ? JoomlaShine.jQuery : jQuery);";
							}
							else
							{
								$html .= "{$parts[0]}(function($) {{$parts[1]}})(jQuery);";
							}
						}
					}

					// Remove JSN ImageShow's buggy fix for jQuery conflict
					if (strpos($html, 'administrator/components/com_imageshow/assets/js/joomlashine/jquery.safe.element.js') !== false)
					{
						$html = preg_replace('#[\r\n][\s\t]*<script[^>]+src="[^"]*/joomlashine/jquery.safe.element.js[^"]*"[^>]*></script>#', '', $html);
					}
				}
			}
		}

		// Attach JS declaration
		$html = str_replace('</head>', JSNHtmlAsset::buildHeader() . '</head>', $html);

		// Fix compatibility problem between require.js and RokPad editor
		if (strpos($html, '/plugins/editors/rokpad/'))
		{
			if (preg_match_all('#[\r\n][\s\t]*<script[^>]+src="[^"]*/plugins/editors/rokpad/[^"]+"[^>]*></script>#', $html, $matches, PREG_SET_ORDER))
			{
				foreach ($matches AS $match)
				{
					// Clean the script tag from its original position
					$html = str_replace($match[0], '', $html);

					// Inject the removed script tag into the end of head section
					$html = str_replace('</head>', $match[0] . '</head>', $html);
				}
			}
		}

		// Set new response body
		JResponse::setBody($html);
	}

	/**
	 * Handle Ajax requests.
	 *
	 * @return  void
	 */
	public function onAjaxJSNFramework()
	{
		// Set necessary headers.
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		// Execute the requested Ajax action.
		JSNAjax::execute();

		// Exit immediately to prevent Joomla from processing further.
		exit;
	}

	/**
	 * Ask user to review JoomlaShine product on JED.
	 *
	 * @return  void
	 */
	private function askForReview()
	{
		/* Continue only if this is admin page of JoomlaShine product
		if (self::$_app->isAdmin() AND in_array($this->option, JSNVersion::$products))
		{
			// Get product options
			$config = JSNConfigHelper::get($this->option);

			if ( (int) $config->get('review_popup', 1) )
			{
				// Get time difference
				$time = time();
				$last = $config->get('last_ask_for_review', 0);

				if ($last == 0)
				{
					$last = filemtime(JPATH_ROOT . "/administrator/components/{$this->option}/" . substr($this->option, 4) . '.xml');
				}

				// Check if it's time to ask for review
				if ($time - $last >= REVIEW_POPUP_PERIOD)
				{
					// Load script to popup a modal ask user for review
					JSNHtmlAsset::loadScript(
						'jsn/askforreview',
						array(
							'url'		=> JUri::root() . "plugins/system/jsnframework/libraries/joomlashine/choosers/review/index.php?component={$this->option}",
							'language'	=> JSNUtilsLanguage::getTranslated(array('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED'))
						)
					);

					// Get config model
					$model = new JSNConfigModel;

					// Store time of last popup
					$form = $model->getForm(array(), true, JPATH_ROOT . '/administrator/components/' . $this->option . '/config.xml');
					$data = array('last_ask_for_review' => $time);

					try
					{
						// Save new configuration
						$model->save($form, $data);
					}
					catch (Exception $e)
					{
						// Do nothing as this is a background process
					}
				}
			}
		}*/
	}
}

/**
 * Finalize response body.
 *
 * @return  void
 */
function jsnExtFrameworkFinalize()
{
	define('JSN_EXTFW_LAST_EXECUTION', 1);
	PlgSystemJSNFramework::onAfterRender();
}

/**
 * Update dependency after an extension is installed.
 *
 * @param   object  $installer   Joomla installer object.
 * @param   mixed   $identifier  Extension ID on installation success, boolean FALSE otherwise.
 *
 * @return  void
 */
function jsnExtFrameworkUpdateDependencyAfterInstallExtension($installer, $identifier)
{
	if (is_integer($identifier))
	{
		// Get installed extension
		$ext = basename($installer->getPath('extension_administrator'));

		// Check if our product is installed
		if (in_array($ext, JSNVersion::$products))
		{
			// Build query to get dependency installation status
			$db	= JFactory::getDbo();
			$q	= $db->getQuery(true);

			$q->select('manifest_cache, custom_data, params');
			$q->from('#__extensions');
			$q->where("element = 'jsnframework'");
			$q->where("type = 'plugin'", 'AND');
			$q->where("folder = 'system'", 'AND');

			$db->setQuery($q);

			// Load dependency installation status
			$status = $db->loadObject();

			// old params
			$oldParams	 = array();

			if ($status->params != '')
			{
				$oldParams = json_decode($status->params, true);
			}

			$ext = substr($ext, 4);
			$dep = ! empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

			// Update dependency list
			in_array($ext, $dep) OR $dep[] = $ext;
			$status->custom_data = array_unique($dep);

			// Build query to update dependency data
			$q = $db->getQuery(true);

			$q->update('#__extensions');
			$q->set("custom_data = '" . json_encode($status->custom_data) . "'");

			// Backward compatible: keep data in this column for older product to recognize
			$manifestCache = json_decode($status->manifest_cache);
			$manifestCache->dependency = $status->custom_data;

			$q->set("manifest_cache = '" . json_encode($manifestCache) . "'");

			// Backward compatible: keep data in this column also for another old product to recognize
			$params = array_combine($status->custom_data, $status->custom_data);
			if (isset($oldParams['token_key']))
			{
				$params ['token_key'] = $oldParams['token_key'];
			}

			$params = json_encode((object) $params);

			$q->set("params = '" . $params . "'");

			$q->where("element = 'jsnframework'");
			$q->where("type = 'plugin'", 'AND');
			$q->where("folder = 'system'", 'AND');

			$db->setQuery($q);
			$db->execute();
		}
	}
}

/**
 * Update dependency before an extension is being removed.
 *
 * @param   integer  $identifier  Extension ID.
 *
 * @return  boolean
 */
function jsnExtFrameworkUpdateDependencyBeforeUninstallExtension($identifier)
{
	// Get extension being removed
	$ext = JTable::getInstance('Extension');
	$ext->load($identifier);
	$ext = $ext->element;

	// Check if our product is being removed
	if (in_array($ext, JSNVersion::$products))
	{
		// Build query to get dependency installation status
		$db	= JFactory::getDbo();
		$q	= $db->getQuery(true);

		$q->select('manifest_cache, custom_data, params');
		$q->from('#__extensions');
		$q->where("element = 'jsnframework'");
		$q->where("type = 'plugin'", 'AND');
		$q->where("folder = 'system'", 'AND');

		$db->setQuery($q);

		// Load dependency installation status
		$status = $db->loadObject();

		// old params
		$oldParams	 = array();

		if ($status->params != '')
		{
			$oldParams = json_decode($status->params, true);
		}

		$ext	= substr($ext, 4);
		$deps	= ! empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

		// Update dependency tracking
		$status->custom_data = array();

		foreach ($deps AS $dep)
		{
			// Backward compatible: ensure that product is not removed
			// if ($dep != $ext)
			if ($dep != $ext AND is_dir(JPATH_BASE . '/components/com_' . $dep))
			{
				$status->custom_data[] = $dep;
			}
		}

		// Build query to update dependency data
		$q = $db->getQuery(true);

		$q->update('#__extensions');
		$q->set("custom_data = '" . json_encode($status->custom_data) . "'");

		// Backward compatible: keep data in this column for older product to recognize
		$manifestCache = json_decode($status->manifest_cache);
		$manifestCache->dependency = $status->custom_data;

		$q->set("manifest_cache = '" . json_encode($manifestCache) . "'");

		// Backward compatible: keep data in this column also for another old product to recognize
		if (count($status->custom_data))
		{
			$params = array_combine($status->custom_data, $status->custom_data);

			if (isset($oldParams['token_key']))
			{
				$params ['token_key'] = $oldParams['token_key'];
			}

			$params = json_encode((object) $params);
		}
		else
		{
			$params = '';
		}

		$q->set("params = '" . $params . "'");

		$q->where("element = 'jsnframework'");
		$q->where("type = 'plugin'", 'AND');
		$q->where("folder = 'system'", 'AND');

		$db->setQuery($q);
		$db->execute();
	}

	// Always return TRUE so the extension can be removed
	return true;
}
