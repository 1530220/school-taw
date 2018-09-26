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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * PowerAdmin component helper.
 *
 * @package  JSN_PowerAdmin_2
 * @since    1.0.0
 */
class JSNPowerAdmin2Helper
{

	/**
	 * Add toolbars.
	 *
	 * @param   string  $title   Page title.
	 * @param   string  $screen  The current screen.
	 * @param   string  $icon    Icon on title bar.
	 *
	 * @return  void
	 */
	public static function addToolbars($title, $screen = '', $icon = '')
	{
		// Set the toolbar.
		JToolbarHelper::title($title, $icon);

		// Add help button.
		JToolbarHelper::link('index.php?option=com_poweradmin2&view=help', JText::_('JTOOLBAR_HELP'), 'question-sign');

		// Register sidebar links.
		foreach (array(
			'manage' => array(
				'name' => JText::_('JSN_MENU_SITE_MANAGER'),
				'link' => JRoute::_('index.php?option=com_poweradmin2&view=manage')
			),
			'search' => array(
				'name' => JText::_('JSN_MENU_SITE_SEARCH'),
				'link' => JRoute::_('index.php?option=com_poweradmin2&view=search')
			),
			'reset' => array(
				'name' => JText::_('JSN_MENU_RESET_JOOMLA'),
				'link' => JRoute::_('index.php?option=com_poweradmin2&view=reset')
			),
			'configuration' => array(
				'name' => JText::_('JSN_MENU_CONFIGURATION'),
				'link' => JRoute::_('index.php?option=com_poweradmin2&view=configuration')
			),
			'about' => array(
				'name' => JText::_('JSN_MENU_ABOUT'),
				'link' => JRoute::_('index.php?option=com_poweradmin2&view=about')
			),
			'help' => array(
				'name' => JText::_('JSN_MENU_HELP'),
				'link' => JRoute::_('index.php?option=com_poweradmin2&view=help')
			)
		) as $slug => $item)
		{
			JHtmlSidebar::addEntry($item['name'], $item['link'], $slug === $screen);
		}
	}

	/**
	 * Add assets
	 *
	 * @return	void
	 */
	public static function addAssets()
	{
		// Make sure JSN Extension Framework 2 is installed.
		if (!class_exists('JsnExtFwAssets'))
		{
			JFactory::getApplication()->redirect('index.php?option=com_poweradmin2&view=installer');
		}

		// Load required libraries.
		JsnExtFwAssets::loadJsnElements();

		// Generate base URL to assets folder.
		$base_url = JUri::root(true) . '/plugins/system/poweradmin2/assets';

		// Load stylesheet of JSN PowerAdmin.
		JsnExtFwAssets::loadStylesheet("{$base_url}/css/style.css");
		JsnExtFwAssets::loadStylesheet("{$base_url}/css/custom.css");
		JsnExtFwAssets::loadScript("{$base_url}/js/poweradmin.js");
	}

	/**
	 * Get configuration for JSN PowerAdmin.
	 *
	 * @return  array
	 */
	public static function getConfig()
	{
		static $config;

		if (!isset($config) && class_exists('JsnExtFwHelper'))
		{
			$config = JsnExtFwHelper::getSettings('com_poweradmin2');
		}

		return ( isset($config) ? $config : array() );
	}

	/**
	 * Return an array of supported search coverages.
	 *
	 * @param   boolean  $enabledOnly  Return only enabled search coverages.
	 *
	 * @return  array
	 */
	public static function getSearchCoverages($enabledOnly = false)
	{
		// Trigger an event to get supported search coverages.
		$supportedCoverages = array();

		JFactory::getApplication()->triggerEvent('onPowerAdminGetSearchCoverages', array(
			&$supportedCoverages
		));

		// Get saved order.
		$config = self::getConfig();
		$coverages = array();

		if (@count($config['search_coverages']))
		{
			foreach ($config['search_coverages'] as $coverage => $enabled)
			{
				if (array_key_exists($coverage, $supportedCoverages) && ( !$enabledOnly || (int) $enabled ))
				{
					$coverages[$coverage] = $supportedCoverages[$coverage];
				}
			}

			if (!$enabledOnly)
			{
				// Append missing coverages.
				foreach ($supportedCoverages as $coverage => $title)
				{
					if (!array_key_exists($coverage, $coverages))
					{
						$coverages[$coverage] = $supportedCoverages[$coverage];
					}
				}
			}
		}
		else
		{
			$coverages = $supportedCoverages;
		}

		return $coverages;
	}

