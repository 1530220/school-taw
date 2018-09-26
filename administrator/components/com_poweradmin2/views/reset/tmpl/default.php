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

// @formatter:off
?>
<form action="<?php echo JRoute::_('index.php?option=com_poweradmin2&view=search'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="reset-joomla" class="jsn-bootstrap4">
			<h2 class="mb-3">
				<?php echo JText::_( 'JSN_JOOMLARESET_RESET_JOOMLA_IN_JUST_FEW_MINUTE' ); ?>
			</h2>
			<div class="card mb-3">
				<div class="card-body">
					<h3 class="card-title">
						<?php echo JText::_( 'JSN_JOOMLARESET_SELECT_JOOMLA_INITIAL_SAMPLE_DATA' ); ?>
					</h3>
					<div class="card-text">
						<?php $i = 1; foreach ( $this->samples as $sample => $text ) : ?>
						<div class="radio">
							<label>
								<input type="radio" name="sample_data" class="sample_data" id="sample_data_<?php echo $i; ?>" value="<?php
									echo $sample;
								?>" <?php
									if ( $i == 1 ) echo 'checked="checked"';
								?>>
								<?php echo $text; ?>
							</label>
						</div>
						<?php $i++; endforeach; ?>
					</div>
				</div>
			</div>
			<div class="card mb-3">
				<div class="card-body">
					<h3 class="card-title">
						<?php echo JText::_( 'JSN_JOOMLARESET_KEEP_THE_CURRENT_LOGGED_IN_ADMIN_ACCOUNT' ); ?>
					</h3>
					<div class="card-text">
						<div class="radio">
							<label>
								<input type="radio" name="keep_user" class="keep_user" id="keep_user_1" value="yes" checked="checked">
								<?php echo JText::_( 'JYES' ); ?>
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="keep_user" class="keep_user" id="keep_user_2" value="no">
								<?php echo JText::_( 'JNO' ); ?>
							</label>
						</div>
						<div id="new_admin_account" class="hidden">
							<hr>
							<div class="form-group">
								<label for="admin_email">
									<?php echo JText::_( 'JSN_JOOMLARESET_NEW_ADMIN_ACCOUNT_EMAIL' ); ?>
								</label>
								<input type="email" id="admin_email" name="admin_email" value="" class="form-control">
							</div>
							<div class="form-group">
								<label for="admin_username">
									<?php echo JText::_( 'JSN_JOOMLARESET_NEW_ADMIN_ACCOUNT_USERNAME' ); ?>
								</label>
								<input type="text" id="admin_username" name="admin_username" value="" class="form-control">
							</div>
							<div class="form-group">
								<label for="admin_password">
									<?php echo JText::_( 'JSN_JOOMLARESET_NEW_ADMIN_ACCOUNT_PASSWORD' ); ?>
								</label>
								<input type="password" id="admin_password" name="admin_password" value="" class="form-control">
							</div>
							<div class="form-group">
								<label for="admin_password_2">
									<?php echo JText::_( 'JSN_JOOMLARESET_NEW_ADMIN_ACCOUNT_PASSWORD_2' ); ?>
								</label>
								<input type="password" id="admin_password_2" name="admin_password_2" value="" class="form-control">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card mb-3">
				<div class="card-body">
					<h3 class="card-title">
						<?php echo JText::_( 'JSN_JOOMLARESET_KEEP_THE_JSN_JOOMLARESET_EXTENSION' ); ?>
					</h3>
					<div class="card-text">
						<div class="radio">
							<label>
								<input type="radio" name="keep_reset" class="keep_reset" id="keep_reset_1" value="yes" checked="checked">
								<?php echo JText::_( 'JYES' ); ?>
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="keep_reset" class="keep_reset" id="keep_reset_2" value="no">
								<?php echo JText::_( 'JNO' ); ?>
							</label>
						</div>
					</div>
				</div>
			</div>
			<button type="button" id="reset_joomla" class="btn btn-block btn-danger">
				<?php echo JText::_( 'JSN_JOOMLARESET_RESET_JOOMLA_NOW' ); ?>
			</button>
		</div>
		<div id="reset-joomla-progress" class="jsn-bootstrap4 hidden">
			<div class="uninstall_extensions">
				<h3>
					<?php echo JText::_( 'JSN_JOOMLARESET_UNINSTALLING_3RD_PARTY_EXTENSIONS' ); ?>
				</h3>
				<div class="progress progress-striped active">
					<div class="progress-bar" style="width: 0%;">
						0%
					</div>
				</div>
				<ul class="error"></ul>
			</div>
			<div class="restore_database hidden">
				<h3>
					<?php echo JText::_( 'JSN_JOOMLARESET_RESTORING_DATABASE' ); ?>
				</h3>
				<div class="progress progress-striped active">
					<div class="progress-bar" style="width: 0%;">
						0%
					</div>
				</div>
				<ul class="error"></ul>
			</div>
			<div class="reset_completed hidden">
				<h3>
					<?php echo JText::_( 'JSN_JOOMLARESET_RESET_COMPLETED' ); ?>
				</h3>
				<a href="<?php echo JUri::base( true ); ?>" class="btn btn-block btn-success">
					<?php echo JText::_( 'JSN_JOOMLARESET_CLICK_TO_REFRESH_JOOMLA_ADMIN' ); ?>
				</a>
			</div>
		</div>
	</div>
</form>
<?php
// Render header.
JsnExtFwHtml::renderHeaderComponent();

// Render footer.
JsnExtFwHtml::renderFooterComponent();
