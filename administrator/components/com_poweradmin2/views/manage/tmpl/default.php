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
$link = JRoute::_(sprintf('index.php?option=com_poweradmin2&task=ajax.getSiteManagerConfig&%1$s=1', $link), false);

// @formatter:off
?>
<form action="<?php echo JRoute::_('index.php?option=com_poweradmin2&view=search'); ?>" method="post" name="adminForm" id="adminForm" onsubmit="return false;">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="site-manager" class="jsn-bootstrap4" data-render="ScreenSiteManager" data-config="<?php echo $link; ?>"></div>
	</div>
	<script type="text/javascript">
		(function paCloseSidebar() {
			if (window.toggleSidebar) {
				if (document.getElementById('j-sidebar-container').classList.contains('j-sidebar-visible')) {
					toggleSidebar();
				}
			} else {
				setTimeout(paCloseSidebar, 100);
			}
		})();
	</script>
</form>
<?php
// Render header.
JsnExtFwHtml::renderHeaderComponent();

// Render footer.
JsnExtFwHtml::renderFooterComponent();
