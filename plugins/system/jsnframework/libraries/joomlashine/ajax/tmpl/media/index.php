<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  JSNTPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file.
defined('_JEXEC') or die('Restricted access');

// Get input object.
$input = JFactory::getApplication()->input;

if ($input->getCmd('option') == 'com_ajax') :

// Get Joomla document object.
$doc = JFactory::getDocument();
?>
<!DOCTYPE html>
<html lang="<?php echo $doc->language; ?>" dir="<?php echo $doc->direction; ?>">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<?php
	// Load and render document head.
	$head = $doc->loadRenderer('head');

	echo $head->render('');
	?>
	<style type="text/css">
		#media-selector > div {
			height: 100vh !important;
		}
	</style>
</head>
<body>
<?php
endif;
?>
	<div id="media-selector"></div>
	<script type="text/javascript">
		(function renderMediaSelector() {
			if ( ! window.BBMediaSelector ) {
				return setTimeout(renderMediaSelector, 200);
			}

			// Define config for BB Media Selector library.
			const config = {
				baseURL: '<?php echo JUri::root(); ?>',
				getAllFiles: '<?php echo "{$this->baseUrl}&action=getListFiles"; ?>',
				getFullDirectory: '<?php echo "{$this->baseUrl}&action=getFullDirectory"; ?>',
				uploadFile: '<?php echo "{$this->baseUrl}&action=uploadFile"; ?>',
				createFolder: '<?php echo "{$this->baseUrl}&action=createFolder"; ?>',
				deleteFolder: '<?php echo "{$this->baseUrl}&action=deleteFolder"; ?>',
				deleteFile: '<?php echo "{$this->baseUrl}&action=deleteFile"; ?>',
				renameFolder: '<?php echo "{$this->baseUrl}&action=renameFolder"; ?>',
				renameFile: '<?php echo "{$this->baseUrl}&action=renameFile"; ?>',
			}

			// Render BB Media Selector library.
			ReactDOM.render(
				React.createElement(BBMediaSelector, {config: config}),
				document.getElementById('media-selector')
			);

			// Init action to select a media file.
			var
			updater = '<?php echo JFactory::getApplication()->input->getString('handler'); ?>',
			element = '<?php echo JFactory::getApplication()->input->getString('element'); ?>';

			if ( window.parent && (element != '' || updater != '' || window.parent.jInsertFieldValue) ) {
				var
				selected,
				button = window.parent.document.querySelector('.modal.in .modal-footer .btn-primary'),
				addEvent = function(elm, evt, fn) {
					if (typeof elm.addEventListener == 'function') {
						elm.addEventListener(evt, fn);
					} else if (typeof elm.attachEvent == 'function') {
						elm.attachEvent(evt, fn);
					}
				},
				removeEvent = function(elm, evt, fn) {
					if (typeof elm.removeEventListener == 'function') {
						elm.removeEventListener(evt, fn);
					} else if (typeof elm.detachEvent == 'function') {
						elm.detachEvent(evt, fn);
					}
				},
				triggerEvent = function(elm, evt) {
					if (typeof elm.dispatchEvent == 'function') {
						elm.dispatchEvent( new window.Event(evt) );
					} else if (typeof elm.fireEvent == 'function') {
						elm.fireEvent( 'on' + evt, document.createEventObject() );
					}
				},
				select = function(event) {
					event.preventDefault();

					// If there is a updater provided, call it.
					if (updater && window.parent[updater]) {
						window.parent[updater](selected, '#' + element);
					}

					// Set new value for the affected field.
					else if (element) {
						var field = window.parent.document.getElementById(element);

						if (field) {
							field.value = selected;

							// Trigger a change event on the affected field.
							triggerEvent(field, 'change');
						}
					}

					// If there is a callback function, call it.
					else if (window.parent.jInsertFieldValue) {
						window.parent.jInsertFieldValue(selected, element);
					}

					// Stop listening to 'click' event on the select button.
					if (button && button.isBBMediaSelectorSelectButton) {
						delete button.isBBMediaSelectorSelectButton;

						removeEvent(button, 'click', select);
					}
				},
				change = function(event) {
					selected = event.detail;

					if (button) {
						// Listen to 'click' event on the select button.
						if ( ! button.isBBMediaSelectorSelectButton ) {
							addEvent(button, 'click', select);

							button.isBBMediaSelectorSelectButton = true;
						}
					} else {
						// Select immediately.
						select(event);

						// Close modal automatically.
						var close = window.parent.document.querySelector('.modal.in .close');

						if (close) {
							close.click();
						}
					}
				};

				// Listen to 'select-file' event on the document.
				if ( ! this.isBBMediaSelectorChangeListened ) {
					addEvent(document, 'select-file', change);

					this.isBBMediaSelectorChangeListened = true;
				}
			}
		})();
	</script>
<?php
if ($input->getCmd('option') == 'com_ajax') :
?>
</body>
</html>
<?php
endif;
