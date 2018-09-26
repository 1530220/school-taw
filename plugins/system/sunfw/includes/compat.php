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

// Import necessary library.
jimport('joomla.filesystem.file');

/**
 * General helper class.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
class SunFwCompat
{

	/**
	 * Method to check Joomla version.
	 *
	 * @param   string  $version  Version string to check against.
	 *
	 * @return  boolean
	 */
	public static function isJoomla($version)
	{
		// Make sure the given version is a string.
		if (!is_string($version))
		{
			$version = (string) $version;
		}

		// Get the current Joomla version.
		$v = ( new \JVersion() )->getShortVersion();

		return ( $version === substr(preg_replace('/[^\d]+/', '', $v), 0, strlen($version)) );
	}

	/**
	 * Method to check if the requested page belongs to a certain client.
	 *
	 * @param   string  $client  Either 'admin' or 'site'.
	 *
	 * @return  boolean
	 */
	public static function isClient($client)
	{
		// Get Joomla application object.
		$app = JFactory::getApplication();

		switch ($client)
		{
			case 'site':
				return ( method_exists($app, 'isClient') ? $app->isClient('site') : $app->isSite() );
			break;

			case 'admin':
			case 'administrator':
				return ( method_exists($app, 'isClient') ? $app->isClient('administrator') : $app->isAdmin() );
			break;
		}

		return false;
	}

	/**
	 * Method to load jQuery script library.
	 *
	 * @return  void
	 */
	public static function loadJquery()
	{
		if (self::isJoomla(4))
		{
			if (self::isClient('admin'))
			{
				\JFactory::getDocument()->addScript(JUri::root(true) . '/media/vendor/jquery/js/jquery.min.js');
			}
			else
			{
				Joomla\CMS\HTML\HTMLHelper::_('jquery.framework');
			}
		}
		else
		{
			if (self::isClient('admin'))
			{
				JFactory::getDocument()->addScript(JUri::root(true) . '/media/jui/js/jquery.js');
			}
			else
			{
				JHtml::_('jquery.framework');
			}
		}
	}
}
