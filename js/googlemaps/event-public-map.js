/* -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of eventHandler, a plugin for Dotclear 2.
 *
 * Copyright(c) 2014-2017 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
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
	script.src = "https://maps.google.com/maps/api/js?sensor=false&callback=eventMapInit";
	document.body.appendChild(script);
});

function eventMapInit(){

	// find place of map on page
	$('.event-map').each(function(){
		$(this).toggle();
		var gmapPlace = $(this).children('.event-map-place');

		// find map info
		var gmapInfo_zoom = $(this).children('.event-map-info').children('.event-map-info-zoom').text();
		var gmapInfo_type = $(this).children('.event-map-info').children('.event-map-info-type').text();
		var gmapInfo_lat = $(this).children('.event-map-info').children('.event-map-info-lat').text();
		var gmapInfo_lng = $(this).children('.event-map-info').children('.event-map-info-lng').text();
		var gmapInfo_info = $(this).children('.event-map-info').children('.event-map-info-info').text();

		// if geo latlng is set
		if (gmapInfo_lat!='' && gmapInfo_lng!='') {
			// create map
			var map = new google.maps.Map($(gmapPlace).get(0), {
				zoom: parseInt(gmapInfo_zoom, 10),
				center: new google.maps.LatLng(gmapInfo_lat, gmapInfo_lng),
				mapTypeId: eval('google.maps.MapTypeId.'+gmapInfo_type),
				disableDefaultUI: true,
				navigationControl: true
			});
			// loop through markers
			$(this).children('.event-map-marker').each(function(){
				// find event info
				var gmapMarker_title=$(this).children('.event-map-marker-title').text();
				var gmapMarker_latitude=$(this).children('.event-map-marker-latitude').text();
				var gmapMarker_longitude=$(this).children('.event-map-marker-longitude').text();
				var gmapMarker_address=$(this).children('.event-map-marker-address').text();
				var gmapMarker_startdt=$(this).children('.event-map-marker-startdt').text();
				var gmapMarker_enddt=$(this).children('.event-map-marker-enddt').text();
				var gmapMarker_read_startdt=$(this).children('.event-map-marker-startdt').attr('title');
				var gmapMarker_read_enddt=$(this).children('.event-map-marker-enddt').attr('title');
				var gmapMarker_link=$(this).children('.event-map-marker-link').text();

				// create center point
				var gmapMarker_LatLng = new google.maps.LatLng(gmapMarker_latitude, gmapMarker_longitude);
				// set address to latlng if none
				if (gmapMarker_address==''){
					var gmapMarker_address = gmapMarker_latitude+', '+gmapMarker_longitude;
				}
				if (gmapInfo_info=='1'){
					// marker bubble content
					var contentString = '<div id="content">'+
					'<div id="siteNotice">'+
					'</div>'+
					'<h3 id="firstHeading" class="firstHeading">'+gmapMarker_title+'</h3>'+
					'<div id="bodyContent"><p>'+
					'- '+gmapMarker_address+'<br />'+
					'- '+gmapMarker_read_startdt+'<br />'+
					'- '+gmapMarker_read_enddt+'<br />'+
					'- <a href="'+gmapMarker_link+'">'+gmapMarker_link+'</a>'+
					'</p></div>'+
					'</div>';
					// create marker bubble
					var infowindow = new google.maps.InfoWindow({
						content: contentString
					});
				}
				// set marker
				var curMarker = new google.maps.Marker({
					position: gmapMarker_LatLng,
					title: gmapMarker_title
				});
				// add marker
				curMarker.setMap(map);
				if (gmapInfo_info=='1') {
					// add bubble on mouse over
					google.maps.event.addListener(curMarker, 'click', function() {
						infowindow.open(map,curMarker);
					});
				} else {
					// open page on click
					google.maps.event.addListener(curMarker, 'click', function() {
						window.location=gmapMarker_link;
					});
				}
			});
		}else{
			$(this).remove();
		}
	});
}
