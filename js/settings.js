/*
 * -- BEGIN LICENSE BLOCK ----------------------------------
 *
 * This file is part of eventHandler, a plugin for Dotclear 2.
 *
 * Copyright(c) 2014-2016 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
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
    $(window).bind('hashchange onhashchange', function (e) {
	$("input[name='section']").val('#'+$.pageTabs.getLocationHash());
    });

    var $map_provider = $('#map_provider'), $map_tile_layer = $('.map-tile-layer'), $map_api_key = $('.map-api-key');
    if ($map_provider.val()==='osm') {
	$map_api_key.hide();
    } else {
	$map_tile_layer.hide();
    }
    $map_provider.change(function() {
	if ($(this).val()=='osm') {
	    $map_tile_layer.show();
	    $map_api_key.hide();
	} else {
	    $map_tile_layer.hide();
	    $map_api_key.show();
	}
    });
});
