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
 * Ajax controller.
 *
 * @package  JSN_PowerAdmin_2
 * @since    1.0.0
 */
class JSNPowerAdmin2ControllerAjax extends JControllerLegacy
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
	 * JSN PowerAdmin config object.
	 *
	 * @var  JObject
	 */
	protected $cfg;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configuration array.
	 *
	 * @return  void
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Verify session token.
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			JSession::checkToken('get') || JSession::checkToken('post') || die('Invalid token.');
		}

		// Set default content type.
		header('Content-Type: application/json');

		// Get Joomla application object.
		$this->app = JFactory::getApplication();

		// Get Joomla database object.
		$this->dbo = JFactory::getDbo();

		// Get JSN PowerAdmin config object.
		$this->cfg = JSNPowerAdmin2Helper::getConfig();

		// Override JAdminCssMenu class.
		JLoader::register('ModMenuHelper', JPATH_ADMINISTRATOR . '/modules/mod_menu/helper.php');
		JLoader::register('JAdminCssMenu', JPATH_ROOT . '/plugins/system/poweradmin2/overrides/administrator/modules/mod_menu/menu.php');
		JLoader::load('JAdminCssMenu');
	}

	/**
	 * Get config for the site manager screen.
	 *
	 * @return  void
	 */
	public function getSiteManagerConfig()
	{
		// Get the current session token.
		$token = JSession::getFormToken();

		// Generate link to get config for the menus panel of site manager.
		$menu = JRoute::_(sprintf('index.php?option=com_poweradmin2&task=ajax.getMenuPanelConfig&%1$s=1', $token), false);

		$this->sendResponse(
			array(
				'site' => JUri::root(),
				'menu' => $menu,
				'wrapperClass' => 'jsn-bootstrap4',
				'toggleModeBtn' => '#toolbar-switch-mode',
				'textMapping' => array(
					'ok' => JText::_('JSN_POWERADMIN_OK'),
					'cancel' => JText::_('JSN_POWERADMIN_CANCEL'),
					'close' => JText::_('JSN_POWERADMIN_CLOSE'),
					'live-view' => JText::_('JSN_POWERADMIN_LIVE_VIEW'),
					'component' => JText::_('JSN_POWERADMIN_COMPONENT'),
					'module' => JText::_('JSN_POWERADMIN_MODULE'),
					'filter' => JText::_('JSN_POWERADMIN_FILTER'),
					'processing' => JText::_('JSN_POWERADMIN_PROCESSING'),
					'close-menu-panel' => JText::_('JSN_POWERADMIN_CLOSE_MENU_PANEL'),
					'open-menu-panel' => JText::_('JSN_POWERADMIN_OPEN_MENU_PANEL'),
					'close-module-panel' => JText::_('JSN_POWERADMIN_CLOSE_MODULE_PANEL'),
					'open-module-panel' => JText::_('JSN_POWERADMIN_OPEN_MODULE_PANEL'),
					'show-all-items' => JText::_('JSN_POWERADMIN_SHOW_ALL_ITEMS'),
					'hide-unpublished-trashed-items' => JText::_('JSN_POWERADMIN_HIDE_INVISIBLE_ITEMS'),
					'switch-to-raw-mode' => JText::_('JSN_POWERADMIN_SWITCH_TO_RAW_MODE'),
					'switch-to-live-mode' => JText::_('JSN_POWERADMIN_SWITCH_TO_LIVE_MODE'),
					'show-all-elements' => JText::_('JSN_POWERADMIN_SHOW_ALL_ELEMENTS'),
					'hide-invisible-elements' => JText::_('JSN_POWERADMIN_HIDE_INVISIBLE_ELEMENTS'),
					'show-display-options' => JText::_('JSN_POWERADMIN_SHOW_DISPLAY_OPTIONS'),
					'clear-display-options' => JText::_('JSN_POWERADMIN_CLEAR_DISPLAY_OPTIONS'),
					'show-empty-position' => JText::_('JSN_POWERADMIN_SHOW_EMPTY_POSITION'),
					'show-unpublished-module' => JText::_('JSN_POWERADMIN_SHOW_UNPUBLISHED_MODULE'),
					'show-trashed-module' => JText::_('JSN_POWERADMIN_SHOW_TRASHED_MODULE'),
					'show-unassigned-module' => JText::_('JSN_POWERADMIN_SHOW_UNASSIGNED_MODULE'),
					'show-hidden-elements' => JText::_('JSN_POWERADMIN_SHOW_HIDDEN_ELEMENTS'),
					'bjSHsjp6' => JText::_('JSN_EXTFW_PRO_BADGE_TEXT'),
					'M2e7Vnfb' => JText::_('JSN_POWERADMIN_INTRODUCE_SWITCH_TO_LIVE_MODE_TITLE'),
					'aCx3hKWy' => JText::_('JSN_POWERADMIN_INTRODUCE_SWITCH_TO_LIVE_MODE_MESSAGE'),
					'ETCD9cVv' => JText::_('JSN_POWERADMIN_INTRODUCE_REMOVE_BANNERS_TITLE'),
					'bB57JAHh' => JText::_('JSN_POWERADMIN_INTRODUCE_REMOVE_BANNERS_MESSAGE')
				)
			));
	}

	/**
	 * Get config for the menu panel at the site manager screen.
	 *
	 * @param   boolean  $return  Whether to return instead of send response to client.
	 *
	 * @return  mixed
	 */
	public function getMenuPanelConfig($return = false)
	{
		// Get Joomla version
		$jv = new JVersion();

		// Get all defined menu types.
		if (version_compare($jv->getShortVersion(), '3.7.0', '>='))
		{
			$this->dbo->setQuery('SELECT id, menutype, title FROM #__menu_types WHERE client_id = 0 ORDER BY title;');
		}
		else
		{
			$this->dbo->setQuery('SELECT id, menutype, title FROM #__menu_types ORDER BY title;');
		}

		$menus = $this->dbo->loadObjectList();

		// Get list of menu type that has home item.
		if (version_compare($jv->getShortVersion(), '3.7.0', '>='))
		{
			$this->dbo->setQuery('SELECT menutype FROM #__menu WHERE client_id = 0 AND home = 1;');
		}
		else
		{
			$this->dbo->setQuery('SELECT menutype FROM #__menu WHERE home = 1;');
		}

		$hasHome = $this->dbo->loadColumn();

		// Prepare data for return.
		foreach ($menus as &$menu)
		{
			$menu->home = in_array($menu->menutype, $hasHome);

			// Get all menu items for the current menu type.
			$qry = $this->dbo->getQuery(true)
				->select('m.*, l.sef AS language_path')
				->from('#__menu AS m')
				->leftJoin('#__languages AS l ON l.lang_code = m.language')
				->where('m.menutype = ' . $this->dbo->quote($menu->menutype))
				->order('m.lft');

			if (version_compare($jv->getShortVersion(), '3.7.0', '>='))
			{
				$qry->where('m.client_id = 0');
			}

			$this->dbo->setQuery($qry);

			$menu->items = $this->dbo->loadObjectList();
		}

		if ($return)
		{
			return $menus;
		}

		$data = array(
			'menus' => $menus,
			'actions' => array(
				'sort' => array(
					'href' => JRoute::_('index.php?option=com_menus&task=items.saveOrderAjax&tmpl=component', false),
					'ajax' => true,
					'post' => array(
						'client_id' => 0,
						'order' => array(),
						'cid' => array(),
						JSession::getFormToken() => 1
					),
					'callback' => 'sortItem'
				),
				'update' => array(
					'href' => JRoute::_('index.php?option=com_menus&tmpl=component&id={id}', false),
					'ajax' => true,
					'post' => array(
						'task' => 'item.save',
						'jform' => (object) array(),
						JSession::getFormToken() => 1
					),
					'callback' => 'updateItem'
				)
			),
			'contextMenu' => array(
				'dropDown' => array(
					array(
						'separator' => true
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=menu.add', false),
						'text' => JText::_('JSN_POWERADMIN_ADD_NEW_MENU'),
						'target' => 'blank'
					)
				),
				'menuType' => array(
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=menu.edit&id={id}', false),
						'text' => JText::_('JSN_POWERADMIN_EDIT'),
						'target' => 'blank'
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=menus.rebuild&tmpl=component', false),
						'text' => JText::_('JSN_POWERADMIN_REBUILD'),
						'ajax' => true,
						'post' => array(
							JSession::getFormToken() => 1
						)
					),
					array(
						'href' => 'javascript:void(0)',
						'text' => JText::_('JSN_POWERADMIN_MORE'),
						'items' => array(
							array(
								'href' => JRoute::_('index.php?option=com_menus&task=menus.delete&tmpl=component&cid[]={id}', false),
								'text' => JText::_('JSN_POWERADMIN_DELETE'),
								'ajax' => true,
								'post' => array(
									JSession::getFormToken() => 1
								),
								'confirm' => JText::_('JSN_POWERADMIN_REMOVE_MENU_TYPE_CONFIRM'),
								'callback' => 'deleteMenu'
							),
							array(
								'href' => JRoute::_('index.php?option=com_config&view=component&component=com_menus&poweradmin-preview=1',
									false),
								'text' => JText::_('JSN_POWERADMIN_OPTIONS'),
								'target' => 'blank'
							)
						)
					),
					array(
						'separator' => true
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=item.add&menutype={menutype}', false),
						'text' => JText::_('JSN_POWERADMIN_ADD_MENU_ITEM'),
						'target' => 'blank'
					)
				),
				'menuItem' => array(
					array(
						'href' => 'javascript:void(0)',
						'text' => JText::_('JSN_POWERADMIN_SELECT'),
						'callback' => 'selectItem',
						'condition' => array(
							'type = component',
							'access = 1'
						)
					),
					array(
						'separator' => true
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=item.edit&id={id}', false),
						'text' => JText::_('JSN_POWERADMIN_EDIT'),
						'target' => 'blank'
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=items.publish&tmpl=component&cid[]={id}', false),
						'text' => JText::_('JSN_POWERADMIN_PUBLISH'),
						'ajax' => true,
						'post' => array(
							JSession::getFormToken() => 1
						),
						'condition' => array(
							'published < 1'
						),
						'callback' => 'publishItem'
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=items.unpublish&tmpl=component&cid[]={id}', false),
						'text' => JText::_('JSN_POWERADMIN_UNPUBLISH'),
						'ajax' => true,
						'post' => array(
							JSession::getFormToken() => 1
						),
						'condition' => array(
							'published = 1'
						),
						'callback' => 'unpublishItem'
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=items.rebuild&tmpl=component', false),
						'text' => JText::_('JSN_POWERADMIN_REBUILD'),
						'ajax' => true,
						'post' => array(
							JSession::getFormToken() => 1
						)
					),
					'more' => array(
						'href' => 'javascript:void(0)',
						'text' => JText::_('JSN_POWERADMIN_MORE'),
						'items' => array(
							array(
								'href' => JRoute::_('index.php?option=com_menus&task=items.setDefault&tmpl=component&cid[]={id}', false),
								'text' => JText::_('JSN_POWERADMIN_SET_AS_HOME'),
								'ajax' => true,
								'post' => array(
									JSession::getFormToken() => 1
								),
								'condition' => array(
									'home = 0'
								)
							),
							array(
								'href' => JRoute::_('index.php?option=com_menus&task=items.checkin&tmpl=component&cid[]={id}', false),
								'text' => JText::_('JSN_POWERADMIN_CHECK_IN'),
								'ajax' => true,
								'post' => array(
									JSession::getFormToken() => 1
								),
								'condition' => array(
									'checked_out > 0'
								),
								'callback' => 'checkInItem'
							),
							array(
								'href' => JRoute::_('index.php?option=com_menus&task=items.trash&tmpl=component&cid[]={id}', false),
								'text' => JText::_('JSN_POWERADMIN_TRASH'),
								'ajax' => true,
								'post' => array(
									JSession::getFormToken() => 1
								),
								'condition' => array(
									'published > -2'
								),
								'confirm' => JText::_('JSN_POWERADMIN_REMOVE_MENU_ITEM_CONFIRM'),
								'callback' => 'trashItem'
							)
						)
					),
					array(
						'separator' => true
					),
					array(
						'href' => 'javascript:void(0)',
						'text' => JText::_('JSN_POWERADMIN_EXPAND_ALL'),
						'callback' => 'expandAll',
						'condition' => array(
							'hasSubMenu'
						)
					),
					array(
						'href' => 'javascript:void(0)',
						'text' => JText::_('JSN_POWERADMIN_COLLAPSE_ALL'),
						'callback' => 'collapseAll',
						'condition' => array(
							'hasSubMenu'
						)
					),
					array(
						'separator' => true
					),
					array(
						'href' => JRoute::_('index.php?option=com_menus&task=item.add&menutype={menutype}', false),
						'text' => JText::_('JSN_POWERADMIN_ADD_MENU_ITEM'),
						'target' => 'blank'
					)
				)
			),
			'textMapping' => array(
				'menu' => JText::_('JSN_POWERADMIN_MENU'),
				'no-menu-available' => JText::_('JSN_POWERADMIN_NO_MENU_AVAILABLE')
			),
			'multiLanguages' => (boolean) JLanguageMultilang::isEnabled()
		);

		// Get content languages.
		JLoader::register('LanguagesModelLanguages', JPATH_ADMINISTRATOR . '/components/com_languages/models/languages.php');

		$data['contentLanguages'] = ( new LanguagesModelLanguages() )->getItems();

		// If custom assets is enabled, add more context menu items.
		$config = JSNPowerAdmin2Helper::getConfig();

		if ((int) $config['custom_assets_enhance'])
		{
			$data['contextMenu']['menuItem']['more']['items'] = array_merge($data['contextMenu']['menuItem']['more']['items'],
				array(
					array(
						'separator' => true
					),
					array(
						'href' => JRoute::_('index.php?option=com_poweradmin2&view=manage&layout=assets&tmpl=component&id={id}', false),
						'text' => JText::_('JSN_POWERADMIN_LOAD_CUSTOM_ASSETS'),
						'task' => 'saveCustomAssets',
						'callback' => 'skipUpdate',
						'modalTitle' => JText::_('JSN_POWERADMIN_LOAD_CUSTOM_ASSETS_MODAL_TITLE'),
						'modalWidth' => '680px'
					)
				));
		}

		$data['contextMenu']['menuItem'] = array_values($data['contextMenu']['menuItem']);

		$this->sendResponse($data);
	}

	/**
	 * Get config for the custom assets component.
	 *
	 * @return  void
	 */
	public function getCustomAssetsConfig()
	{
		// Get menu item ID.
		$id = $this->app->input->getInt('id');

		// Get all defined custom assets.
		$custom_css = JSNPowerAdmin2Helper::getCustomAssets($id, 'css', false);
		$custom_js = JSNPowerAdmin2Helper::getCustomAssets($id, 'js', false);

		$this->sendResponse(
			array(
				'action' => JRoute::_("index.php?option=com_poweradmin2&task=ajax.saveCustomAssets&id={$id}", false),
				'token' => JSession::getFormToken(),
				'site' => JUri::root(),
				'wrapperClass' => 'jsn-bootstrap4',
				'values' => array(
					'css' => $custom_css ? $custom_css : array(
						'assets' => array(),
						'legacy' => 0
					),
					'js' => $custom_js ? $custom_js : array(
						'assets' => array(),
						'legacy' => 0
					)
				),
				'textMapping' => array(
					'ok' => JText::_('JSN_POWERADMIN_OK'),
					'cancel' => JText::_('JSN_POWERADMIN_CANCEL'),
					'close' => JText::_('JSN_POWERADMIN_CLOSE'),
					'add' => JText::_('JSN_POWERADMIN_ADD'),
					'custom-css-files' => JText::_('JSN_POWERADMIN_CUSTOM_CSS_FILES'),
					'custom-js-files' => JText::_('JSN_POWERADMIN_CUSTOM_JS_FILES'),
					'apply-custom-assets-for-children' => JText::_('JSN_POWERADMIN_CUSTOM_APPLY_FOR_CHILDREN_MENU_ITEMS'),
					'add-custom-css-file' => JText::_('JSN_POWERADMIN_ADD_CUSTOM_CSS_FILE'),
					'add-custom-js-file' => JText::_('JSN_POWERADMIN_ADD_CUSTOM_JS_FILE'),
					'custom-asset-input-hint' => JText::_('JSN_POWERADMIN_CUSTOM_ASSET_INPUT_HINT'),
					'custom-asset-inaccessible' => JText::_('JSN_POWERADMIN_CUSTOM_ASSET_INACCESSIBLE')
				)
			));
	}

	/**
	 * Get config for the page preview component.
	 *
	 * @return  void
	 */
	public function getPagePreviewConfig()
	{
		// Define supported option type for customizing component output.
		$optionTypes = array(
			'content' => 'component'
		);

		// Get data for the specified menu item.
		$item = $this->app->input->getInt('id');

		$this->dbo->setQuery("SELECT * FROM #__menu WHERE id = {$item};");

		$item = $this->dbo->loadObject();

		// Trigger an event to allow 3rd-party to register more option types.
		$this->app->triggerEvent('onPowerAdminGetComponentOutputOptions', array(
			$item,
			&$optionTypes
		));

		// Define context menu items for the component output representation.
		$componentContextMenu = array(
			array(
				'href' => JRoute::_("index.php?option=com_menus&task=item.edit&id={$item->id}", false),
				'text' => JText::_('JSN_POWERADMIN_EDIT_MENU_ITEM'),
				'target' => 'blank'
			)
		);

		// Trigger an event to get action to edit content item associated with the current page.
		$editContentItem = array(
			'href' => '',
			'text' => JText::_('JSN_POWERADMIN_EDIT_CONTENT_ITEM'),
			'target' => '_blank'
		);

		$this->app->triggerEvent('onPowerAdminGetContentItemEditLink', array(
			$item,
			&$editContentItem
		));

		if (!empty($editContentItem['href']))
		{
			$componentContextMenu[] = $editContentItem;
		}

		// Send response back.
		$this->sendResponse(
			array(
				'wrapperClass' => 'jsn-bootstrap4',
				'optionTypes' => $optionTypes,
				'actions' => array(
					'move' => array(
						'ajax' => true,
						'href' => JRoute::_('index.php?option=com_modules&tmpl=component', false),
						'post' => array(
							'task' => 'module.batch',
							'client_id' => 0,
							'batch' => array(
								'move_copy' => 'm'
							),
							JSession::getFormToken() => 1
						),
						'callback' => 'moveModule'
					),
					'sort' => array(
						'ajax' => true,
						'href' => JRoute::_('index.php?option=com_modules&task=modules.saveOrderAjax&tmpl=component', false),
						'post' => array(
							'client_id' => 0,
							'list' => array(
								'fullordering' => 'a.ordering ASC'
							),
							'filter' => (object) array(),
							'order' => array(),
							'cid' => array(),
							JSession::getFormToken() => 1
						),
						'callback' => 'sortModule'
					),
					'save' => array(
						'ajax' => true,
						'href' => JRoute::_('index.php?option=com_poweradmin2&task=ajax.saveMenuItemParam', false),
						'post' => array(
							'id' => $item->id,
							'param' => '',
							'value' => '',
							'scope' => 'this-page',
							JSession::getFormToken() => 1
						),
						'scopes' => 'this-page,all-pages',
						'callback' => 'saveComponentDisplayOption'
					)
				),
				'contextMenu' => array(
					'component' => $componentContextMenu,
					'module' => array(
						array(
							'href' => JRoute::_('index.php?option=com_modules&task=module.edit&id={id}', false),
							'text' => JText::_('JSN_POWERADMIN_EDIT'),
							'target' => 'blank'
						),
						array(
							'href' => '{url}&poweradmin-preview=1&select-position=1',
							'text' => JText::_('JSN_POWERADMIN_CHANGE_POSITION'),
							'task' => 'changeModulePosition',
							'callback' => 'showPositionSelector',
							'modalTitle' => JText::_('JSN_POWERADMIN_CHANGE_POSITION_MODAL_TITLE'),
							'modalWidth' => '90%'
						),
						array(
							'href' => JRoute::_('index.php?option=com_modules&task=modules.publish&tmpl=component&cid[]={id}', false),
							'text' => JText::_('JSN_POWERADMIN_PUBLISH'),
							'ajax' => true,
							'post' => array(
								JSession::getFormToken() => 1
							),
							'condition' => array(
								'published < 1'
							),
							'callback' => 'publishModule'
						),
						array(
							'href' => JRoute::_('index.php?option=com_modules&task=modules.unpublish&tmpl=component&cid[]={id}', false),
							'text' => JText::_('JSN_POWERADMIN_UNPUBLISH'),
							'ajax' => true,
							'post' => array(
								JSession::getFormToken() => 1
							),
							'condition' => array(
								'published = 1'
							),
							'callback' => 'unpublishModule'
						),
						array(
							'href' => 'javascript:void(0)',
							'text' => JText::_('JSN_POWERADMIN_ASSIGN_TO'),
							'condition' => array(
								'assigned = 0'
							),
							'items' => array(
								array(
									'href' => JRoute::_('index.php?option=com_poweradmin2&task=ajax.assignModule&tmpl=component&id={id}',
										false),
									'text' => JText::_('JSN_POWERADMIN_THIS_PAGE'),
									'ajax' => true,
									'post' => array(
										'to' => $item->id,
										JSession::getFormToken() => 1
									),
									'callback' => 'assignModule'
								),
								array(
									'href' => JRoute::_('index.php?option=com_poweradmin2&task=ajax.assignModule&tmpl=component&id={id}',
										false),
									'text' => JText::_('JSN_POWERADMIN_ALL_PAGES'),
									'ajax' => true,
									'post' => array(
										'to' => 0,
										JSession::getFormToken() => 1
									),
									'callback' => 'assignModule'
								),
								array(
									'separator' => true
								),
								array(
									'href' => JRoute::_(
										'index.php?option=com_poweradmin2&view=manage&layout=assignment&tmpl=component&id={id}', false),
									'text' => JText::_('JSN_POWERADMIN_CUSTOM_PAGES'),
									'task' => 'saveModuleAssignment',
									'callback' => 'refresh',
									'modalTitle' => JText::_('JSN_POWERADMIN_MODULE_ASSIGNMENT_MODAL_TITLE'),
									'modalWidth' => '464px'
								)
							)
						),
						array(
							'href' => 'javascript:void(0)',
							'text' => JText::_('JSN_POWERADMIN_UNASSIGN_FROM'),
							'condition' => array(
								'assigned = 1'
							),
							'items' => array(
								array(
									'href' => JRoute::_('index.php?option=com_poweradmin2&task=ajax.unassignModule&tmpl=component&id={id}',
										false),
									'text' => JText::_('JSN_POWERADMIN_THIS_PAGE'),
									'ajax' => true,
									'post' => array(
										'from' => $item->id,
										JSession::getFormToken() => 1
									),
									'callback' => 'unassignModule'
								),
								array(
									'href' => JRoute::_('index.php?option=com_poweradmin2&task=ajax.unassignModule&tmpl=component&id={id}',
										false),
									'text' => JText::_('JSN_POWERADMIN_ALL_PAGES'),
									'ajax' => true,
									'post' => array(
										'from' => 0,
										JSession::getFormToken() => 1
									),
									'callback' => 'unassignModule'
								),
								array(
									'separator' => true
								),
								array(
									'href' => JRoute::_(
										'index.php?option=com_poweradmin2&view=manage&layout=assignment&tmpl=component&id={id}', false),
									'text' => JText::_('JSN_POWERADMIN_CUSTOM_PAGES'),
									'task' => 'saveModuleAssignment',
									'callback' => 'refresh',
									'modalTitle' => JText::_('JSN_POWERADMIN_MODULE_ASSIGNMENT_MODAL_TITLE'),
									'modalWidth' => '464px'
								)
							)
						),
						array(
							'separator' => true
						),
						array(
							'href' => 'javascript:void(0)',
							'text' => JText::_('JSN_POWERADMIN_MORE'),
							'items' => array(
								array(
									'href' => JRoute::_('index.php?option=com_modules&task=modules.duplicate&tmpl=component', false),
									'text' => JText::_('JSN_POWERADMIN_DUPLICATE'),
									'ajax' => true,
									'post' => array(
										'cid' => array(),
										JSession::getFormToken() => 1
									),
									'callback' => 'duplicateModule'
								),
								array(
									'href' => JRoute::_('index.php?option=com_modules&task=modules.trash&tmpl=component&cid[]={id}', false),
									'text' => JText::_('JSN_POWERADMIN_TRASH'),
									'ajax' => true,
									'post' => array(
										JSession::getFormToken() => 1
									),
									'condition' => array(
										'published > -2'
									),
									'callback' => 'trashModule'
								),
								array(
									'href' => JRoute::_(
										'index.php?option=com_config&view=component&component=com_modules&poweradmin-preview=1', false),
									'text' => JText::_('JSN_POWERADMIN_OPTIONS'),
									'target' => 'blank'
								)
							)
						)
					),
					'position' => array(
						array(
							'href' => 'javascript:void(0)',
							'text' => JText::_('JSN_POWERADMIN_VIEW_POSITIONS'),
							'callback' => 'viewPositions',
							'modalWidth' => '90%'
						),
						array(
							'separator' => true
						),
						array(
							'href' => JRoute::_(
								'index.php?option=com_modules&view=select&tmpl=component&poweradmin-preview=1&position={position}', false),
							'text' => JText::_('JSN_POWERADMIN_ADD_MODULE'),
							'task' => 'module.save',
							'callback' => 'addModule'
						)
					)
				),
				'textMapping' => array(
					'ok' => JText::_('JSN_POWERADMIN_OK'),
					'cancel' => JText::_('JSN_POWERADMIN_CANCEL'),
					'close' => JText::_('JSN_POWERADMIN_CLOSE'),
					'assigned' => JText::_('JSN_POWERADMIN_ASSIGNED'),
					'unassigned' => JText::_('JSN_POWERADMIN_UNASSIGNED'),
					'published' => JText::_('JSN_POWERADMIN_PUBLISHED'),
					'unpublished' => JText::_('JSN_POWERADMIN_UNPUBLISHED'),
					'trashed' => JText::_('JSN_POWERADMIN_TRASHED'),
					'this-page' => JText::_('JSN_POWERADMIN_FOR_THIS_PAGE'),
					'all-pages' => JText::_('JSN_POWERADMIN_FOR_ALL_PAGES'),
					'unsupported-component' => JText::_('JSN_POWERADMIN_UNSUPPORTED_COMPONENT')
				)
			));
	}

	/**
	 * Get config for the module assignment component.
	 *
	 * @return  void
	 */
	public function getModuleAssignmentConfig()
	{
		// Get module ID.
		$id = $this->app->input->getInt('id');

		// Get all menu types and items.
		$menus = $this->getMenuPanelConfig(true);

		// Get all menu items which this specified module is (un-)assigned to.
		$this->dbo->setQuery("SELECT COUNT(*) FROM #__modules_menu WHERE moduleid = {$id};");

		if ($this->dbo->loadResult())
		{
			$this->dbo->setQuery("SELECT menuid FROM #__modules_menu WHERE moduleid = {$id};");

			$assigned = $this->dbo->loadColumn();
		}
		else
		{
			$assigned = array();
		}

		// Send response back.
		$this->sendResponse(
			array(
				'menus' => $menus,
				'assigned' => $assigned,
				'action' => JRoute::_("index.php?option=com_poweradmin2&task=ajax.saveModuleAssignment&id={$id}", false),
				'token' => JSession::getFormToken(),
				'textMapping' => array(
					'assign-to' => JText::_('JSN_POWERADMIN_MODULE_ASSIGNMENT_ASSIGN_TO'),
					'no-page' => JText::_('JSN_POWERADMIN_NO_PAGE'),
					'all-pages' => JText::_('JSN_POWERADMIN_ALL_PAGES'),
					'all-except-selected-page' => JText::_('JSN_POWERADMIN_ALL_PAGES_EXCEPT_SELECTED'),
					'selected-page' => JText::_('JSN_POWERADMIN_SELECTED_PAGE'),
					'show-all-items' => JText::_('JSN_POWERADMIN_SHOW_ALL_ITEMS'),
					'hide-unpublished-trashed-items' => JText::_('JSN_POWERADMIN_HIDE_INVISIBLE_ITEMS'),
					'no-menu-available' => JText::_('JSN_POWERADMIN_NO_MENU_AVAILABLE')
				)
			));
	}

	/**
	 * Get config for the site search screen.
	 *
	 * @return  void
	 */
	public function getSiteSearchConfig()
	{
		// Get enabled search coverages.
		$coverages = array();

		foreach (JSNPowerAdmin2Helper::getSearchCoverages(true) as $value => $text)
		{
			// Get search page for the current coverage.
			$page = null;
			$name = null;

			$this->app->triggerEvent('onPowerAdminGetSearchPageForCoverage', array(
				$value,
				&$page,
				&$name
			));

			if (!empty($page) && !empty($name))
			{
				$coverages[$value] = array(
					'text' => $text,
					'page' => $page,
					'name' => $name
				);
			}
		}

		// Send response back.
		$this->sendResponse(
			array(
				'coverages' => $coverages,
				'textMapping' => array(
					'search' => JText::_('JSEARCH_FILTER'),
					'clear' => JText::_('JSEARCH_FILTER_CLEAR'),
					'all' => JText::_('JALL')
				)
			));
	}

	/**
	 * Get config for the admin bar component.
	 *
	 * @return  void
	 */
	public function getAdminBarConfig()
	{
		// Load language file for the 'mod_menu' module.
		$lang = JFactory::getLanguage();

		$lang->load('mod_menu', JPATH_ADMINISTRATOR);

		// Get Joomla backend menu.
		$root = new JAdminCssMenu();
		$params = new JRegistry();

		$params->set('shownew', 1);
		$params->set('showhelp', 1);

		$root = $root->load($params, true);
		$menu = array();

		function prepareBackendMenuItem($item)
		{
			if (( method_exists($item, 'get') ? $item->get('class') : $item->class ) == 'separator')
			{
				$_item = array(
					'separator' => true
				);
			}
			else
			{
				$_item = array(
					'id' => method_exists($item, 'get') ? $item->get('id') : $item->id,
					'link' => str_replace('&amp;', '&', method_exists($item, 'get') ? $item->get('link') : $item->link),
					'title' => JText::_(method_exists($item, 'get') ? $item->get('title') : $item->title),
					'class' => method_exists($item, 'get') ? $item->get('class') : $item->class,
					'target' => method_exists($item, 'get') ? $item->get('target') : $item->target,
					'active' => method_exists($item, 'get') ? $item->get('active') : $item->active
				);

				if ($item->hasChildren())
				{
					foreach ($item->getChildren() as $child)
					{
						$_item['items'][] = prepareBackendMenuItem($child);
					}
				}

				// Add link to edit active templates into Templates menu.
				if ($_item['title'] == 'MOD_MENU_EXTENSIONS_EXTENSIONS' || $_item['title'] == JText::_('MOD_MENU_EXTENSIONS_EXTENSIONS'))
				{
					foreach ($_item['items'] as $k => $v)
					{
						if (@$v['title'] == 'MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER' ||
							 @$v['title'] == JText::_('MOD_MENU_EXTENSIONS_TEMPLATE_MANAGER'))
						{
							if (!@count($v['items']))
							{
								$_item['items'][$k]['items'] = array(
									array(
										'id' => 'com-templates-styles',
										'title' => JText::_('JSN_POWERADMIN_TEMPLATE_STYLES'),
										'modal' => true,
										'modalTitle' => JText::_('JSN_POWERADMIN_TEMPLATE_STYLES'),
										'modalButtons' => 'disabled',
										'modalCloseButton' => true,
										'link' => JRoute::_('index.php?option=com_poweradmin2&view=manage&layout=styles&tmpl=component',
											false)
									)
								);
							}
							else
							{
								// Replace 'Styles' menu item.
								foreach ($v['items'] as $k2 => $v2)
								{
									if (@$v2['title'] == 'MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES' ||
										 @$v2['title'] == JText::_('MOD_MENU_COM_TEMPLATES_SUBMENU_STYLES'))
									{
										$_item['items'][$k]['items'][$k2]['modal'] = true;
										$_item['items'][$k]['items'][$k2]['modalTitle'] = JText::_('JSN_POWERADMIN_TEMPLATE_STYLES');
										$_item['items'][$k]['items'][$k2]['modalButtons'] = 'disabled';
										$_item['items'][$k]['items'][$k2]['modalCloseButton'] = true;
										$_item['items'][$k]['items'][$k2]['link'] = JRoute::_(
											'index.php?option=com_poweradmin2&view=manage&layout=styles&tmpl=component', false);
									}
								}
							}

							// Query for active templates.
							$dbo = JFactory::getDbo()->setQuery('SELECT * FROM #__template_styles WHERE home = 1;');

							if ($tpls = $dbo->loadObjectList())
							{
								$_item['items'][$k]['items'][] = array(
									'separator' => true
								);

								foreach ($tpls as $tpl)
								{
									$_item['items'][$k]['items'][] = array(
										'link' => JRoute::_("index.php?option=com_templates&task=style.edit&id={$tpl->id}", false),
										'title' => $tpl->title
									);
								}
							}
						}
					}
				}
			}

			return $_item;
		}

		foreach ($root->getChildren() as $item)
		{
			$menu[] = prepareBackendMenuItem($item);
		}

		foreach ($menu as & $item)
		{
			// Mark the default menu under the 'Menus' dropdown.
			if (@$item['title'] == 'MOD_MENU_MENUS' || @$item['title'] == JText::_('MOD_MENU_MENUS'))
			{
				// Get the default menu.
				$dbo = JFactory::getDbo()->setQuery(
					'SELECT #__menu.language, #__menu_types.menutype FROM #__menu
					JOIN #__menu_types ON #__menu_types.menutype = #__menu.menutype
					WHERE #__menu.client_id = 0 AND #__menu.home = 1;');

				$defaultMenus = $dbo->loadObjectList();

				// Mark default menu.
				foreach ($defaultMenus as $defaultMenu)
				{
					foreach ($item['items'] as & $_item)
					{
						if (isset($_item['link']))
						{
							if (preg_match("/^index\.php\?option=com_menus&view=items&menutype={$defaultMenu->menutype}$/", $_item['link']))
							{
								if ($defaultMenu->language == '' || $defaultMenu->language == '*')
								{
									if (strpos($_item['title'], 'icon-home') === false)
									{
										$_item['title'] .= ' <i class="fa fa-home"></i>';
									}
								}
								elseif (strpos($_item['title'], '/media/mod_languages/images/') === false)
								{
									$image = JUri::root() . 'media/mod_languages/images/' .
										 strtolower(str_replace('-', '_', $defaultMenu->language)) . '.gif';

									$_item['title'] .= ' <img src="' . $image . '" class="ml-1" />';
								}

								break;
							}
						}
					}
				}
			}

			// Replace the default 'Components' backend menu with the customized one.
			if (@$item['title'] == 'MOD_MENU_COMPONENTS' || @$item['title'] == JText::_('MOD_MENU_COMPONENTS'))
			{
				$item['items'] = JSNPowerAdmin2Helper::getComponentsMenu();
			}
		}

		// Get the current user.
		$user = JFactory::getUser();

		// Get parameters of the current user.
		$userParams = json_decode($user->get('params'));

		// Generate links to switch editor.
		$editors = JPluginHelper::getPlugin('editors');

		foreach ($editors as $k => $editor)
		{
			if ($userParams && $userParams->editor == $editor->name)
			{
				unset($editors[$k]);

				continue;
			}

			// Load language file for this editor.
			$lang->load("plg_editors_{$editor->name}.sys", JPATH_ADMINISTRATOR);

			$editors[$k] = array(
				'link' => JRoute::_("index.php?option=com_poweradmin2&task=ajax.switchEditor&editor={$editor->name}", false),
				'ajax' => true,
				'title' => JText::_(strtoupper("PLG_EDITORS_{$editor->name}")),
				'class' => 'ajax-action switch-editor'
			);
		}

		$editors = array_values($editors);

		// Get message count.
		$this->dbo->setQuery("SELECT COUNT(*) FROM #__messages WHERE user_id_to = {$user->id} AND state = 0;");

		$msgCount = $this->dbo->loadResult();

		// Generate logout link.
		$token = JSession::getFormToken();
		$logout = JRoute::_("index.php?option=com_login&task=logout&{$token}=1", false);

		// Get edit to preview link mapping.
		$editToPreviewLinkMapping = array();

		$this->app->triggerEvent('onPowerAdminGetEditToPreviewLinkMapping', array(
			&$editToPreviewLinkMapping
		));

		// Get user verification data.
		$params = JsnExtFwHelper::getSettings('com_poweradmin2', true);

		// Send response back.
		$this->sendResponse(
			array(
				'wrapperClass' => 'jsn-bootstrap4',
				'actions' => array(
					'removeFavorite' => array(
						'link' => JRoute::_("index.php?option=com_poweradmin2&task=ajax.removeFavorite&{$token}=1&id={id}", false),
						'ajax' => true
					)
				),
				'brand' => array(
					'logo' => JUri::root(true) . "/{$this->cfg['logo_file']}",
					'link' => $this->cfg['logo_link'],
					'target' => $this->cfg['logo_target'],
					'slogan' => JText::_($this->cfg['logo_slogan'])
				),
				'left' => $menu,
				'right' => array(
					array(
						'link' => JUri::root(),
						'icon' => 'eye',
						'target' => '_blank',
						'linkClass' => 'pa-preview-site'
					),
					array(
						'link' => '#',
						'icon' => 'desktop',
						'items' => array(
							array(
								'link' => JRoute::_('index.php?option=com_poweradmin2&view=manage', false),
								'title' => JText::_('JSN_PA2_MENU_MANAGE_TEXT')
							),
							array(
								'link' => JRoute::_('index.php?option=com_poweradmin2&view=reset', false),
								'title' => JText::_('JSN_PA2_MENU_RESET_TEXT')
							)
						),
						'openOnHover' => false
					),
					array(
						'link' => '#',
						'icon' => 'user-o',
						'text' => '<i className="fa user-life"><i className="fa fa-user"></i></i>',
						'items' => array(
							array(
								'link' => null,
								'title' => JText::sprintf('JSN_POWERADMIN_WELCOME_USER', $user->name),
								'class' => 'welcome-user',
								'callback' => 'initSessionExpiration',
								'linkClass' => 'd-flex justify-content-between',
								'sessionExpiration' => (int) $this->app->getCfg('lifetime'),
								'warnBeforeSessionTimeout' => (int) $this->cfg['admin_session_timeout_warning_disabled'] ? 0 : (int) $this->cfg['admin_session_timeout_warning']
							),
							array(
								'separator' => true
							),
							array(
								'link' => JRoute::_("index.php?option=com_admin&task=profile.edit&id={$user->id}&tmpl=component", false),
								'title' => JText::_('JSN_POWERADMIN_USER_PROFILE'),
								'task' => 'profile.save',
								'modal' => true,
								'modalTitle' => JText::_('JSN_POWERADMIN_USER_PROFILE')
							),
							array(
								'link' => '#',
								'title' => JText::_('JSN_POWERADMIN_SWITCH_EDITOR'),
								'items' => $editors,
								'badge' => ( $userParams && $userParams->editor ) ? $userParams->editor : JText::_('JOPTION_USE_DEFAULT')
							),
							array(
								'link' => JRoute::_("index.php?option=com_messages", false),
								'title' => JText::_('JSN_POWERADMIN_PRIVATE_MESSAGES'),
								'badge' => $msgCount
							),
							array(
								'link' => $logout,
								'title' => JText::_('JSN_POWERADMIN_LOGOUT')
							)
						),
						'openOnHover' => false
					),
					array(
						'separator' => true
					),
					array(
						'link' => JRoute::_("index.php?option=com_poweradmin2&task=ajax.getFavorites&{$token}=1", false),
						'icon' => 'star',
						'items' => array(
							array(
								'icon' => 'star',
								'link' => JRoute::_(
									"index.php?option=com_poweradmin2&task=ajax.saveFavorite&{$token}=1&title={title}&link={link}", false),
								'title' => JText::_('JSN_POWERADMIN_ADD_TO_FAVORITES'),
								'class' => 'add-favorite',
								'onClick' => 'addFavorite'
							),
							array(
								'separator' => true
							)
						),
						'onClick' => 'getFavorites',
						'openOnHover' => false
					),
					array(
						'link' => JRoute::_("index.php?option=com_poweradmin2&task=history.load&{$token}=1", false),
						'icon' => 'history',
						'items' => array(),
						'onClick' => 'getHistory',
						'openOnHover' => false
					),
					array(
						'link' => JRoute::_("index.php?option=com_poweradmin2&task=ajax.search&{$token}=1", false),
						'icon' => 'search',
						'class' => 'spotlight-search',
						'items' => array(),
						'callback' => 'initSpotlightSearch',
						'placeholder' => JText::_('JSN_POWERADMIN_SEARCH_PLACEHOLDER'),
						'openOnHover' => false,
						'hideOnClickOutsideOnly' => true
					),
					array(
						'separator' => true
					),
					array(
						'link' => 'http://www.joomlashine.com/',
						'class' => 'jsn-logo',
						'image' => JUri::root(true) . '/plugins/system/jsnextfw/assets/joomlashine/img/logo-jsn.png',
						'target' => '_blank'
					)
				),
				'textMapping' => array(
					'ok' => JText::_('JSN_POWERADMIN_OK'),
					'cancel' => JText::_('JSN_POWERADMIN_CANCEL'),
					'close' => JText::_('JSN_POWERADMIN_CLOSE'),
					'processing' => JText::_('JSN_POWERADMIN_PROCESSING'),
					'are-you-sure' => JText::_('JSN_POWERADMIN_ARE_YOU_SURE'),
					'not-found-any-favorite' => JText::_('JSN_POWERADMIN_NOT_FOUND_ANY_FAVORITE'),
					'add-current-page-to-favorite' => JText::_('JSN_POWERADMIN_ADD_CURRENT_PAGE_TO_FAVORITE'),
					'page-title' => JText::_('JSN_POWERADMIN_PAGE_TITLE'),
					'page-url' => JText::_('JSN_POWERADMIN_PAGE_URL'),
					'not-found-any-activity' => JText::_('JSN_POWERADMIN_NOT_FOUND_ANY_ACTIVITY'),
					'spotlight-search-hint' => JText::_('JSN_POWERADMIN_SPOTLIGHT_SEARCH_HINT'),
					'not-found-any-result' => JText::_('JSN_POWERADMIN_NOT_FOUND_ANY_RESULT'),
					'working-session-is-being-expired' => JText::_('JSN_POWERADMIN_SESSION_TIMEOUT_ALERT'),
					'please-verify-license' => JText::_('JSN_POWERADMIN_VERIFY_LICENSE')
				),
				'editToPreviewLinkMapping' => $editToPreviewLinkMapping,
				'license' => array(
					'user' => isset($params['username']) ? $params['username'] : null,
					'token' => isset($params['token']) ? $params['token'] : null
				)
			));
	}

	/**
	 * Get config for the admin bar settings screen.
	 *
	 * @return  void
	 */
	public function getAdminBarSettingsConfig()
	{
		// Get saved settings.
		$data = JSNPowerAdmin2Helper::getConfig();

		// Get settings form declaration.
		$form = json_decode(file_get_contents(JPATH_ADMINISTRATOR . "/components/com_poweradmin2/config/admin-bar.json"), true);

		// Prepare save handler.
		$save = 'action=update&component=com_poweradmin2&' . JSession::getFormToken() . '=1';
		$save = JRoute::_("index.php?option=com_ajax&format=json&plugin=jsnextfw&context=settings&{$save}", false);

		// Send response back.
		$this->sendResponse(
			array(
				'data' => $data,
				'form' => $form,
				'saveButton' => '#toolbar-apply .button-apply, #toolbar-apply .btn-success',
				'saveHandler' => $save,
				'textMapping' => JsnExtFwText::translate(JsnExtFwHelper::getTranslatableString($form))
			));
	}

	/**
	 * Get config for the backend menu component.
	 *
	 * @return  void
	 */
	public function getComponentsMenuConfig()
	{
		// Send response back.
		$this->sendResponse(
			array(
				'items' => JSNPowerAdmin2Helper::getComponentsMenu(),
				'saveButton' => '.form-actions .btn-primary',
				'wrapperClass' => 'jsn-bootstrap4',
				'actions' => array(
					'permission' => array(
						'href' => JRoute::_('index.php?option=com_poweradmin2&view=manage&layout=permissions&component={component}', false),
						'target' => 'blank'
					)
				),
				'textMapping' => array(
					'ok' => JText::_('JSN_POWERADMIN_OK'),
					'cancel' => JText::_('JSN_POWERADMIN_CANCEL'),
					'close' => JText::_('JSN_POWERADMIN_CLOSE'),
					'customize-components-menu' => JText::_('JSN_POWERADMIN_CUSTOMIZE_COMPONENTS_MENU'),
					'add-new-component-menu-group' => JText::_('JSN_POWERADMIN_ADD_NEW_COMPONENTS_MENU_GROUP'),
					'new-component-menu-group-hint' => JText::_('JSN_POWERADMIN_ADD_NEW_COMPONENTS_MENU_GROUP_HINT'),
					'drop-menu-item-here-to-add-to-group' => JText::_('JSN_POWERADMIN_DROP_MENU_ITEM_TO_ADD_TO_GROUP'),
					'click-to-edit-group-title' => JText::_('JSN_POWERADMIN_CLICK_TO_EDIT_GROUP_TITLE'),
					'cannot-remove-non-empty-group' => JText::_('JSN_POWERADMIN_CANNOT_REMOVE_NON_EMPTY_GROUP'),
					'confirm-save-change' => JText::_('JSN_POWERADMIN_CONFIRM_SAVE_CHANGE')
				)
			));
	}

	/**
	 * Get config for the template styles component.
	 *
	 * @reutrn  void
	 */
	public function getTemplateStylesConfig()
	{
		// Get all installed template styles.
		$qr = $this->dbo->getQuery(true)
			->select('t.*')
			->from('#__template_styles AS t')
			->select('e.extension_id')
			->join('LEFT', '#__extensions AS e ON e.type = "template" AND e.element = t.template')
			->where('e.enabled = 1');

		$this->dbo->setQuery($qr);

		$results = $this->dbo->loadObjectList();
		$styles = array();

		foreach ($results as $result)
		{
			$result->thumb = ( intval($result->client_id) ? 'administrator/' : '' ) . "templates/{$result->template}/template_thumbnail.png";

			if (!is_file(JPATH_ROOT . "/{$result->thumb}"))
			{
				$result->thumb = null;
			}

			$styles[intval($result->client_id) ? 'admin' : 'site'][] = $result;
		}

		$this->sendResponse(
			array(
				'root' => JUri::root(),
				'items' => $styles,
				'wrapperClass' => 'jsn-bootstrap4',
				'contextMenu' => array(
					'templateStyle' => array(
						array(
							'href' => JRoute::_('index.php?option=com_templates&task=style.edit&id={id}', false),
							'text' => JText::_('JSN_POWERADMIN_EDIT'),
							'target' => '_blank'
						),
						array(
							'href' => JRoute::_('index.php?option=com_templates&task=styles.setDefault&tmpl=component', false),
							'text' => JText::_('JSN_POWERADMIN_SET_AS_DEFAULT'),
							'ajax' => true,
							'post' => array(
								'cid' => array(),
								JSession::getFormToken() => 1
							),
							'condition' => array(
								'home = 0'
							),
							'callback' => 'addStyleIdToPost'
						),
						array(
							'href' => JRoute::_('index.php?option=com_templates&task=styles.duplicate&tmpl=component', false),
							'text' => JText::_('JSN_POWERADMIN_DUPLICATE'),
							'ajax' => true,
							'post' => array(
								'cid' => array(),
								JSession::getFormToken() => 1
							),
							'callback' => 'addStyleIdToPost'
						),
						array(
							'href' => JRoute::_('index.php?option=com_templates&task=styles.delete&tmpl=component', false),
							'text' => JText::_('JSN_POWERADMIN_DELETE'),
							'ajax' => true,
							'post' => array(
								'cid' => array(),
								JSession::getFormToken() => 1
							),
							'callback' => 'addStyleIdToPost'
						),
						array(
							'separator' => true
						),
						array(
							'href' => JRoute::_('index.php?option=com_installer&task=manage.remove&tmpl=component&cid[]={extension_id}',
								false),
							'text' => JText::_('JSN_POWERADMIN_UNINSTALL_TEMPLATE'),
							'ajax' => true,
							'post' => array(
								JSession::getFormToken() => 1
							),
							'confirm' => JText::_('JSN_POWERADMIN_UNINSTALL_TEMPLATE_CONFIRM')
						)
					)
				),
				'textMapping' => array(
					'ok' => JText::_('JSN_POWERADMIN_OK'),
					'cancel' => JText::_('JSN_POWERADMIN_CANCEL'),
					'close' => JText::_('JSN_POWERADMIN_CLOSE'),
					'processing' => JText::_('JSN_POWERADMIN_PROCESSING'),
					'installed-template-styles' => JText::_('JSN_POWERADMIN_INSTALLED_TEMPLATE_STYLES'),
					'site' => JText::_('JSN_POWERADMIN_SITE_TEMPLATES'),
					'admin' => JText::_('JSN_POWERADMIN_ADMIN_TEMPLATES'),
					'no-thumb-available' => JText::_('JSN_POWERADMIN_THUMBNAIL_NOT_AVAILABLE'),
					'get-more-templates' => JText::_('JSN_POWERADMIN_GET_MORE_TEMPLATES')
				)
			));
	}

	/**
	 * Get favorites.
	 *
	 * @return  void
	 */
	public function getFavorites()
	{
		// Get the current user.
		$user = JFactory::getUser();

		// Query for all favorites.
		$this->dbo->setQuery("SELECT * FROM #__jsn_poweradmin2_favourite WHERE user_id = {$user->id};");

		$this->sendResponse(array(
			'success' => true,
			'favorites' => $this->dbo->loadObjectList()
		));
	}

	/**
	 * Get available search coverages.
	 *
	 * @return  void
	 */
	public function getSearchCoverages()
	{
		$results = array();

		foreach (JSNPowerAdmin2Helper::getSearchCoverages() as $value => $text)
		{
			$results[] = array(
				'text' => $text,
				'value' => $value
			);
		}

		$this->sendResponse(array(
			'success' => true,
			'data' => $results
		));
	}

	/**
	 * Save custom assets.
	 *
	 * @return  void
	 */
	public function saveCustomAssets()
	{
		// Get and verify variables.
		$id = $this->app->input->getInt('id');
		$data = json_decode($this->app->input->getString('data'), true);

		if (!$id || !$data)
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Save custom assets for the specified menu item.
		foreach ($data as $type => $value)
		{
			try
			{
				//  Get current values.
				$this->dbo->setQuery("SELECT * FROM #__jsn_poweradmin2_menu_assets WHERE menuId = {$id} AND type = '{$type}';");

				if ($row = $this->dbo->loadObject())
				{
					$this->dbo->setQuery(
						$this->dbo->getQuery(true)
							->update('#__jsn_poweradmin2_menu_assets')
							->set('assets = ' . $this->dbo->quote(json_encode($value['assets'])))
							->set('legacy = ' . (int) $value['legacy'])
							->where("id = {$row->id}"))
						->execute();
				}
				else
				{
					$this->dbo->setQuery(
						$this->dbo->getQuery(true)
							->insert('#__jsn_poweradmin2_menu_assets')
							->columns('menuId, assets, type, legacy')
							->values(
							"{$id}, " . $this->dbo->quote(json_encode($value['assets'])) . ", '{$type}', " . (int) $value['legacy']))
						->execute();
				}
			}
			catch (Exception $e)
			{
				$this->sendResponse(array(
					'success' => false,
					'message' => $e->getMessage()
				));
			}
		}

		$this->sendResponse(array(
			'success' => true
		));
	}

	/**
	 * Save a display option for the specified menu item.
	 *
	 * @return  void
	 */
	public function saveMenuItemParam()
	{
		// Get and verify variables.
		$item = $this->app->input->getInt('id');
		$param = $this->app->input->getCmd('param');
		$value = $this->app->input->getInt('value');
		$scope = $this->app->input->getCmd('scope');

		if (!$item || empty($param))
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Get data for the specified menu item.
		$this->dbo->setQuery("SELECT * FROM #__menu WHERE id = {$item};");

		$item = $this->dbo->loadObject();

		if ($item->type != 'component')
		{
			$scope = 'this-page';
		}

		// If context is 'all-pages', save display option to component parameters.
		if ($scope == 'all-pages')
		{
			// Parse menu item link.
			parse_str(substr($item->link, strpos($item->link, '?') + 1), $query);

			// Get the current component parameters.
			$qry = $this->dbo->getQuery(true)
				->select('*')
				->from('#__extensions')
				->where('type = "component"')
				->where('element = ' . $this->dbo->quote($query['option']));

			$this->dbo->setQuery($qry);

			if ($component = $this->dbo->loadObject())
			{
				// Update component parameters.
				$params = empty($component->params) ? array() : json_decode($component->params, true);
				$params[$param] = $value;
				$params = json_encode($params);

				// Then, store to database.
				$qry = $this->dbo->getQuery(true)
					->update('#__extensions')
					->set('params = ' . $this->dbo->quote($params))
					->where("extension_id = {$component->extension_id}");

				if (!$this->dbo->setQuery($qry)->execute())
				{
					$this->sendResponse(array(
						'success' => false,
						'message' => $this->dbo->getErrorMsg()
					));
				}
			}
			else
			{
				$scope = 'this-page';
			}
		}

		// Update menu item parameters.
		$params = empty($item->params) ? array() : json_decode($item->params, true);
		$params[$param] = $scope == 'this-page' ? $value : '';
		$params = json_encode($params);

		// Then, store to database.
		$qry = $this->dbo->getQuery(true)
			->update('#__menu')
			->set('params = ' . $this->dbo->quote($params))
			->where("id = {$item->id}");

		if (!$this->dbo->setQuery($qry)->execute())
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => $this->dbo->getErrorMsg()
			));
		}

		$this->sendResponse(array(
			'success' => true
		));
	}

	/**
	 * Save module assignment.
	 *
	 * @return  void
	 */
	public function saveModuleAssignment()
	{
		// Get and verify variables.
		$id = $this->app->input->getInt('id');
		$assign_to = $this->app->input->getString('assign_to');
		$menu_items = (array) $this->app->input->get('menu_items', array(), 'raw');

		if (!$id || !in_array($assign_to, array(
			'no-page',
			'all-pages',
			'all-except-selected-page',
			'selected-page'
		)))
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Unassign module from all pages.
		$this->dbo->setQuery("DELETE FROM #__modules_menu WHERE moduleid = {$id};");

		if (!$this->dbo->execute())
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => $this->dbo->getErrorMsg()
			));
		}

		// Save module assignment.
		switch ($assign_to)
		{
			case 'all-pages':
				// Assign module to all pages.
				$this->dbo->setQuery("INSERT INTO #__modules_menu (moduleid, menuid) VALUES ({$id}, 0);");

				if (!$this->dbo->execute())
				{
					$this->sendResponse(array(
						'success' => false,
						'message' => $this->dbo->getErrorMsg()
					));
				}
			break;

			case 'all-except-selected-page':
				// Assign module to all except selected page(s).
				foreach ($menu_items as $item)
				{
					$item = (int) $item;

					if ($item > 0)
					{
						$item = 0 - $item;
					}

					$this->dbo->setQuery("INSERT INTO #__modules_menu (moduleid, menuid) VALUES ({$id}, {$item});");

					if (!$this->dbo->execute())
					{
						$this->sendResponse(array(
							'success' => false,
							'message' => $this->dbo->getErrorMsg()
						));
					}
				}
			break;

			case 'selected-page':
				// Assign module to all except selected page(s).
				foreach ($menu_items as $item)
				{
					$item = (int) $item;

					if ($item < 0)
					{
						$item = 0 - $item;
					}

					$this->dbo->setQuery("INSERT INTO #__modules_menu (moduleid, menuid) VALUES ({$id}, {$item});");

					if (!$this->dbo->execute())
					{
						$this->sendResponse(array(
							'success' => false,
							'message' => $this->dbo->getErrorMsg()
						));
					}
				}
			break;
		}

		$this->sendResponse(array(
			'success' => true
		));
	}

	/**
	 * Save a favorite.
	 *
	 * @return  void
	 */
	public function saveFavorite()
	{
		// Get variables.
		$link = $this->app->input->getString('link');
		$title = $this->app->input->getString('title');

		if (empty($link) || empty($title))
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Get the current user.
		$user = JFactory::getUser();

		// Create new favorite.
		$qry = $this->dbo->getQuery(true)
			->insert('#__jsn_poweradmin2_favourite')
			->columns('user_id, title, url')
			->values(implode(', ', array(
			$user->id,
			$this->dbo->quote($title),
			$this->dbo->quote($link)
		)));

		$this->dbo->setQuery($qry);

		if (!$this->dbo->execute())
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => $this->dbo->getErrorMsg()
			));
		}

		$this->sendResponse(array(
			'success' => true,
			'id' => $this->dbo->insertid()
		));
	}

	/**
	 * Assign a module to a menu item.
	 *
	 * @return  void
	 */
	public function assignModule()
	{
		// Get and verify variables.
		$module = $this->app->input->getInt('id');
		$itemID = $this->app->input->getInt('to');

		if (!$module)
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Delete all current assignment if module is being assigned to all pages.
		if ($itemID == 0)
		{
			$this->dbo->setQuery("DELETE FROM #__modules_menu WHERE moduleid = {$module};");

			if (!$this->dbo->execute())
			{
				$this->sendResponse(array(
					'success' => false,
					'message' => $this->dbo->getErrorMsg()
				));
			}
		}

		// Get current assignment record.
		$this->dbo->setQuery("SELECT moduleid FROM #__modules_menu WHERE moduleid = {$module} AND menuid = -{$itemID};");

		if ($this->dbo->loadResult())
		{
			$this->dbo->setQuery("DELETE FROM #__modules_menu WHERE moduleid = {$module} AND menuid = -{$itemID};");
		}

		// Assign module.
		$this->dbo->setQuery("INSERT IGNORE INTO #__modules_menu (moduleid, menuid) VALUES ({$module}, {$itemID});");

		if (!$this->dbo->execute())
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => $this->dbo->getErrorMsg()
			));
		}

		$this->sendResponse(array(
			'success' => true
		));
	}

	/**
	 * Unassign a module from a menu item.
	 *
	 * @return  void
	 */
	public function unassignModule()
	{
		// Get and verify variables.
		$module = $this->app->input->getInt('id');
		$itemID = $this->app->input->getInt('from');

		if (!$module)
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Delete all current assignment if module is being unassigned from all pages.
		if ($itemID == 0)
		{
			$this->dbo->setQuery("DELETE FROM #__modules_menu WHERE moduleid = {$module};");
		}

		// Otherwise, unassign module from the specified page.
		else
		{
			// Get current assignment record.
			$this->dbo->setQuery("SELECT moduleid FROM #__modules_menu WHERE moduleid = {$module} AND menuid = {$itemID};");

			if ($this->dbo->loadResult())
			{
				$this->dbo->setQuery("DELETE FROM #__modules_menu WHERE moduleid = {$module} AND menuid = {$itemID};");
			}
			else
			{
				$this->dbo->setQuery("INSERT IGNORE INTO #__modules_menu (moduleid, menuid) VALUES ({$module}, -{$itemID});");
			}
		}

		if (!$this->dbo->execute())
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => $this->dbo->getErrorMsg()
			));
		}

		$this->sendResponse(array(
			'success' => true
		));
	}

	/**
	 * Remove a favorite.
	 *
	 * @return  void
	 */
	public function removeFavorite()
	{
		// Get favorite ID.
		$id = $this->app->input->getInt('id');

		if (empty($id))
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Get the current user.
		$user = JFactory::getUser();

		// Remove the specified favorite.
		$this->dbo->setQuery("DELETE FROM #__jsn_poweradmin2_favourite WHERE id = {$id} AND user_id = {$user->id};");

		if (!$this->dbo->execute())
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => $this->dbo->getErrorMsg()
			));
		}

		$this->sendResponse(array(
			'success' => true
		));
	}

	/**
	 * Switch default editor for the current user.
	 *
	 * @return  void
	 */
	public function switchEditor()
	{
		// Get editor to switch to.
		$newEditor = $this->app->input->getCmd('editor');

		if (empty($newEditor))
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Get list of available editors.
		$editors = JPluginHelper::getPlugin('editors');

		// Set the requested editor as active one for the current user.
		foreach ($editors as $editor)
		{
			if ($editor->name == $newEditor)
			{
				// Get current user.
				$user = JFactory::getUser();

				// Get current user parameters.
				$params = json_decode($user->get('params'));

				if (!$params)
				{
					$params = new stdClass();
				}

				// Set new editor to user parameters.
				$params->editor = $editor->name;

				$user->setParam('editor', $editor->name);

				// Save new user parameters.
				$table = $user->getTable();

				$table->load($user->id);

				$table->params = json_encode($params);

				$table->store();

				$this->sendResponse(array(
					'success' => true
				));
			}
		}

		$this->sendResponse(array(
			'success' => false,
			'message' => 'Invalid parameters.'
		));
	}

	/**
	 * Search enabled coverages.
	 *
	 * @return  void
	 */
	public function search()
	{
		// Get keyword to search for.
		$keyword = $this->app->input->getString('keyword');

		if (empty($keyword))
		{
			$this->sendResponse(array(
				'success' => false,
				'message' => 'Invalid parameters.'
			));
		}

		// Loop thru enabled search coverages to search for results.
		$coverages = JSNPowerAdmin2Helper::getSearchCoverages(true);
		$results = array();

		foreach ($coverages as $coverage => $label)
		{
			$coverageResults = array();

			// Trigger an event to get search results for the current coverage.
			$this->app->triggerEvent('onPowerAdminGetSearchResultsForCoverage',
				array(
					$coverage,
					$keyword,
					&$coverageResults
				));

			if (count($coverageResults))
			{
				// Prepare search results for display.
				foreach ($coverageResults as $k => &$result)
				{
					if (stripos($result->title, $keyword) === false)
					{
						$result->description = strip_tags(isset($result->description) ? $result->description : '');

						if (( $pos = stripos($result->description, $keyword) ) === false)
						{
							unset($coverageResults[$k]);

							continue;
						}

						if (( $pos = $pos - 20 ) > 0)
						{
							$result->description = '...' . substr($result->description, $pos, strlen($keyword) + 40) . '...';
						}
						else
						{
							$result->description = substr($result->description, 0, strlen($keyword) + 40) . '...';
						}
					}
					else
					{
						unset($result->description);
					}
				}

				if ($count = count($coverageResults))
				{
					if ((int) $this->cfg['search_result_num'] && $count > (int) $this->cfg['search_result_num'])
					{
						array_splice($coverageResults, (int) $this->cfg['search_result_num']);
					}

					// Get search page for the current coverage.
					$page = null;
					$name = null;

					$this->app->triggerEvent('onPowerAdminGetSearchPageForCoverage',
						array(
							$coverage,
							&$page,
							&$name
						));

					if (!empty($page) && !empty($name))
					{
						// Store search results.
						$more = JRoute::_("index.php?option=com_poweradmin2&view=search&keyword={$keyword}&coverage={$coverage}", false);
						$results[] = array(
							'title' => $label,
							'class' => 'search-coverage'
						);
						$results = array_merge($results, $coverageResults);

						if ((int) $this->cfg['search_result_num'] && $count == (int) $this->cfg['search_result_num'])
						{
							$results[] = array(
								'title' => JText::_('JSN_POWERADMIN_VIEW_MORE_RESULTS'),
								'class' => 'text-right',
								'link' => $more
							);
						}
					}
				}
			}
		}

		$this->sendResponse(array(
			'success' => true,
			'data' => $results
		));
	}

	/**
	 * Send response back.
	 *
	 * @param   mixed  $data  Data to send to client.
	 *
	 * @return  void
	 */
	protected function sendResponse($data)
	{
		$output = json_encode($data);

		// Set Content-Length header.
		header('Content-Length: ' . strlen($output));

		// Print output.
		echo $output;

		// Then, exit immediately to prevent Joomla from processing further.
		exit();
	}
}
