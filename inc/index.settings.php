<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2015 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
#
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'settings'){return;}

# Read settings
$active = (boolean) $s->active;
$public_posts_of_event_place = (string) $s->public_posts_of_event_place;
$public_events_of_post_place = (string) $s->public_events_of_post_place;
$public_events_list_sortby = (string) $s->public_events_list_sortby;
$public_events_list_order = (string) $s->public_events_list_order;
$public_hidden_categories = @unserialize($s->public_hidden_categories);
if (!is_array($public_hidden_categories)) {
	$public_hidden_categories = array();
}
$public_map_zoom = abs((integer) $s->public_map_zoom);
if (!$public_map_zoom) {
	$public_map_zoom = 9;
}
$public_map_type = (string) $s->public_map_type;
$public_extra_css = (string) $s->public_extra_css;

$map_provider = $s->map_provider;
$map_api_key = $s->map_api_key;
$map_tile_layer = $s->map_tile_layer;

# Combos
$combo_place = array(
	__('hide') => '',
	__('before content') => 'before',
	__('after content') => 'after'
);


$combo_list_sortby = array(
	'' => null,
	__('Post title') => 'post:title',
	__('Post selected') => 'post:selected',
	__('Post author') => 'post:author',
	__('Post date') => 'post:date',
	__('Post id') => 'post:id',
	__('Event start date') => 'eventhandler:startdt',
	__('Event end date') => 'eventhandler:enddt',
);
$combo_list_order = array(
	'' => null,
	__('Ascending') => 'asc',
	__('Descending') => 'desc'
);

for ($i=3;$i<21;$i++) {
	$combo_map_zoom[$i] = $i;
}
$combo_map_type = array(
	__('road map') => 'ROADMAP',
	__('satellite') => 'SATELLITE',
	__('hybrid') => 'HYBRID',
	__('terrain') => 'TERRAIN'
);


# Categories combo
$combo_categories = array('-'=>'');
try {
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
while ($categories->fetch()) {
	$combo_categories[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '. html::escapeHTML($categories->cat_title)] = $categories->cat_id;
}

# Map providers combo
$combo_map_provider = array(
	'GoogleMaps' => 'googlemaps',
	'OpenStreetMap' => 'osm'
);

# Action
if ($action == 'savesettings') {
	$default_tab = 'configuration';
	try	 {
		$active = !empty($_POST['active']);
		if (isset($_POST['public_posts_of_event_place']) && in_array($_POST['public_posts_of_event_place'], $combo_place)) {
			$public_posts_of_event_place = $_POST['public_posts_of_event_place'];
		}
		if (isset($_POST['public_events_of_post_place']) && in_array($_POST['public_posts_of_post_place'], $combo_place)) {
			$public_events_of_post_place = $_POST['public_events_of_post_place'];
		}
		if (isset($_POST['public_events_list_sortby']) && in_array($_POST['public_events_list_sortby'], $combo_list_sortby)) {
			$public_events_list_sortby = $_POST['public_events_list_sortby'];
		}
		if (isset($_POST['public_events_list_order']) && in_array($_POST['public_events_list_order'], $combo_list_order)) {
			$public_events_list_order = $_POST['public_events_list_order'];
		}
		if (isset($_POST['public_hidden_categories'])) {
			$public_hidden_categories = $_POST['public_hidden_categories'];
		} else {
			$public_hidden_categories = array();
		}
		if (!empty($_POST['public_map_zoom'])) {
			$public_map_zoom = abs((integer) $_POST['public_map_zoom']);
		}
		if (!$public_map_zoom) {
			$public_map_zoom = 9;
		}
		if (!empty($_POST['public_map_type']) && in_array($_POST['public_map_type'], $combo_map_type)) {
			$public_map_type = $_POST['public_map_type'];
		}
		if (!empty($_POST['public_extra_css'])) {
			$public_extra_css = $_POST['public_extra_css'];
		}
		if (!empty($_POST['map_provider']) && in_array($_POST['map_provider'], $combo_map_provider)) {
			$map_provider = $_POST['map_provider'];
		}
		if (!empty($_POST['map_tile_layer'])) {
			$map_tile_layer = $_POST['map_tile_layer'];
		}
		if (!empty($_POST['map_api_key'])) {
			$map_api_key = $_POST['map_api_key'];
		}

		$s->put('active', $active, 'boolean');
		$s->put('public_posts_of_event_place', $public_posts_of_event_place, 'string');
		$s->put('public_events_of_post_place', $public_events_of_post_place, 'string');
		$s->put('public_events_list_sortby', $public_events_list_sortby, 'string');
		$s->put('public_events_list_order', $public_events_list_order, 'string');
		$s->put('public_hidden_categories', serialize($public_hidden_categories), 'string');
		$s->put('public_map_zoom', $public_map_zoom, 'integer');
		$s->put('public_map_type', $public_map_type, 'string');
		$s->put('public_extra_css', $public_extra_css, 'string');
		$s->put('map_provider', $map_provider, 'string');
		$s->put('map_tile_layer', $map_tile_layer, 'string');
		$s->put('map_api_key', $map_api_key, 'string');

		# --BEHAVIOR-- adminEventHandlerSettingsSave
		$core->callBehavior("adminEventHandlerSettingsSave");
		dcPage::addSuccessNotice(__('Configuration has been updated.'));

		$core->blog->triggerBlog();

		$_SESSION['eh_tab'] = 'configuration';
		http::redirect($p_url.'&part=settings');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
if ($action == 'importeventdata') {
	include dirname(__FILE__).'/patch.eventdata.php';
}

# Display
include(dirname(__FILE__).'/../tpl/settings.tpl');
