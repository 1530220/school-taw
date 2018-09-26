<?php
/**
 * @version    $Id$
 * @package    JSN_Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */
defined('JPATH_BASE') or die;

/**
 * Supports an HTML select list of newsfeeds.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class JFormFieldJSNToken extends JSNFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 */
	protected $type = 'jsnToken';

	/**
	 * True to translate the default value string.
	 *
	 * @var	boolean
	 */
	protected $defaultTranslation;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	 The field input markup.
	 */
	protected function getInput()
	{
		$_type = isset($this->element['input-type']) ? $this->element['input-type'] : 'text';
		$_value = $this->element['defaultTranslation'] ? JText::_($this->value) : $this->value;
		$_extText = $this->element['exttextTranlation'] ? JText::_($this->element['exttext']) : $this->element['exttext'];
		$class = isset($this->element['class']) ? $this->element['class'] : "";

		$html [] = '<p>' . JText::_('JSN_EXTFW_TOKEN_KEY_DESC') . '</p>';
		$html [] = '
		<div class="row hide">
			<div class="span12">
				<div id="jsnextfw-get-token-message" class="jsnextfw-token-message"></div>
			</div>
		</div>
		<div class="row">
			<div class="span6">
				<label for="jsnextfw-token-key-username">' . JText::_('JSN_EXTFW_USERNAME') . ': </label>
				<input type="text" class="form-control jsn-input-large-fluid" id="jsnextfw-token-key-username" placeholder="' . JText::_('JSN_EXTFW_USERNAME') . '">
			</div>
			<div class="span6">
				<label for="jsnextfw-token-key-password">' . JText::_('JSN_EXTFW_PASSWORD') . ': </label>
				<input type="password" class="form-control jsn-input-large-fluid" id="jsnextfw-token-key-password" placeholder="' . JText::_('JSN_EXTFW_PASSWORD') . '">
			</div>
		</div>
		<div class="row">
			<div class="span12 padding-top-5">
				<button type="button" class="btn btn-default" id="jsnextfw-get-token-key-btn"><i class="icon-key"></i> ' . JText::_('JSN_EXTFW_GET_TOKEN_KEY') . '</button>
			</div>
		</div>
		<hr />
		<div class="row">
			<div class="span12">
				<label for="sunfw-token-key-username">' . JText::_('JSN_EXTFW_CURRENT_TOKEN_KEY') . ': </label>
				<input type="text" class="form-control jsn-input-small-fluid" id="' . $this->id . '" name="' . $this->name .'" value="' . $_value . '" readonly="readonly" placeholder="' . JText::_('JSN_EXTFW_TOKEN_KEY_IS_NOT_SET') . '">
			</div>
		</div>';
		return implode($html);
	}

	/**
	 * Get the field label markup.
	 *
	 * @return  string
	 */
	protected function getLabel()
	{
		return '';
	}
}
