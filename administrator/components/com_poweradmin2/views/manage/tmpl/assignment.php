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

// Load assets.
JHtml::_('bootstrap.tooltip', '.hasTooltip', array(
	'placement' => 'bottom'
));

// Generate link to get config for module assignment.
$link = 'id=' . JFactory::getApplication()->input->getInt('id') . '&' . JSession::getFormToken() . '=1';
$link = JRoute::_(sprintf('index.php?option=com_poweradmin2&task=ajax.getModuleAssignmentConfig&%1$s', $link), false);

// @formatter:off
?>
<div id="module-assignment" class="jsn-bootstrap4" data-render="ComponentModuleAssignment" data-config="<?php echo $link; ?>"></div>
