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

// Import necessary Joomla library
jimport('joomla.filesystem.folder');


/**
 * Image manipulation class.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class JSNUtilsExtension
{

	/**
	 * Get extension parameters stored in the 'extensions' table.
	 *
	 * @param   string  $type     Either 'component', 'module', 'plugin' or 'template'.
	 * @param   string  $element  Extension's element name.
	 * @param   string  $group    Plugin group, required for 'plugin'.
	 *
	 * @return  array
	 */
	public static function getExtensionParams( $type, $element, $group = '' )
	{
		$dbo = JFactory::getDbo();
		$q   = $dbo->getQuery( true );

		$q
		->select( 'params' )
		->from( '#__extensions' )
		->where( 'type = ' . $q->quote( $type ) )
		->where( 'element = ' . $q->quote( $element ) );

		if ( 'plugin' == $type )
		{
			$q->where( 'folder = ' . $q->quote( $group ) );
		}

		$dbo->setQuery( $q );

		if ( ! ( $params = json_decode( $dbo->loadResult(), true ) ) )
		{
			$params = array();
		}

		return $params;
	}

	/**
	 * Update extension parameters stored in the 'extensions' table.
	 *
	 * @param   array   $params   Array of parameters.
	 * @param   string  $type     Either 'component', 'module', 'plugin' or 'template'.
	 * @param   string  $element  Extension's element name.
	 * @param   string  $group    Plugin group, required for 'plugin'.
	 *
	 * @return  array
	 */
	public static function updateExtensionParams( $params, $type, $element, $group = '' )
	{
		// Get current extension params.
		$curParams = self::getExtensionParams( $type, $element, $group );

		// Then merge with new params.
		$params = array_merge( $curParams, $params );

		// Store to database.
		$dbo = JFactory::getDbo();
		$q   = $dbo->getQuery( true );

		$q
		->update( '#__extensions' )
		->set( 'params = ' . $q->quote( json_encode( $params ) ) )
		->where( 'type = ' . $q->quote( $type ) )
		->where( 'element = ' . $q->quote( $element ) );

		if ( 'plugin' == $type )
		{
			$q->where( 'folder = ' . $q->quote( $group ) );
		}

		$dbo->setQuery( $q );

		$dbo->execute();

		return $params;
	}
}
