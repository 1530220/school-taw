<?php
/**
 * @version    $Id$
 * @package    SUN Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

/**
 * Class that register overrides for some built-in classes of Joomla.
 */
class SunFwOverwrite
{

	/**
	 * initialize
	 */
	public static function initialize()
	{
		if (SunFwCompat::isClient('site') && SunFwRecognization::detect())
		{
			if (SunFwCompat::isJoomla(4))
			{
				// Override the built-in \Joomla\CMS\MVC\View\HtmlView class of Joomla.
				if (!class_exists('\\Joomla\\CMS\\MVC\\View\\HtmlView', false))
				{
					JLoader::register('\\Joomla\\CMS\\MVC\\View\\HtmlView',
						SUNFW_PATH_INCLUDES . '/overwrite/j4x/libraries/src/MVC/View/HtmlView.php');
					JLoader::load('\\Joomla\\CMS\\MVC\\View\\HtmlView');
				}

				// Override the built-in \Joomla\CMS\Helper\ModuleHelper class of Joomla.
				if (!class_exists('\\Joomla\\CMS\\Helper\\ModuleHelper', false))
				{
					JLoader::register('\\Joomla\\CMS\\Helper\\ModuleHelper',
						SUNFW_PATH_INCLUDES . '/overwrite/j4x/libraries/src/Helper/ModuleHelper.php');
					JLoader::load('\\Joomla\\CMS\\Helper\\ModuleHelper');
				}

				// Override the built-in \Joomla\CMS\Layout\FileLayout class of Joomla.
				if (!class_exists('\\Joomla\\CMS\\Layout\\FileLayout', false))
				{
					JLoader::register('\\Joomla\\CMS\\Layout\\FileLayout',
						SUNFW_PATH_INCLUDES . '/overwrite/j4x/libraries/src/Layout/FileLayout.php');
					JLoader::load('\\Joomla\\CMS\\Layout\\FileLayout');
				}
			}
			else
			{
				// Override the built-in JViewLegacy class of Joomla.
				if (!class_exists('JViewLegacy', false))
				{
					JLoader::register('JViewLegacy', SUNFW_PATH_INCLUDES . '/overwrite/j3x/libraries/legacy/view/legacy.php');
					JLoader::load('JViewLegacy');
				}

				// Override the built-in JModuleHelper class of Joomla.
				if (!class_exists('JModuleHelper', false))
				{
					JLoader::register('JModuleHelper', SUNFW_PATH_INCLUDES . '/overwrite/j3x/libraries/cms/module/helper.php');
					JLoader::load('JModuleHelper');
				}

				// Override the built-in JLayoutFile class of Joomla.
				if (!class_exists('JLayoutFile', false))
				{
					JLoader::register('JLayoutFile', SUNFW_PATH_INCLUDES . '/overwrite/j3x/libraries/cms/layout/file.php');
					JLoader::load('JLayoutFile');
				}
			}

			// If SH404Sef is not installed, load pagination template override.
			if (!SunFwUtils::checkSH404SEF())
			{
				// Override the built-in JPagination class of Joomla.
				if (!class_exists('JPagination', false))
				{
					JLoader::register('JPagination', SUNFW_PATH_INCLUDES . '/overwrite/j3x/libraries/cms/pagination/pagination.php');
					JLoader::load('JPagination');
				}
			}

			// If VirtueMart is requested, override the built-in VmView class of VirtueMart.
			if (JFactory::getApplication()->input->getCmd('option') == 'com_virtuemart')
			{
				if (!class_exists('VmView', false))
				{
					JLoader::register('VmView', SUNFW_PATH_INCLUDES . '/overwrite/j3x/components/com_virtuemart/helpers/vmview.php');
					JLoader::load('VmView');
				}
			}
		}
	}
}
