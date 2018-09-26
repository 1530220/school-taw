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

// Check if JoomlaShine extension framework is disabled?
$plugin = JTable::getInstance('Extension');
$plugin->load(array(
	'element' => 'jsnextfw',
	'type' => 'plugin',
	'folder' => 'system'
));

if ($plugin->extension_id && !$plugin->enabled)
{
	try
	{
		// Enable our extension framework
		$plugin->enabled = 1;
		$plugin->store();
	}
	catch (Exception $e)
	{
		JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
	}
}

// Check if the system plugin of JSN PowerAdmin is disabled?
$plugin->load(array(
	'element' => 'poweradmin2',
	'type' => 'plugin',
	'folder' => 'system'
));

if ($plugin->extension_id && !$plugin->enabled)
{
	try
	{
		// Enable our extension framework
		$plugin->enabled = 1;
		$plugin->store();
	}
	catch (Exception $e)
	{
		JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
	}
}

// Get admin component directory
$path = dirname(__FILE__);

// Load constant definition file
require_once "{$path}/poweradmin2.defines.php";

// Setup necessary include paths
JTable::addIncludePath("{$path}/tables");

JModelLegacy::addIncludePath("{$path}/models");
JModelLegacy::addTablePath("{$path}/tables");

JHtml::addIncludePath("{$path}/elements/html");
