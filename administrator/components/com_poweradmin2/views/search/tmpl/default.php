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

// Generate link to get config for site manager.
$link = JSession::getFormToken();
$link = JRoute::_(sprintf('index.php?option=com_poweradmin2&task=ajax.getSiteSearchConfig&%1$s=1', $link), false);

// Get Joomla input object.
$input = JFactory::getApplication()->input;

// @formatter:off
?>
<form action="<?php echo JRoute::_('index.php?option=com_poweradmin2&view=search'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<div
			id="site-search"
			class="jsn-bootstrap4"
			data-render="ScreenSiteSearch"
			data-config="<?php echo $link; ?>"
			data-keyword="<?php echo $input->getString('keyword'); ?>"
			data-coverage="<?php echo $input->getCmd('coverage'); ?>"
		></div>
	</div>
</form>
<?php
// Render header.
JsnExtFwHtml::renderHeaderComponent();

// Render footer.
JsnExtFwHtml::renderFooterComponent();
