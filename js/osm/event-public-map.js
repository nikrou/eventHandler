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
	var lat = long = zoom = 0, tile_layer = '';
	$('.event-map').each(function() {
		var map_container = this;

		lat = $('.event-map-info-lat', $(map_container)).text();
		long = $('.event-map-info-lng', $(map_container)).text();
		zoom = $('.event-map-info-zoom', $(map_container)).text();
		tile_layer = $('.event-map-info-tile-layer', $(map_container)).text();

		if (!lat || !long || !zoom || !tile_layer) {
			return;
		}

		$(map_container).show();
		var map = L.map(map_container).setView([lat,long], zoom);
		mapLink = '<a href="https://openstreetmap.org">OpenStreetMap</a>';
		map_properties = { attribution: 'Map data &copy; ' + mapLink, maxZoom: 20 };
		L.tileLayer(tile_layer, map_properties).addTo(map);

		// markers
		$('.event-map-marker', $(map_container)).each(function() {
			var popup_content = '<h3><a href="'+$('.event-map-marker-link', $(this)).text()+'">'+$('.event-map-marker-title', $(this)).text()+'</a></h3>';
			popup_content += '<p>'+$('.event-map-marker-address', $(this)).text()+'</p>';
			popup_content += '<p> - '+$('.event-map-marker-startdt', $(this)).attr('title')+'</p>';
			popup_content += '<p> - '+$('.event-map-marker-enddt', $(this)).attr('title')+'</p>';

			L.marker([$('.event-map-marker-latitude', $(this)).text(),
				  $('.event-map-marker-longitude', $(this)).text()
				 ])
				.addTo(map)
				.bindPopup(popup_content);
		});
	});
});
