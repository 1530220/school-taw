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

define([
	'jquery',
	'jquery.ui',
	'jquery.tipsy'
],

function ($)
{
	// Declare JSNConfig contructor
	var JSNConfig = function(params) {
		// Object parameters
		this.params = params;

		$(document).ready($.proxy(function() {
			this.menuLinks = $('#jsn-config-menu a');
			
			this.initialize();
		}, this));
	};

	JSNConfig.prototype = {
		initialize: function () {
			var self = this;

			// Initialize menu link for config group
			this.menuLinks.unbind('linkBeforeRequest').bind('linkBeforeRequest', function (event) {
				$('i', this).addClass('jsn-icon-loading');
			});

			this.menuLinks.unbind('linkRequested').bind('linkRequested', function (event, response) {
				var	activeMenu = $('#jsn-config-menu li.active'),
					currentMenu = $(this).closest('li'),
					currentMenuIcon = $('i', this);

				activeMenu.removeClass('active');
				currentMenu.addClass('active');
				currentMenuIcon.removeClass('jsn-icon-loading');

				$('#jsn-config-form > div').html(response.content);

				if (this.id == 'linklangs' || this.id == 'linkpermissions') {
					$('form').bind('formSubmitted', $.proxy(function () {
						$(this).trigger('click');
					}, this));
				}
				
				self.initPermissionSettings();
				self.initTooltip();
				//self.initCheckToken();
				self.intGetToken();
			});

			this.initPermissionSettings();
			this.initTooltip();
			//this.initCheckToken();
			this.intGetToken();
		},

		initPermissionSettings: function() {
			// Initialize accordion for permission settings
			$("#permissions-sliders ul#rules").accordion({header: "h3"});
		},

		initTooltip: function() {
			// Initialize tooltip for form field label
			$('#jsn-config-form label.control-label').tipsy({
				gravity: 'w',
				fade: true
			});
		},
		
		intGetToken: function () {
			$('#jsnextfw-get-token-key-btn').unbind('click').bind('click', function (event) {
				var msgEle = $('#jsnextfw-get-token-message');
				var iconBtn			= $(this).find('i');
				msgEle.html('');
				msgEle.parent().parent().addClass('hide');
				msgEle.removeClass('alert alert-error alert-success');
				iconBtn.removeClass('icon-key').addClass('jsn-icon16 jsn-icon-loading');
				$.ajax({
					type: 'POST',
					dataType: 'json',
					url: 'index.php?option=' + jsn_ext_option + '&view=configuration&tmpl=component&task=configuration.getToken&' + jsn_ext_jtoken + '=1',
					data: {
						username: document.querySelector( '#jsnextfw-token-key-username'  ).value,
						password: document.querySelector( '#jsnextfw-token-key-password'  ).value,
					},					
					success: function (response) {
						if (response) {
							if (response.result == 'success') 
							{
								msgEle.html(response.message);							
								msgEle.addClass('alert alert-success');
								$('#jsnconfig_token_key').val(response.token);
								document.querySelector( '#jsnextfw-token-key-username'  ).value = '';
								document.querySelector( '#jsnextfw-token-key-password'  ).value = '';
								$('#global-parameter-token-key-field').find('button.btn-primary').trigger('click');
							} 
							else 
							{
								msgEle.html(response.message);
								msgEle.addClass('alert alert-error');
							}
							msgEle.parent().parent().removeClass('hide');
							iconBtn.addClass('icon-key').removeClass('jsn-icon16 jsn-icon-loading');
						}
					}
				});				
			});
		},
		
		initCheckToken: function () {
			var self = this;
			var emsg = $('#check-token-key-msg');
			var eloading = $('#loading-token-key'); 
			$('#check-token-key').unbind('click').bind('click', function (event) {
				eloading.removeClass('hide');
				emsg.removeClass('label-success').removeClass('label-important');
				emsg.html('');
				$.ajax({
					type: 'GET',
					dataType: 'json',
					url: 'index.php?option=' + jsn_ext_option + '&view=configuration&tmpl=component&task=configuration.verifyToken&' + jsn_ext_jtoken + '=1&token=' + $('#jsnconfig_token_key').val(),
					success: function (response) {
						if (response) {
							if (response.result == 'success') 
							{
								emsg.html(response.message);
								emsg.addClass('label-success');
							} 
							else 
							{
								emsg.html(response.message);
								emsg.addClass('label-important');
							}
							eloading.addClass('hide');
						}
					}
				});				
			});
		}
	};

	return JSNConfig;
});
