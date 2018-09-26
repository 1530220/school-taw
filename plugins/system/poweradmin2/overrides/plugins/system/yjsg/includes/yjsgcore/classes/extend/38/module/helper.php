<?php
// no direct access
defined('_JEXEC') or die();

/**
 * Module helper class
 *
 * @package     Joomla.Legacy
 * @subpackage  Module
 * @since       11.1
 */
abstract class JModuleHelper extends YjsgJModuleHelperDefault
{

	/**
	 * Render the module.
	 *
	 * @param   object  $module   A module object.
	 * @param   array   $attribs  An array of attributes for the module (probably from the XML).
	 *
	 * @return  string  The HTML content of the module output.
	 *
	 * @since   11.1
	 */
	public static function renderModule($module, $attribs = array())
	{
		static $chrome;

		// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
		if (JFactory::getApplication()->input->getInt('poweradmin-preview') &&
			 !( $module->title === $module->content && $module->content === $module->position ))
		{
			$module->module = 'mod_dummy';
		}
		// Modified to support Site Manager feature of JSN PowerAdmin :: End -->

		if (constant('JDEBUG'))
		{
			JProfiler::getInstance('Application')->mark('beforeRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		$app = JFactory::getApplication();

		// Yjsg instance
		$yjsg = Yjsg::getInstance();

		// Record the scope.
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $module->module;

		// Get module parameters
		$params = new JRegistry();
		$params->loadString($module->params);

		// Get the template
		$template = $app->getTemplate();

		// Get module path
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
		$path = JPATH_BASE . '/modules/' . $module->module . '/' . $module->module . '.php';

		// Load the module
		if (file_exists($path))
		{
			$lang = JFactory::getLanguage();

			// 1.5 or Core then 1.6 3PD
			$lang->load($module->module, JPATH_BASE, null, false, false) || $lang->load($module->module, dirname($path), null, false,
				false) || $lang->load($module->module, JPATH_BASE, $lang->getDefault(), false, false) ||
				 $lang->load($module->module, dirname($path), $lang->getDefault(), false, false);

			$content = '';
			ob_start();
			include $path;
			$module->content = ob_get_contents() . $content;
			ob_end_clean();
		}

		// Load the module chrome functions
		if (!$chrome)
		{
			$chrome = array();
		}

		include_once JPATH_THEMES . '/system/html/modules.php';
		$chromePath = JPATH_THEMES . '/' . $template . '/html/modules.php';
		//yjsg start
		if ($yjsg->preplugin())
		{

			$yjsgChromePath = YJSGPATH . 'legacy' . YJDS . 'html' . YJDS . 'modules.php';
		}
		else
		{

			$yjsgChromePath = YJSGPATH . 'includes' . YJDS . 'html' . YJDS . 'modules.php';
		}
		//yjsg end
		if (!isset($chrome[$chromePath]))
		{
			if (file_exists($chromePath))
			{
				include_once $chromePath;
			}
			//yjsg start
			elseif (file_exists($yjsgChromePath))
			{
				include_once $yjsgChromePath;
			}
			//yjsg start

			$chrome[$chromePath] = true;
		}

		// Check if the current module has a style param to override template module style
		$paramsChromeStyle = $params->get('style');
		if ($paramsChromeStyle)
		{
			$attribs['style'] = preg_replace('/^(system|' . $template . ')\-/i', '', $paramsChromeStyle);
		}

		// Make sure a style is set
		if (!isset($attribs['style']))
		{
			$attribs['style'] = 'none';
		}

		// Dynamically add outline style
		if ($app->input->getBool('tp') && JComponentHelper::getParams('com_templates')->get('template_positions_display'))
		{
			$attribs['style'] .= ' outline';
		}

		// yjsg module positions
		if ($app->input->getBool('modulepositions'))
		{
			$attribs['style'] .= ' yjsg_module_positions';
		}

		foreach (explode(' ', $attribs['style']) as $style)
		{
			$chromeMethod = 'modChrome_' . $style;

			// Apply chrome and render module
			if (function_exists($chromeMethod))
			{
				$module->style = $attribs['style'];

				ob_start();
				$chromeMethod($module, $params, $attribs);
				$module->content = ob_get_contents();
				ob_end_clean();
			}
		}

		// Revert the scope
		$app->scope = $scope;

		// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
		if (JFactory::getApplication()->input->getInt('poweradmin-preview'))
		{
			$app->triggerEvent('onAfterRenderModule', array(
				&$module,
				&$attribs
			));
		}
		// Modified to support Site Manager feature of JSN PowerAdmin :: End -->

		if (constant('JDEBUG'))
		{
			JProfiler::getInstance('Application')->mark('afterRenderModule ' . $module->module . ' (' . $module->title . ')');
		}

		return $module->content;
	}

	/**
	 * Get modules by position
	 *
	 * @param   string  $position  The position of the module
	 *
	 * @return  array  An array of module objects
	 *
	 * @since   1.5
	 */
	public static function &getModules($position)
	{
		$position = strtolower($position);
		$result = array();
		$input = JFactory::getApplication()->input;

		$modules = & static::load();

		$total = count($modules);

		for ($i = 0; $i < $total; $i++)
		{
			if ($modules[$i]->position == $position)
			{
				$result[] = &$modules[$i];
			}
		}

		if (count($result) == 0)
		{
			// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
			if ($input->getInt('select-position') || $input->getInt('show-empty-position') ||
				 ( $input->getBool('tp') && JComponentHelper::getParams('com_templates')->get('template_positions_display') ))
			{
				$result[0] = static::getModule('mod_' . $position);
				$result[0]->title = $position;
				$result[0]->content = $position;
				$result[0]->position = $position;
			}
			// Modified to support Site Manager feature of JSN PowerAdmin :: End -->
		}

		// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
		if (JFactory::getApplication()->input->getInt('poweradmin-preview'))
		{
			// Trigger an event to provide support for previewing module positions of Gantry based templates.
			JFactory::getApplication()->triggerEvent('onAfterGetModules', array(
				$position,
				&$result
			));
		}
		// Modified to support Site Manager feature of JSN PowerAdmin :: End -->

		return $result;
	}

	/**
	 * Get the path to a layout for a module
	 *
	 * @param   string  $module  The name of the module
	 * @param   string  $layout  The name of the module layout. If alternative layout, in the form template:filename.
	 *
	 * @return  string  The path to the module layout
	 *
	 * @since   11.1
	 */
	public static function getLayoutPath($module, $layout = 'default')
	{
		$template = JFactory::getApplication()->getTemplate();
		$defaultLayout = $layout;

		// Yjsg instance
		$yjsg = Yjsg::getInstance();

		if (strpos($layout, ':') !== false)
		{
			// Get the template and file name from the string
			$temp = explode(':', $layout);
			$template = ( $temp[0] == '_' ) ? $template : $temp[0];
			$layout = $temp[1];
			$defaultLayout = ( $temp[1] ) ? $temp[1] : 'default';
		}

		// Build the template and base path for the layout
		$tPath = JPATH_THEMES . '/' . $template . '/html/' . $module . '/' . $layout . '.php';
		//yjsg start
		if (JFactory::getApplication()->isSite())
		{
			if ($yjsg->preplugin())
			{

				$yjsgPath = YJSGPATH . 'legacy' . YJDS . 'html' . YJDS . $module . YJDS . $layout . '.php';
			}
			else
			{

				$yjsgPath = YJSGPATH . 'includes' . YJDS . 'html' . YJDS . $module . YJDS . $layout . '.php';
			}
		}
		//yjsg end
		$bPath = JPATH_BASE . '/modules/' . $module . '/tmpl/' . $defaultLayout . '.php';
		$dPath = JPATH_BASE . '/modules/' . $module . '/tmpl/default.php';

		// If the template has a layout override use it
		if (file_exists($tPath))
		{
			return $tPath;
		}
		//yjsg start
		elseif (isset($yjsgPath) && file_exists($yjsgPath))
		{
			return $yjsgPath;
		}
		//yjsg end
		elseif (file_exists($bPath))
		{
			return $bPath;
		}
		else
		{
			return $dPath;
		}
	}

	/**
	 * Module list
	 *
	 * @return  array
	 */
	public static function getModuleList()
	{
		$app = \JFactory::getApplication();
		$Itemid = $app->input->getInt('Itemid', 0);
		$groups = implode(',', \JFactory::getUser()->getAuthorisedViewLevels());
		$lang = \JFactory::getLanguage()->getTag();
		$clientId = (int) $app->getClientId();

		// Build a cache ID for the resulting data object
		$cacheId = $groups . $clientId . $Itemid;

		$db = \JFactory::getDbo();

		// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
		if (JFactory::getApplication()->input->getInt('poweradmin-preview'))
		{
			$query = $db->getQuery(true)
				->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, m.published')
				->from('#__modules AS m')
				->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
				->where('e.enabled = 1')
				->where('m.access IN (' . $groups . ')')
				->where('m.client_id = ' . $clientId);

			// Prepare module states.
			$states = array(
				1
			);

			if ($app->input->getInt('show-unpublished-module'))
			{
				$states[] = 0;
			}

			if ($app->input->getInt('show-trashed-module'))
			{
				$states[] = -2;
			}

			$query->where('m.published IN (' . implode(', ', $states) . ')');

			// Prepare module assignment.
			if (!$app->input->getInt('show-unassigned-module'))
			{
				$query->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id')->where(
					'(mm.menuid = ' . (int) $Itemid . ' OR mm.menuid <= 0)');
			}
		}
		else
		{
			$query = $db->getQuery(true)
				->select('m.id, m.title, m.module, m.position, m.content, m.showtitle, m.params, mm.menuid')
				->from('#__modules AS m')
				->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = m.id')
				->where('m.published = 1')
				->join('LEFT', '#__extensions AS e ON e.element = m.module AND e.client_id = m.client_id')
				->where('e.enabled = 1');

			$date = \JFactory::getDate();
			$now = $date->toSql();
			$nullDate = $db->getNullDate();

			$query->where('(m.publish_up = ' . $db->quote($nullDate) . ' OR m.publish_up <= ' . $db->quote($now) . ')')
				->where('(m.publish_down = ' . $db->quote($nullDate) . ' OR m.publish_down >= ' . $db->quote($now) . ')')
				->where('m.access IN (' . $groups . ')')
				->where('m.client_id = ' . $clientId)
				->where('(mm.menuid = ' . $Itemid . ' OR mm.menuid <= 0)');
		}
		// Modified to support Site Manager feature of JSN PowerAdmin :: End -->

		// Filter by language
		if ($app->isClient('site') && $app->getLanguageFilter())
		{
			$query->where('m.language IN (' . $db->quote($lang) . ',' . $db->quote('*') . ')');
			$cacheId .= $lang . '*';
		}

		if ($app->isClient('administrator') && static::isAdminMultilang())
		{
			$query->where('m.language IN (' . $db->quote($lang) . ',' . $db->quote('*') . ')');
			$cacheId .= $lang . '*';
		}

		$query->order('m.position, m.ordering');

		// Set the query
		$db->setQuery($query);

		try
		{
			/** @var \JCacheControllerCallback $cache */
			$cache = \JFactory::getCache('com_modules', 'callback');

			$modules = $cache->get(array(
				$db,
				'loadObjectList'
			), array(), md5($cacheId), false);
		}
		catch (\RuntimeException $e)
		{
			\JLog::add(\JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $e->getMessage()), \JLog::WARNING, 'jerror');

			return array();
		}

		return $modules;
	}

