/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of eventHandler, a plugin for Dotclear 2.
 *
 * Copyright(c) 2014-2015 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
 *
 * Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 * contact@jcdenis.fr http://jcd.lv
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
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({address:address},
				 function(results, status) {
					 if (status == google.maps.GeocoderStatus.OK) {
						 $('#event_latitude').val(results[0].geometry.location.lat());
						 $('#event_longitude').val(results[0].geometry.location.lng());
						 var new_address = results[0].formatted_address;
						 if (new_address!=undefined){
							 $('#event_address').val(new_address);
						 }
					 } else {
						 alert("Geocode was not successful for the following reason: " + status);
					 }
				 });
		e.preventDefault();
		return false;
	});
});
