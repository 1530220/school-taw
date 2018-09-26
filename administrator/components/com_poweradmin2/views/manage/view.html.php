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
 * Site management view.
 *
 * @package  JSN_PowerAdmin_2
 * @since    1.0.0
 */
class JSNPowerAdmin2ViewManage extends JViewLegacy
{

	/**
	 * Display method
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return	void
	 */
	function display($tpl = null)
	{
		// Add a button for switching mode.
		JToolbarHelper::custom('switch-mode', 'switch-mode', 'switch-mode', JText::_('JSN_POWERADMIN_SWITCH_TO_LIVE_MODE'), false);

		// Add toolbars.
		JSNPowerAdmin2Helper::addToolbars(JText::_('JSN_POWERADMIN_MANAGE_TITLE'), 'manage', 'list pa-manage');

		// Add assets.
		JSNPowerAdmin2Helper::addAssets();

		if (JFactory::getApplication()->input->getCmd('layout') == 'assets')
		{
			JsnExtFwAssets::loadSortable();
		}

		// Display the template
		parent::display($tpl);
	}
}
