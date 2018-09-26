if (typeof(jQuery) != 'undefined') {
	var jsnISQuery = (typeof JoomlaShine != 'undefined' && typeof JoomlaShine.jQuery != 'undefined') ? JoomlaShine.jQuery : jQuery;
	if (typeof(oldJquery) != 'undefined')
	{	
		jQuery = oldJquery;
	}
}