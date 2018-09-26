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
JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));

// Get ACL declaration.
$component = JFactory::getApplication()->input->getCmd('component');
$form = '';

if (strpos($component, 'com_') === false)
{
	$component = 'com_poweradmin2';
}

// Check if the component folder has access.xml file.
if ( is_file(JPATH_ADMINISTRATOR . "/components/{$component}/access.xml") )
{
	$form = '<?xml version="1.0" encoding="utf-8"?>
<form>
	<fieldset
		name="permissions"
		label="JCONFIG_PERMISSIONS_LABEL"
		description="JCONFIG_PERMISSIONS_DESC"
	>
		<field
			name="rules"
			type="parules"
			filter="rules"
			validate="rules"
			section="component"
			component="' . $component . '"
		/>
	</fieldset>
</form>';
}

// Finalize form declaration.
if ( empty($form) ) :
?>
<div class="alert alert-info">
	<span class="icon-info"></span>
	<?php echo JText::_('JSN_POWERADMIN_COMPONENT_HAS_NO_PERMISSION_OPTIONS'); ?>
</div>
<?php
else :

// Load component language.
JFactory::getLanguage()->load($component);

// Render form.
JLoader::register('JFormFieldPARules', JPATH_ADMINISTRATOR . '/components/com_poweradmin2/models/fields/parules.php');
JLoader::load('JFormFieldPARules');

$form = JForm::getInstance( 'permissions', $form, array('control' => 'jform') );
?>
<form id="component-form" name="adminForm" autocomplete="off" class="form-validate" method="POST" action="<?php
	echo JRoute::_("index.php?option=com_config&component={$component}&tmpl=component");
?>">
	<div class="control-group">
		<?php echo $form->renderFieldset('permissions'); ?>
	</div>
	<input type="hidden" name="task" value="config.save.component.save" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php
endif;
