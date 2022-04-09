// -- BEGIN LICENSE BLOCK ----------------------------------
//
// This file is part of eventHandler, a plugin for Dotclear 2.
//							    #
// Licensed under the GPL version 2.0 license.
//
// See LICENSE file or
// http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
//
// -- END LICENSE BLOCK ------------------------------------

$(function(){
    $('#event-area-title').toggleWithLegend(
	$('#event-area-content'),{
	    legend_click: true,
	    cookie:'dcx_eventhandler_admin_options'
	}
    );
});
