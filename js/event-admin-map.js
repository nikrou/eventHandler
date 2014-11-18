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
	var script = document.createElement("script");
	script.type = "text/javascript";
	script.src = "http://maps.google.com/maps/api/js?sensor=false&callback=eventGeocoderInit";
	document.body.appendChild(script);
});

var geocoder;
var map;

function eventGeocoderInit(){

	geocoder = new google.maps.Geocoder();

	$('#event-map-link').attr('href','#');

	$('#event-map-link').click(function(){
		eventCodeAddress();
		return false;
	});
}

function eventCodeAddress() {
	var address=$('#event_address').val();
	if (address==''){
		return false;
	}
	if (geocoder) {
		geocoder.geocode( { 'address': address}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				//split location into readable latlng
				var latlng = new String(results[0].geometry.location);
				latlng = latlng.slice(1,-1);
				loc = latlng.split(', ');
				if (loc[0]!=undefined && loc[1]!=undefined){
					$('#event_latitude').val(loc[0]);
					$('#event_longitude').val(loc[1]);
				}
				// rewrite returned address
				var new_address = results[0].formatted_address;
				if (new_address!=undefined){
					$('#event_address').val(new_address);
				}
			} else {
				alert("Geocode was not successful for the following reason: " + status);
			}
			return false;
		});
		return false;
	}
	return false;
}
