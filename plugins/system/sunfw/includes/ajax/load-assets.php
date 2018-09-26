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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Handle Ajax requests from menu assignment pane.
 *
 * @package  SUN Framework
 * @since    1.0.0
 */
class SunFwAjaxLoadAssets extends SunFwAjax
{

	/**
	 * Handle Ajax request for loading Javascript files.
	 *
	 * @return  void
	 */
	public function getJsAction()
	{
		// Get request variables.
		$scripts = isset($_REQUEST['files']) ? $_REQUEST['files'] : null;

		if (empty($scripts))
		{
			return;
		}

		// Get content of script files to load.
		$scripts = explode(',', $scripts);
		$content = '';

		foreach ($scripts as $script)
		{
			if (is_file($script = JPATH_ROOT . '/' . ltrim($script, '/')))
			{
				$content .= ";\n" . file_get_contents($script);

				if (preg_match('#/templates/[^/]+/js/template\.js#', $script))
				{
					$content = preg_replace('#jQuery\s*\(\s*window\s*\)\s*\.load\s*\(#', "window.addEventListener('load', ", $content);
				}
			}
		}

		// Set necessary headers.
		header('Content-Type: application/javascript; charset=UTF-8');
		header('Content-Length: ' . strlen($content));
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + ( 365 * 24 * 60 * 60 )) . ' GMT');
		header('Cache-Control: public, max-age=' . ( 365 * 24 * 60 * 60 ));

		// Output the content.
		echo $content;

		exit();
	}
}
