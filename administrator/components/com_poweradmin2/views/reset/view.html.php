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
 * Site search view.
 *
 * @package  JSN_PowerAdmin_2
 * @since    1.0.0
 */
class JSNPowerAdmin2ViewReset extends JViewLegacy
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
		// Add toolbars.
		JSNPowerAdmin2Helper::addToolbars(JText::_('JSN_POWERADMIN_RESET_TITLE'), 'reset', 'undo-2 pa-reset');

		// Add assets.
		JSNPowerAdmin2Helper::addAssets();

		// Load assets for current screen.
		JsnExtFwAssets::loadScript(JUri::root(true) . '/administrator/components/com_poweradmin2/assets/js/reset-joomla.js');

		JsnExtFwAssets::loadInlineScript(
			'jsn_joomlareset = ' . json_encode(
				array(
					'invalid_email' => JText::_('JSN_JOOMLARESET_INVALID_EMAIL'),
					'invalid_username' => JText::_('JSN_JOOMLARESET_INVALID_USERNAME'),
					'password_not_match' => JText::_('JSN_JOOMLARESET_PASSWORD_NOT_MATCH'),
					'are_you_sure' => JText::_('JSN_JOOMLARESET_ARE_YOU_SURE'),
					'manual_uninstall_component' => JText::_('JSN_JOOMLARESET_MANUAL_UNINSTALL_COMPONENT'),
					'continue_resetting_joomla' => JText::_('JSN_JOOMLARESET_CONTINUE_RESETTING_JOOMLA'),
					'zwXEjFPU' => JText::_('JSN_POWERADMIN_INTRODUCE_PRO_ONLY_FEATURE_MESSAGE'),
					'vAm7nbhW' => JText::_('JSN_POWERADMIN_INTRODUCE_PRO_ONLY_FEATURE_BUTTON'),
					'dGsY8kMV' => JText::_('JSN_POWERADMIN_INTRODUCE_SWITCH_TO_LIVE_MODE_TITLE'),
					'k8cuGn7t' => JText::_('JSN_POWERADMIN_INTRODUCE_SWITCH_TO_LIVE_MODE_MESSAGE')
				)));

		// Get all available Joomla sample data.
		try
		{
			$samples = $this->getModel()->getJoomlaSampleData();
		}
		catch (Exception $e)
		{
			throw $e;
		}

		// Assign variables for rendering.
		$this->assignRef('samples', $samples);

		// Display the template
		parent::display($tpl);
	}
}
