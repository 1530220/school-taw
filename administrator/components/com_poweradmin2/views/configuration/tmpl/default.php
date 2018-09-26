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

// Generate link to get config for admin bar settings.
$link = JSession::getFormToken();
$link = JRoute::_(sprintf('index.php?option=com_poweradmin2&task=ajax.getAdminBarSettingsConfig&%1$s=1', $link), false);

// @formatter:off
?>
<form action="<?php echo JRoute::_('index.php?option=com_poweradmin2&view=configuration'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="jsn-bootstrap4 jsn-content-main jsn-settings">
			<div class="horizontal-form">
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item active">
						<a class="nav-link" id="admin-bar-tab" data-toggle="tab" href="#admin-bar-pane" role="tab">
							<?php echo JText::_('JSN_POWERADMIN_CONFIGURATION_ADMIN_BAR'); ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="site-manager-tab" data-toggle="tab" href="#site-manager-pane" role="tab">
							<?php echo JText::_('JSN_POWERADMIN_CONFIGURATION_SITE_MANAGER'); ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="languages-tab" data-toggle="tab" href="#languages-pane" role="tab">
							<?php echo JText::_('JSN_EXTFW_CONFIGURATION_LANGUAGE'); ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="user-account-tab" data-toggle="tab" href="#user-account-pane" role="tab">
							<?php echo JText::_('JSN_EXTFW_CONFIGURATION_USER_ACCOUNT'); ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="privacy-settings-tab" data-toggle="tab" href="#privacy-settings-pane" role="tab">
							<?php echo JText::_('JSN_EXTFW_CONFIGURATION_PRIVACY_SETTINGS'); ?>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="global-params-tab" data-toggle="tab" href="#global-params-pane" role="tab">
							<?php echo JText::_('JSN_EXTFW_CONFIGURATION_GLOBAL_PARAMETERS'); ?>
						</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade in active" id="admin-bar-pane" role="tabpanel">
						<div
							id="admin-bar-settings"
							class="jsn-bootstrap4"
							data-render="ScreenAdminBarSettings"
							data-root="<?php echo JUri::root(true); ?>"
							data-config="<?php echo $link; ?>"
							data-text-mapping="<?php
								echo JsnExtFwText::toJson(JsnExtFwText::translate(
									array(
										'JSN_POWERADMIN_CONFIGURATION_ADMIN_BAR_INTRO'
									)
								));
							?>"
						></div>
					</div>
					<div class="tab-pane fade" id="site-manager-pane" role="tabpanel">
						<?php JsnExtFwHtml::renderSettingsForm('com_poweradmin2', '#toolbar-apply .button-apply, #toolbar-apply .btn-success', null, 'config/site-manager.json'); ?>
					</div>
					<div class="tab-pane fade" id="languages-pane" role="tabpanel">
						<?php JsnExtFwHtml::renderLanguageForm('com_poweradmin2', '#save-languages'); ?>

						<hr />

						<button id="save-languages" type="button" class="btn btn-primary d-block mx-auto">
							<?php echo JText::_('JSN_EXTFW_INSTALL_SELECTED_LANGUAGES'); ?>
						</button>
					</div>
					<div class="tab-pane fade" id="user-account-pane" role="tabpanel">
						<?php JsnExtFwHtml::renderAccountPane('com_poweradmin2'); ?>
					</div>
					<div class="tab-pane fade" id="privacy-settings-pane" role="tabpanel">
						<?php JsnExtFwHtml::renderPrivacySettings('com_poweradmin2'); ?>
					</div>
					<div class="tab-pane fade" id="global-params-pane" role="tabpanel">
						<?php
						JsnExtFwHtml::renderSettingsForm('jsnextfw', '#toolbar-apply .button-apply, #toolbar-apply .btn-success', null, 'config/framework.json');

						JsnExtFwHtml::renderSettingsForm('com_poweradmin2', '#toolbar-apply .button-apply, #toolbar-apply .btn-success', null, JSNEXTFW_PATH . '/config/extension.json');
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
<?php
// Render header.
JsnExtFwHtml::renderHeaderComponent();

// Render footer.
JsnExtFwHtml::renderFooterComponent();