	/**
	 * Get 'Components' backend menu.
	 *
	 * @return  array
	 */
	public static function getComponentsMenu()
	{
		// Get all menu items for the 'Components' backend menu.
		$lang = JFactory::getLanguage();
		$dbo = JFactory::getDbo();

		$dbo->setQuery(
			$dbo->getQuery(true)
				->select('m.*')
				->from('#__menu AS m')
				->select('e.element')
				->join('LEFT', '#__extensions AS e ON e.extension_id = m.component_id')
				//->where("m.menutype = 'main'")
				->where('m.client_id = 1')
				->where('e.enabled = 1')
				->where('m.id > 1')
				->order('m.lft'));

		$items = $dbo->loadObjectList();

		foreach ($items as $item)
		{
			// Translate title.
			$item->text = JText::_($item->title);

			if ($item->text == $item->title)
			{
				// Load language file.
				parse_str(substr($item->link, strpos($item->link, '?') + 1), $params);

				if ($lang->load("{$params['option']}.sys", JPATH_ADMINISTRATOR) ||
					 $lang->load("{$params['option']}.sys", JPATH_ADMINISTRATOR . "/components/{$params['option']}"))
				{
					$item->text = JText::_($item->title);
				}
			}

			// Store item.
			if ((int) $item->parent_id === 1)
			{
				$menu[$item->id] = $item;
			}
			else
			{
				$menu[$item->parent_id]->submenu[] = $item;
			}
		}

		// Sort menu items by title.
		foreach (array_values($menu) as $item)
		{
			if (isset($item->id))
			{
				unset($menu[$item->id]);
			}

			if (isset($item->text))
			{
				$menu[$item->text] = $item;
			}
		}

		ksort($menu);

		$menu = array_values($menu);

		// Get JSN PowerAdmin's config.
		$config = self::getConfig();
		$components = array();

		if ((int) $config['allow_uninstall'])
		{
			// Get ID for all installed components.
			$dbo = JFactory::getDbo();

			$dbo->setquery("SELECT extension_id, element FROM #__extensions WHERE type = 'component' AND protected = 0;");

			$components = $dbo->loadAssocList('element', 'extension_id');
		}

		// Add links to uninstall components.
		foreach ($menu as $i => $menuItem)
		{
			// Simply unset menu item if missing data.
			if (empty($menuItem->link) && empty($menuItem->title) && ( empty($menuItem->class) || $menuItem->class !== 'separator' ))
			{
				unset($menu[$i]);

				continue;
			}

			// Simply unset menu item if component was uninstalled.
			elseif ($menuItem->element === 'com_categories')
			{
				if (preg_match('/^index\.php\?option=com_categories&extension=([^\r\n]+)$/', $menuItem->link, $match))
				{
					if (!( is_dir(JPATH_ROOT . "/components/{$match[1]}") || is_dir(JPATH_ADMINISTRATOR . "/components/{$match[1]}") ))
					{
						unset($menu[$i]);

						continue;
					}
				}
			}

			// Clear title from menu item.
			unset($menu[$i]->title);

			if (@count($menuItem->submenu))
			{
				foreach ($menuItem->submenu as $k => $subItem)
				{
					unset($menu[$i]->submenu[$k]->title);
				}
			}

			// Add a link to uninstall component if possible.
			if (array_key_exists($menuItem->element, $components))
			{
				$menu[$i]->submenu = isset($menu[$i]->submenu) ? $menu[$i]->submenu : array();

				if (count($menuItem->submenu))
				{
					$menu[$i]->submenu[] = array(
						'separator' => true
					);
				}

				$menu[$i]->submenu[] = array(
					'text' => JText::_('JSN_POWERADMIN_UNINSTALL_COMPONENT'),
					'link' => JRoute::_('index.php?option=com_installer&view=manage', false),
					'ajax' => true,
					'post' => array(
						'cid' => array(
							$components[$menuItem->element]
						),
						'task' => 'manage.remove',
						JSession::getFormToken() => 1
					),
					'class' => 'ajax-action uninstall-component',
					'confirm' => JText::sprintf('JSN_POWERADMIN_UNINSTALL_COMPONENT_CONFIRM', $menuItem->text),
					'callback' => 'uninstallComponent',
					'successMessage' => JText::sprintf('JSN_POWERADMIN_UNINSTALL_COMPONENT_SUCCESS', $menuItem->text),
					'closeModalHandler' => 'uninstallComponent'
				);
			}
		}

		/* @formatter:off
		// Get customized 'Components' backend menu.
		$user = JFactory::getUser();
		$customized = $config['backend_menu'];

		// Bind data for the customized 'Components' backend menu.
		if (@count($customized))
		{
			$proccessed = array();

			foreach ($customized as $i => $item)
			{
				if (!is_array($item))
				{
					// Check if the menu item is still available?
					$available = false;

					foreach ($menu as $menuItem)
					{
						if ($menuItem->id == $item)
						{
							$available = true;

							break;
						}
					}

					if ($available)
					{
						$customized[$i] = $menuItem;
						$proccessed[] = $menuItem->id;
					}
					else
					{
						unset($customized[$i]);
					}
				}
				else
				{
					foreach ($item['items'] as $j => $subItem)
					{
						// Check if the sub-menu item is still available?
						$available = false;

						foreach ($menu as $menuItem)
						{
							if ($menuItem->id == $subItem)
							{
								$available = true;

								break;
							}
						}

						if ($available)
						{
							// Check access permission.
							if ($user->authorise('core.manage', $menuItem->element))
							{
								$customized[$i]['items'][$j] = $menuItem;
							}
							else
							{
								unset($customized[$i]['items'][$j]);
							}

							$proccessed[] = $menuItem->id;
						}
						else
						{
							unset($customized[$i]['items'][$j]);
						}
					}

					if (count($customized[$i]['items']))
					{
						$customized[$i]['items'] = array_values($customized[$i]['items']);
					}
					else
					{
						unset($customized[$i]);
					}
				}
			}

			// Add missing item, if has any, to the customized 'Components' backend menu.
			if (count($menu) > count($proccessed))
			{
				foreach ($menu as $menuItem)
				{
					if (!in_array($menuItem->id, $proccessed))
					{
						$customized[] = $menuItem;
					}
				}
			}

			$customized = array_values($customized);

			return $customized;
		}
		*/

		return array_values($menu);
	}