	/**
	 * Clean the module list
	 *
	 * @param   array  $modules  Array with module objects
	 *
	 * @return  array
	 */
	public static function cleanModuleList($modules)
	{
		// Apply negative selections and eliminate duplicates
		$Itemid = \JFactory::getApplication()->input->getInt('Itemid');
		$negId = $Itemid ? -(int) $Itemid : false;
		$clean = array();
		$dupes = array();

		foreach ($modules as $i => $module)
		{
			// The module is excluded if there is an explicit prohibition
			// <!-- Modified to support Site Manager feature of JSN PowerAdmin :: Begin
			$negHit = JFactory::getApplication()->input->getInt('poweradmin-preview') ? false : ( $negId === (int) $module->menuid );
			// Modified to support Site Manager feature of JSN PowerAdmin :: End -->

			if (isset($dupes[$module->id]))
			{
				// If this item has been excluded, keep the duplicate flag set,
				// but remove any item from the modules array.
				if ($negHit)
				{
					unset($clean[$module->id]);
				}

				continue;
			}

			$dupes[$module->id] = true;

			// Only accept modules without explicit exclusions.
			if ($negHit)
			{
				continue;
			}

			$module->name = substr($module->module, 4);
			$module->style = null;
			$module->position = strtolower($module->position);

			$clean[$module->id] = $module;
		}

		unset($dupes);

		// Return to simple indexing that matches the query order.
		return array_values($clean);
	}
}
