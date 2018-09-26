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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Create image selector field.
 *
 * Below is a sample field declaration for generating image selector field:
 *
 * <code>&lt;field
 *     name="site_logo" type="jsnmedia" default=""
 *     label="JSN_SAMPLE_SITE_LOGO_LABEL" description="JSN_SAMPLE_SITE_LOGO_DESC"
 * /&gt;</code>
 *
 * @package  JSN_Framework
 * @since    1.0.0
 *
 */
class JFormFieldJSNMedia extends JSNFormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 */
	protected $type = 'JSNMedia';

	/**
	 * Method to get the field input markup for a media selector.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Preset output
		$html = array ();

		// Initialize variables
		$editable    = isset($this->element['editable'])    ? (string) $this->element['editable']    : '';
		$clearButton = isset($this->element['clearButton']) ? (string) $this->element['clearButton'] : '';
		$mediaRoot   = isset($this->element['directory'])   ? (string) $this->element['directory']   : '';

		$selectorLink = 'index.php?option=com_ajax&format=html&plugin=jsnframework&context=media'
			. '&extension=' . JFactory::getApplication()->input->getCmd('option')
			. '&element=' . $this->id . '&handler=JSNMediaUpdateField'
			. '&' . JSession::getFormToken() . '=1';

		// Load script to handle selection update
		$html[] = JSNHtmlAsset::loadScript(
			'jsn/media',
			array(
				'url'      => $selectorLink,
				'field'    => "#{$this->id}",
				'language' => JSNUtilsLanguage::getTranslated(array('JSN_EXTFW_CONFIG_CLICK_TO_SELECT'))
			),
			true
		);

		// Initialize attributes
		$class		= ' class="' . (isset($this->element['class']) ? (string) $this->element['class'] : 'jsn-input-large-fluid') . '"';
		$disabled	= $editable ? '' : ' disabled="disabled"';

		// Generate clear button
		$clear = '';

		if ($clearButton)
		{
			$clear	= '<button class="btn inline" type="button">' . JText::_('JSN_EXTFW_GENERAL_CLEAR') . '</button>';
		}

		// Generate markup
		$html[]	= '<div class="input-append row-fluid">'
				. '<input type="text" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '"' . $class . $disabled . ' />'
				. '<button class="btn" type="button">...</button>' . $clear
				. '</div>';

		return implode($html);
	}
}
