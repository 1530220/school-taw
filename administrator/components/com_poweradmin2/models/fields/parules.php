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

// Load the base class.
require_once JPATH_ROOT . '/libraries/joomla/form/fields/rules.php';

/**
 * Create field class to configure component permissions.
 */
class JFormFieldPARules extends JFormFieldRules
{
	/**
	 * The form field type.
	 *
	 * @var	string
	 */
	public $type = 'PARules';

	/**
	 * Disable label markup.
	 *
	 * @return  string
	 */
	protected function getLabel()
	{
		return '';
	}

	/**
	 * Render markup for input field.
	 *
	 * @return  string
	 */
	protected function getInput()
	{
		// Add menu groups under the 'Components' backend menu if editing permissions for JSN PowerAdmin.
		if ($this->component == 'com_poweradmin2')
		{
			$items = JSNPowerAdmin2Helper::getComponentsMenu();

			foreach ($items as $item)
			{
				if ( is_array($item) )
				{
					$action = $this->element->addChild('action');

					$name = strtolower( preg_replace('/[^a-zA-Z0-9\-\._]+/', '-', $item['title']) );
					$title = JText::sprintf('JSN_POWERADMIN_SEE_COMPONENTS_MENU_GROUP', $item['title']);
					$description = JText::sprintf('JSN_POWERADMIN_SEE_COMPONENTS_MENU_GROUP_DESC', $item['title']);

					$action->addAttribute('name', "core.view.component.group.{$name}");
					$action->addAttribute('title', $title);
					$action->addAttribute('description', $description);
				}
			}
		}

		// Generate markup.
		$html[] = parent::getInput();
		$html[] = '
			<input type="hidden" id="jform_title" name="jform[title]" value="' . $this->component . '" />
			<script type="text/javascript">
				jQuery(function($) {
					$(window).load(function() {
						jQuery(document).ajaxSend(function(event, xhr, opts) {
							if (opts.url.indexOf("index.php?option=com_config&task=config.store") > -1) {
								var params = (opts.data || opts.url.split("?")[1]).split("&"), data = {};

								for (var i = 0; i < params.length; i++) {
									var pairs = params[i].split("=");

									data[pairs[0]] = pairs[1];
								}

								if (data.title.indexOf("com_") == 0 && data.comp != data.title) {
									data.comp = data.title;
								}

								if (data.action.indexOf("core.") > 0) {
									data.action = "core." + data.action.split("core.")[1];
								}

								opts.data = [];

								for (var name in data) {
									opts.data.push(name + "=" + data[name]);
								}

								opts.data = opts.data.join("&");

								if (data.option && data.task) {
									opts.url = opts.url.split("?")[0] + "?" + opts.data;
								}
							}
						});
					});
				});
			</script>
		';

		return implode($html);
	}
}
