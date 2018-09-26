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

// No direct access
defined('_JEXEC') or die;

class JSNPowerAdmin2TableHistory extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('#__jsn_poweradmin2_history', 'id', $db);
	}
}
