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

define(['jsn/libs/common/edition'], function (Edition) {
	var JSNTestEdition = function(params) {
		this.data = {};

		this.editionManager = new Edition( params, function(data) {
			console.log(data);
			if ( JSON.stringify(this.data) != JSON.stringify(data) ) {
				this.data = data;

				// Show a test message about feature limitation.
				setTimeout(function() {
					this.editionManager.introducePro('sample-title', 'sample-message');
				}.bind(this), 1);
			}
		}.bind(this) );
	};

	return JSNTestEdition;
});
