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

// Load required assets.
JHtml::_('bootstrap.tooltip', '.hasTooltip', array(
	'placement' => 'bottom'
));

// Generate link to get config for custom assets.
$link = 'id=' . JFactory::getApplication()->input->getInt('id') . '&' . JSession::getFormToken() . '=1';
$link = JRoute::_(sprintf('index.php?option=com_poweradmin2&task=ajax.getCustomAssetsConfig&%1$s', $link), false);

// @formatter:off
?>
<div id="custom-assets" class="jsn-bootstrap4" data-render="ComponentCustomAssets" data-config="<?php echo $link; ?>"></div>
