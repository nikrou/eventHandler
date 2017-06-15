/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of eventHandler, a plugin for Dotclear 2.
 *
 * Copyright(c) 2014-2017 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
 *
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * -- END LICENSE BLOCK ------------------------------------*/

$(function() {
	$('#event-map-link').click(function(e) {
		var address = $('#event_address').val();
		if (address==''){
			return false;
		}

		var geocode = 'https://nominatim.openstreetmap.org/search.php?format=json&addressdetails=1&limit=5&q=' + encodeURIComponent(address);

		// use jQuery to call the API and get the JSON results
		try {
			$.getJSON(geocode, function(data) {
				$('#event_latitude').val(data[0].lat);
				$('#event_longitude').val(data[0].lon);
			});
		} catch (e) {
			//console.log(e);
		}
		e.preventDefault();
		return false;
	});
});
