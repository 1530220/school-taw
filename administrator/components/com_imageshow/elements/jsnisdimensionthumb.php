<?php
/**
 * @author JoomlaShine.com Team
 * @copyright JoomlaShine.com
 * @link joomlashine.com
 * @package JSN ImageShow
 * @version $Id$
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');
class JFormFieldJSNISDimensionThumb extends JFormField
{
	public $type = 'JSNISDimensionThumb';

	protected function getInput()
	{
		$msg        = JText::_('JSN_ALLOW_ONLY_DIGITS');
		$doc 		= JFactory::getDocument();
		$doc->addScriptDeclaration("

			var JSNISOriginalValue = '';
			function JSNISChangeInputThumbDimension()
			{
				var item		= new Array();
				var objWidth 	= jQuery('#tmp_width_thumb_dimension');
				var objHeight 	= jQuery('#tmp_height_thumb_dimension');
				item[0] 		= objWidth.val();
				item[1] 		= objHeight.val();
				var JSNISThumbDimensionElement		= jQuery('#".$this->id."');
				JSNISThumbDimensionElement.val('');
				JSNISThumbDimensionElement.val(item.join(','));
			}

			function JSNISGetInputValue(object)
			{
				JSNISOriginalValue = object.value;
			}

			function JSNISCheckNumberValue(object)
			{
				var patt;
				var msg;
				patt = /^[0-9]+$/;
				msg  = '" . $msg . "';
				if(object.value != '' && !patt.test(object.value))
				{
					alert (msg);
					object.value = JSNISOriginalValue;
					return;
				}
			}

			(function($){



				function JSNISSetValueWidthHeight(str)
				{
					var width 	= $('#tmp_width_thumb_dimension');
					var height 	= $('#tmp_height_thumb_dimension');

					if (str != '')
					{
						var item = str.split(',');
						if (item[0] != undefined)
						{
							width.val(item[0]);
						}

						if (item[1] != undefined)
						{
							height.val(item[1]);
						}
					}
					else
					{
						width.val(250);
						height.val(150);
					}
				}

				$(document).ready(function() {
					JSNISSetValueWidthHeight('".$this->value."');
				});
			})((typeof JoomlaShine != 'undefined' && typeof JoomlaShine.jQuery != 'undefined') ? JoomlaShine.jQuery : jQuery);


		");
		$html       = '';
		$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : 'class="jsn-text"';
		$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$postfix	= (isset($this->element['postfix'])) ? '<span class="jsn-postfix">'.$this->element['postfix'].'</span>' : '';
		$html   = '<span style="float:left; margin:0 5px 0 0; line-height: 28px;"><input placeholder="' . JText::_('MENU_OVERALL_THUMB_DIMENSION_WIDTH') . '" type="text" onfocus="JSNISGetInputValue(this);" onchange="JSNISCheckNumberValue(this); JSNISChangeInputThumbDimension()" name="tmp_width_thumb_dimension" id="tmp_width_thumb_dimension" value="" size="5" /> x </span>';
		$html  .= '<span style="float:left; margin:0 5px 0 0; line-height: 28px;"><input placeholder="'. JText::_('MENU_OVERALL_THUMB_DIMENSION_HEIGHT') . '" type="text" onfocus="JSNISGetInputValue(this);" onchange="JSNISCheckNumberValue(this); JSNISChangeInputThumbDimension()" name="tmp_height_thumb_dimension" id="tmp_height_thumb_dimension" value="" size="5" /></span> px';
		$html  .= '<input type="hidden" name="'.$this->name.'" id="'.$this->id.'"' .
				' value="'.$this->value.'"' .
				$class.$size.$disabled.$readonly.$maxLength.'/> '.$postfix;

		return $html;
	}
}