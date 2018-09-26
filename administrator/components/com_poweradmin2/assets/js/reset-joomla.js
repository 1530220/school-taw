/**
 * @version    $Id$
 * @package    JSN_JoomlaReset
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

( function(api) {
	function check() {
		var mask = document.querySelector('#j-main-container > .pa-overlay-mask');

		// Check if the current license is free?
		if (api.isCurrentUserFree('com_poweradmin2')) {
			// Create a mask once.
			if (!mask) {
				var container = document.getElementById('j-main-container');
				var mask = document.createElement('div');

				container.style.position = 'relative';

				mask.className = 'pa-overlay-mask';
				mask.innerHTML =
					'<div class="pa-overlay-message"><p>' + jsn_joomlareset.zwXEjFPU + '</p>'
						+ '<button type="button" class="btn btn-default">' + jsn_joomlareset.vAm7nbhW + '</button></div>';

				container.appendChild(mask);

				// Listen to click event on the Learn More button.
				api.Event.add('.pa-overlay-message > button', 'click', function() {
					api.showIntroduceProModal(jsn_joomlareset.dGsY8kMV, jsn_joomlareset.k8cuGn7t, 'com_poweradmin2');
				});
			}
		}

		// Otherwise, init site reset form.
		else {
			if (mask) {
				mask.parentNode.removeChild(mask);
			}

			// Setup action to handle click event on radio buttons.
			api.Event.add('input[type="radio"]', 'click', function(event) {
				// Get all radio inputs of the same name.
				var inputs = document.querySelectorAll('input[name="' + event.target.name + '"]');

				// Loop thru radio inputs to update attribute.
				for (var i = 0; i < inputs.length; i++) {
					if (inputs[i].checked) {
						inputs[i].setAttribute('checked', 'checked');
					}
					else {
						inputs[i].removeAttribute('checked');
					}
				}
			});

			// Setup action for creating new admin account.
			api.Event.add('input[name="keep_user"]', 'click', function() {
				document.querySelector('#new_admin_account').classList[this.value == 'yes' ? 'add' : 'remove']('hidden');
			});

			// Setup submit button.
			api.Event.add('#reset_joomla', 'click', function(event) {
				// Validate data for new admin account.
				if (document.querySelector('input[name="keep_user"][checked]').value == 'no') {
					var valid = true;

					// Validate email.
					if (document.querySelector('input[name="admin_email"]').value == '') {
						var test = /^[a-zA-Z0-9\-\._]+@[a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]+){1,9}$/;

						if (!document.querySelector('input[name="admin_email"]').value.match(test)) {
							document.querySelector('input[name="admin_email"]').focus();

							valid = jsn_joomlareset.invalid_email;
						}
					}

					// Validate username.
					if (document.querySelector('input[name="admin_username"]').value == '') {
						var test = /^[a-zA-Z0-9\-\._]+$/;

						if (!document.querySelector('input[name="admin_username"]').value.match(test)) {
							document.querySelector('input[name="admin_username"]').focus();

							valid = jsn_joomlareset.invalid_username;
						}
					}

					// Validate password.
					if (document.querySelector('input[name="admin_password"]').value == '') {
						var test = document.querySelector('input[name="admin_password_2"]').value;

						if (document.querySelector('input[name="admin_password"]').value != test) {
							document.querySelector('input[name="admin_password"]').focus();

							valid = jsn_joomlareset.password_not_match;
						}
					}

					if (valid !== true) {
						event.preventDefault();

						return api.Modal.alert(valid);
					}
				}

				api.Modal.confirm(React.createElement('div', {}, [
					api.Text.parse(jsn_joomlareset.are_you_sure), React.createElement(api.ElementForm, {
						form: {
							controls: {
								'confirm': {
									type: 'text'
								}
							}
						}
					})
				]), function(modal) {
					// Get verification.
					var input = modal.refs.mountedDOMNode.querySelector('input[type="text"]');

					if (input && input.value == 'DELETE') {
						// Show resetting progress.
						document.querySelector('#reset-joomla').classList.add('hidden');
						document.querySelector('#reset-joomla-progress').classList.remove('hidden');

						// Send Ajax request to get list of 3rd-party extensions.
						api.Ajax.request('index.php?option=com_poweradmin2&task=reset.getExtensions', function(response) {
							var res = JSON.parse(response.responseText.match(/\{"\w+":.+\}$/)[0]);

							if (res && res.success) {
								uninstallExtensions(res.content);
							}
							else {
								api.Modal.alert(res ? res.content : response.responseText);
							}
						});
					}
				}, null, false, function() {
					// Disable the Ok button.
					document.querySelector('.modal-footer .btn-primary').disabled = true;

					// Listen to change event on the text field for confirmation.
					( function trackInputField() {
						var input = document.querySelector('.modal-body input[type="text"]');

						if (input) {
							api.Event.add(input, 'keyup', function(event) {
								if (event.target.value == 'DELETE') {
									document.querySelector('.modal-footer .btn-primary').disabled = false;
								}
								else {
									document.querySelector('.modal-footer .btn-primary').disabled = true;
								}
							});
						}
						else {
							setTimeout(trackInputField, 100);
						}
					} )();
				});
			});

			// Define function to uninstall extensions.
			function uninstallExtensions(extensions, current) {
				if (!extensions) {
					extensions = [];
				}

				if (!current) {
					current = 1;
				}

				if (current > extensions.length) {
					// Update progress.
					var progress = document.querySelector('#reset-joomla-progress .uninstall_extensions .progress-bar');

					progress.style.width = '100%';
					progress.textContent = '100%';

					document.querySelector('#reset-joomla-progress .uninstall_extensions .progress').classList.remove('active');

					// Jump to next step.
					return restore_database();
				}

				// Send Ajax request to uninstall extension.
				api.Ajax.request('index.php?option=com_poweradmin2&task=reset.uninstallExtension', function(response) {
					var res = response.responseText.match(/\{"\w+":.+\}$/);

					if (res) {
						res = JSON.parse(res[0]);

						// Update progress.
						var percent = Math.round(( current / extensions.length ) * 100) + '%';
						var progress = document.querySelector('#reset-joomla-progress .uninstall_extensions .progress-bar');

						progress.style.width = percent;
						progress.textContent = percent;

						// Uninstall next extension.
						setTimeout(function() {
							uninstallExtensions(extensions, ++current);
						}, 100);

						// Update status if necessary.
						if (!res || !res.success) {
							var status = document.createElement('li');

							status.innerHTML = res ? res.content : response.responseText;

							document.querySelector('#reset-joomla-progress .uninstall_extensions .error').appendChild(status);
						}
					}
					else {
						api.Modal.get({
							title: jsn_joomlareset.manual_uninstall_component.replace('%s', extensions[current - 1].name.substr(4)),
							type: 'iframe',
							content: {
								src: response.responseURL
							},
							width: '99%',
							height: '99%',
							buttons: [
								{
									text: jsn_joomlareset.continue_resetting_joomla,
									className: 'btn btn-primary',
									onClick: function(modal) {
										modal.close();

										// Update progress.
										var percent = Math.round(( current / extensions.length ) * 100) + '%';
										var progress =
											document.querySelector('#reset-joomla-progress .uninstall_extensions .progress-bar');

										progress.style.width = percent;
										progress.textContent = percent;

										// Uninstall next extension.
										setTimeout(function() {
											uninstallExtensions(extensions, ++current);
										}, 100);
									}
								}
							],
							onModalShown: function(event) {
								var buttons = event.target.refs.mountedDOMNode.querySelectorAll('.modal-footer .btn');

								for (var i = 0; i < buttons.length; i++) {
									buttons[i].disabled = false;
									buttons[i].removeAttribute('disabled');
								}
							}
						}, false);
					}
				}, {
					extension: extensions[current - 1]
				});
			}

			// Define function to restore database.
			function restore_database(current) {
				var total_step = 2;

				if (!current) {
					current = 1;
				}

				// Show progress.
				document.querySelector('#reset-joomla-progress .restore_database').classList.remove('hidden');

				// Prepare Ajax request.
				var request;

				switch (current) {
					case 1:
						// Drop unused tables.
						request = {
							url: 'index.php?option=com_poweradmin2&task=reset.dropUnusedTables',
							type: 'POST',
							data: {
								sample_data: document.querySelector('input[name="sample_data"][checked]').value,
								keep_reset: document.querySelector('input[name="keep_reset"][checked]').value
							}
						};
					break;

					case 2:
						// Import the selected sample data.
						request = {
							url: 'index.php?option=com_poweradmin2&task=reset.importSampleData',
							type: 'POST',
							data: {
								sample_data: document.querySelector('input[name="sample_data"][checked]').value,
								keep_reset: document.querySelector('input[name="keep_reset"][checked]').value,
								keep_user: document.querySelector('input[name="keep_user"][checked]').value,
								admin_email: document.querySelector('input[name="admin_email"]').value,
								admin_username: document.querySelector('input[name="admin_username"]').value,
								admin_password: document.querySelector('input[name="admin_password"]').value
							}
						};
					break;
				}

				if (!request) {
					// Update progress.
					var progress = document.querySelector('#reset-joomla-progress .restore_database .progress-bar');

					progress.style.width = '100%';
					progress.textContent = '100%';

					document.querySelector('#reset-joomla-progress .restore_database .progress').classList.remove('active');

					// Show success message.
					document.querySelector('#reset-joomla-progress .reset_completed').classList.remove('hidden');

					return;
				}

				// Send Ajax request.
				if (!request.complete) {
					request.complete = function(response) {
						var res = JSON.parse(response.responseText.match(/\{"\w+":.+\}$/)[0]);

						// Update progress.
						var percent = Math.round(( current / total_step ) * 100) + '%';
						var progress = document.querySelector('#reset-joomla-progress .restore_database .progress-bar');

						progress.style.width = percent;
						progress.textContent = percent;

						// Go to next step.
						restore_database(++current);

						if (!res || !res.success) {
							var status = document.createElement('li');

							status.innerHTML = res ? res.content : response.responseText;

							document.querySelector('#reset-joomla-progress .restore_database .error').appendChild(status);
						}
					};
				}

				api.Ajax.request(request.url, request.complete, request.data);
			}
		}
	}

	api.Edition.init({
		extension: 'com_poweradmin2',
		callback: function() {
			this.initJsnPowerAdminSiteResetTimeout && clearTimeout(this.initJsnPowerAdminSiteResetTimeout);

			this.initJsnPowerAdminSiteResetTimeout = setTimeout(check, 100);
		}
	});
} )(( JSN = window.JSN || {} ));
