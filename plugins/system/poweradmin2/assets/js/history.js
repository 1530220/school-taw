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

(function(api) {
	var History = api.History = function(params) {
		this.params = params;

		// Skip tracking history on some pages.
		if ( String.prototype.match.call(window.location, /\boption=com_(config|joomprofile)\b/) ) {
			return;
		}

		// Save last clicked link to cookie.
		api.Event.add(document, 'click', function(event) {
			// Find clicked target.
			var target = event.target;

			while (target && target.nodeName != 'A' && target.nodeName != 'BODY') {
				target = target.parentNode;
			}

			if ( ! target || target.nodeName == 'BODY' ) {
				return;
			}

			if (target.href && target.href.indexOf('?option=com_') > -1) {
				Cookies.set('jsn-poweradmin-last-link', target.href.substring( target.href.indexOf('?') + 1) );
			}
		});

		// Get data from the current page.
		var getData = function() {
			var
			title       = this.findTitle(),
			description = this.findDesc(),
			checkboxes  = document.querySelectorAll('input[name="boxchecked"]');

			if ( ! title ) {
				if (checkboxes.length) {
					this.findPage( function(page) {
						Cookies.set( 'jsn-poweradmin-list-page', JSON.stringify(page) );
					}.bind(this) );
				} else {
					Cookies.set('jsn-poweradmin-list-page', null);
				}

				return;
			}

			// Get history for the current page.
			var
			page        = JSON.parse( Cookies.get('jsn-poweradmin-list-page') ),
			clickedLink = Cookies.get('jsn-poweradmin-last-link'),
			sessionKey  = Cookies.get('jsn-poweradmin-post-session'),
			pageKey     = Cookies.get('jsn-poweradmin-page-key');

			if ( ! pageKey ) {
				return;
			}

			var entry = {
				title          : title.value,
				description    : description ? api.Text.stripTags(description.value) : '',
				pageKey        : pageKey,
				postSessionKey : sessionKey || '',
				lastClickedLink: clickedLink || '',
				currentLink    : clickedLink ? window.location.search.substring(1) : ''
			};

			if (page) {
				entry.iconCss  = page.css;
				entry.iconPath = page.path;
				entry.name     = page.name;
				entry.parent   = page.parent;
				entry.params   = page.key
			}

			api.Ajax.request(
				'index.php?option=com_poweradmin2&task=history.save&' + this.params.token + '=1',
				function(res) {
					this.attachHistory(parseInt(res.responseText), title);
				}.bind(this),
				entry
			);
		}.bind(this);

		if ( document.readyState == 'complete' || (document.readyState != 'loading' && ! document.documentElement.doScroll) ) {
			getData();
		} else {
			api.Event.add(document, 'DOMContentLoaded', getData);
		}
	};

	History.prototype = api.History.prototype = {
		findTitle: function() {
			var inputs = document.querySelectorAll('input[name]');

			for (var i = 0; i < inputs.length; i++) {
				if (inputs[i].name.match(/\b(title|name|subject|label)\b/i) && inputs[i].value) {
					return inputs[i];
				}
			}
		},

		findDesc: function() {
			var textareas = document.querySelectorAll('textarea[name]');

			for (var i = 0; i < textareas.length; i++) {
				if (
					textareas[i].name.indexOf('meta') < 0
					&&
					textareas[i].name.match(/\b(articletext|description|desc|intro|introtext|introduction|about|note|content)\b/i)
				) {
					return textareas[i];
				}
			}

			// If no description input found, fall back to description display.
			textareas = document.querySelector('span.mod-desc, span.plg-desc');

			if (textareas) {
				return {
					value: textareas.textContent
				}
			}
		},

		findIconPath: function(menu) {
			var
			menuCss = window.getComputedStyle(menu),
			backgroundImage = menuCss.getPropertyValue('background-image').trim(),
			regex = /^url\(\s*['|"]?\s*([^\)]+)\s*['|"]?\s*\)$/i,
			match = backgroundImage.match(regex);

			if ( ! match ) {
				return '';
			}

			var image = match[1], uri = window.location.pathname;

			if (uri.indexOf('index.php') > -1) {
				uri = uri.substring( 0, uri.indexOf('index.php') );
			}

			if (image.indexOf(uri) > -1) {
				image = image.substring(image.indexOf(uri) + uri.length);
			}

			return image;
		},

		getMenuInformation: function(menu, parentMenu) {
			return {
				css   : menu.className,
				icon  : this.findIconPath(menu),
				name  : menu.textContent,
				key   : Cookies.get('jsn-poweradmin-page-key'),
				parent: parentMenu ? parentMenu.textContent : ''
			};
		},

		findPage: function(callback) {
			var
			menubar = document.querySelector('#menu, #pa-admin-bar ul.nav'),
			queryString = window.location.search,
			params = {},
			icon = {};

			if ( ! menubar ) {
				return setTimeout(this.findPage.bind(this, callback), 200);
			}

			// Skip if menubar is disabled.
			if ( menubar.classList.contains('disabled') ) {
				if (typeof callback == 'function') {
					callback(null);
				}

				return null;
			}

			// Prepare query string.
			if (queryString.indexOf('?') > -1) {
				queryString = queryString.substring(1);
			}

			// Convert query string to object.
			params = api.Text.parseQueryString(queryString);

			// Find component name inside page when it does not exist in query string.
			if ( ! params.option ) {
				var input = document.querySelector('input[name="option"]');

				if ( ! input ) {
					var page = this.getMenuInformation( menubar.querySelector('a[href="index.php"]') );

					if (typeof callback == 'function') {
						callback(page);
					}

					return page;
				}

				params.option = input.value;
			}

			// Find information for component that used com_categories for category management.
			if (params.option == 'com_categories' && params.extension) {
				var
				menu = menubar.querySelector('a[href*="option=com_categories&extension=' + params.extension + '"]'),
				component = menubar.querySelector('a[href*="option=' + params.extension +'"]');

				if ( ! menu ) {
					menu = component;
				}

				var page = this.getMenuInformation(menu, component);

				if (typeof callback == 'function') {
					callback(page);
				}

				return page;
			}

			// Find menu item of the current page.
			var
			menu  = menuRoot = menubar.querySelector('a[href="index.php?option=' + params.option +'"]'),
			items = menubar.querySelectorAll('a[href="index.php?option=' + params.option +'"]');

			for (var i = 0; i < items; i++) {
				var
				menuLink       = items[i].href,
				queryString    = menuLink.indexOf('?') > -1 ? menuLink.substring(menuLink.indexOf('?') + 1) : '',
				menuParams     = api.Text.parseQueryString(queryString),
				sameController = (params.controller !== undefined && menuParams.controller !== undefined && params.controller == menuParams.controller),
				sameView       = (params.view !== undefined && menuParams.view !== undefined && params.view == menuParams.view),
				sameTask       = (params.task !== undefined && menuParams.task !== undefined && params.task == menuParams.task),
				sameViewTask   = (sameView && sameTask),
				sameAll        = (sameViewTask && sameController);

				if (sameAll || sameViewTask || sameController || sameView || sameTask) {
					menu = menuItem;

					break;
				}
			}

			if ( ! menu ) {
				if (typeof callback == 'function') {
					callback(null);
				}

				return null;
			}

			var page = this.getMenuInformation(menu, menuRoot);

			if (typeof callback == 'function') {
				callback(page);
			}

			return page;
		},

		attachHistory: function(id, field) {
			var
			input_id    = document.createElement('input'),
			input_title = document.createElement('input');

			input_id.type  = 'hidden';
			input_id.name  = 'jsn_history_id';
			input_id.value = id;

			input_title.type  = 'hidden';
			input_title.name  = 'jsn_history_title';
			input_title.value = field.value;

			field.parentNode.appendChild(input_id);
			field.parentNode.appendChild(input_title);

			api.Event.add(field, 'change', function() {
				input_title.value = field.value;
			});
		}
	};
})( (JSN = window.JSN || {}) );
