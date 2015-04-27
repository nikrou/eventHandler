/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of eventHandler, a plugin for Dotclear 2.
 *
 * Copyright(c) 2014 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
 *
 * Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 * contact@jcdenis.fr http://jcd.lv
 *
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * -- END LICENSE BLOCK ------------------------------------*/

$(function(){
	var localized_form = document.getElementById('entry-content');
	var start = document.getElementById('event_startdt');
	var end = document.getElementById('event_enddt');
	if(localized_form!=undefined){
		var start_dtPick=new datePicker(start);
		start_dtPick.img_top = '1.5em';
		start_dtPick.draw();
		var end_dtPick = new datePicker(end);
		end_dtPick.img_top = '1.5em';
		end_dtPick.draw();
	}
	$('#event-area-title').toggleWithLegend(
		$('#event-area-content'),{
			legend_click: true,
			cookie:'dcx_eventhandler_admin_options'
		}
	);
});