	/**
	 * Get custom assets for a menu item.
	 *
	 * @param   integer  $id       Menu item ID.
	 * @param   string   $type     Either 'css' or 'js'.
	 * @param   boolean  $inherit  Whether to get assets propagated from parent?
	 *
	 * @return  mixed
	 */
	public static function getCustomAssets($id, $type, $inherit = true)
	{
		// Prepare arguments.
		$id = (int) $id;
		$type = in_array($type, array(
			'css',
			'js'
		)) ? $type : 'css';

		// Query for custom assets.
		try
		{
			$dbo = JFactory::getDbo();

			$dbo->setQuery("SELECT * FROM #__jsn_poweradmin2_menu_assets WHERE menuId = {$id} AND type = '{$type}';");

			if ($data = $dbo->loadObject())
			{
				$data->assets = json_decode($data->assets, true);

				return $data;
			}

			// Check if the parent menu items of this menu item has custom assets?
			if ($inherit)
			{
				do
				{
					$dbo->setQuery("SELECT parent_id FROM #__menu WHERE id = {$id};");

					if ($id = (int) $dbo->loadResult())
					{
						$dbo->setQuery("SELECT * FROM #__jsn_poweradmin2_menu_assets WHERE menuId = {$id} AND type = '{$type}';");

						if ($data = $dbo->loadObject())
						{
							if (intval($data->legacy))
							{
								$data->assets = json_decode($data->assets, true);
							}
							else
							{
								$data->assets = array();
							}

							return $data;
						}
					}
				}
				while ($id);
			}
		}
		catch (Exception $e)
		{
			// Do nothing.
		}

		return false;
	}
}
