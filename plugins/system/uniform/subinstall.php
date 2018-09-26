<?php
/**
 * @version    $Id: subinstall.php 19029 2012-11-28 07:42:29Z thailv $
 * @package    JSNUniform
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2016 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined( '_JEXEC' ) OR die( 'Restricted access' );

/**
 * Subinstall script for finalizing JSN PageBuilder 3 system plugin.
 *
 * @package  JSN_PageBuilder3
 */
class PlgSystemUniformInstallerScript {
	/**
	 * Enable JSN Uniform system plugin.
	 *
	 * @param   string $route Route type: install, update or uninstall.
	 * @param   object $_this The installer object.
	 *
	 * @return  boolean
	 */
	public function postflight( $route, $_this ) {
		// Get a database connector object
		$db = JFactory::getDbo();

		try
		{
			// Enable plugin by default
			$q = $db->getQuery( true );

			$q
				->update( '#__extensions' )
				->set('ordering = 9999')
				->where( "element = 'uniform'" )
				->where( "type = 'plugin'", 'AND' )
				->where( "folder = 'system'", 'AND' );

			$db->setQuery( $q )->execute();
		}
		catch ( Exception $e )
		{
			throw $e;
		}
	}
}
