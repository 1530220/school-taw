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

// Get Joomla version.
$version = new JVersion;

if ( version_compare($version->getShortVersion(), '3.8', '>=') )
{
	JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

	require_once dirname(__FILE__) . '/menu.j38.php';
}
elseif ( version_compare($version->getShortVersion(), '3.7', '>=') )
{
	require_once dirname(__FILE__) . '/menu.j37.php';
}
else
{
	require_once dirname(__FILE__) . '/menu.j36.php';
}
