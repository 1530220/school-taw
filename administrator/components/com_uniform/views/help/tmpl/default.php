<?php
/**
 * @version    $Id: default.php 19014 2012-11-28 04:48:56Z thailv $
 * @package    JSNUniform
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2016 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// Display messages
if (JFactory::getApplication()->input->getInt('ajax') != 1)
{
	echo $this->msgs;
}
?>
<div id="jsn-uniform-help" class="jsn-page-help">
    <div class="jsn-bootstrap">
		<div class="row-fluid">
			<div class="span6">
				<h2 class="jsn-section-header"><?php echo JText::_('JSN_UNIFORM_HELP_DOCUMENTATION'); ?></h2>
				<div class="jsn-section-content">
					<?php echo JText::_('JSN_UNIFORM_HELP_DES_DOCUMENTATION'); ?>
					<ul>
						<li><a class="jsn-link-action" href="https://www.joomlashine.com/documentation/jsn-extensions/jsn-uniform.html" target="_blank"><?php echo JText::_('JSN_UNIFORM_HELP_ONLINE_DOCUMENTATION'); ?>
							</a></li>
						<li><a class="jsn-link-action" href="http://www.joomlashine.com/joomla-extensions/jsn-uniform-videos.html" target="_blank"><?php echo JText::_('JSN_UNIFORM_HELP_WATCH_QUICK_START_VIDEO'); ?>
							</a></li>
					</ul>
				</div>
			</div>
			<div class="span6">
				<h2 class="jsn-section-header"><?php echo JText::_('JSN_UNIFORM_HELP_SUPPORT_FORUM'); ?></h2>
				<div class="jsn-section-content">
					<?php echo JText::_('JSN_UNIFORM_HELP_DES_CHECK_SUPPORT_FORUM'); ?>
					<ul>
						<?php
						if (strtolower(JSN_UNIFORM_EDITION) == 'pro standard' || strtolower(JSN_UNIFORM_EDITION) == 'pro unlimited')
						{
							?>
							<li><a class="jsn-link-action" href="https://www.joomlashine.com/forums.html" target="_blank"><?php echo JText::_('JSN_UNIFORM_HELP_CHECK_SUPPORT_FORUM'); ?>
								</a></li>
							<?php
						}
						else
						{
							?>
							<li><a class="jsn-link-action" href="http://www.joomlashine.com/joomla-extensions/buy-jsn-uniform.html" target="_blank"><?php echo JText::_('JSN_UNIFORM_HELP_BUY_PRO_STANDARD_EDITION'); ?>
								</a></li>
							<?php
						}
						?>
					</ul>
				</div>
			</div>
		</div>
    </div>
</div>
