<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of eventHandler, a plugin for Dotclear 2.
 *
 *  Copyright(c) 2014-2023 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
 *
 *  Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 *  contact@jcdenis.fr http://jcd.lv
 *
 *  Licensed under the GPL version 2.0 license.
 *  A copy of this license is available in LICENSE file or at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'settings') {
    return;
}

$settings = dcCore::app()->blog->settings->eventHandler;

// Read settings
$active = (boolean) $settings->active;
$public_posts_of_event_place = (string) $settings->public_posts_of_event_place;
$public_events_of_post_place = (string) $settings->public_events_of_post_place;
$public_events_list_sortby = (string) $settings->public_events_list_sortby;
$public_events_list_order = (string) $settings->public_events_list_order;
$public_hidden_categories = @unserialize($settings->public_hidden_categories);
if (!is_array($public_hidden_categories)) {
    $public_hidden_categories = [];
}
$public_map_zoom = abs((integer) $settings->public_map_zoom);
if (!$public_map_zoom) {
    $public_map_zoom = 9;
}
$public_map_type = (string) $settings->public_map_type;
$public_extra_css = (string) $settings->public_extra_css;

$map_provider = $settings->map_provider;
$map_api_key = $settings->map_api_key;
$map_tile_layer = $settings->map_tile_layer;

// Combos
$combo_place = [
    __('hide') => '',
    __('before content') => 'before',
    __('after content') => 'after'
];


$combo_list_sortby = [
    '' => null,
    __('Post title') => 'post:title',
    __('Post selected') => 'post:selected',
    __('Post author') => 'post:author',
    __('Post date') => 'post:date',
    __('Post id') => 'post:id',
    __('Event start date') => 'eventhandler:startdt',
    __('Event end date') => 'eventhandler:enddt',
];
$combo_list_order = [
    '' => null,
    __('Ascending') => 'asc',
    __('Descending') => 'desc'
];

for ($i = 3;$i < 21;$i++) {
    $combo_map_zoom[$i] = $i;
}
$combo_map_type = [
    __('road map') => 'ROADMAP',
    __('satellite') => 'SATELLITE',
    __('hybrid') => 'HYBRID',
    __('terrain') => 'TERRAIN'
];


// Categories combo
$categories = null;
$combo_categories = ['-' => ''];
try {
    $categories = dcCore::app()->blog->getCategories(['post_type' => 'post']);
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}
while ($categories->fetch()) {
    $combo_categories[str_repeat('&nbsp;&nbsp;', $categories->level - 1) . '&bull; ' . html::escapeHTML($categories->cat_title)] = $categories->cat_id;
}

// Map providers combo
$combo_map_provider = [
    'GoogleMaps' => 'googlemaps',
    'OpenStreetMap' => 'osm'
];

/** @phpstan-ignore-next-line ; define in index.php */
if ($action === 'savesettings') {
    $default_tab = 'configuration';
    try {
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
            $public_hidden_categories = [];
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

        $settings->put('active', $active, 'boolean');
        $settings->put('public_posts_of_event_place', $public_posts_of_event_place, 'string');
        $settings->put('public_events_of_post_place', $public_events_of_post_place, 'string');
        $settings->put('public_events_list_sortby', $public_events_list_sortby, 'string');
        $settings->put('public_events_list_order', $public_events_list_order, 'string');
        $settings->put('public_hidden_categories', serialize($public_hidden_categories), 'string');
        $settings->put('public_map_zoom', $public_map_zoom, 'integer');
        $settings->put('public_map_type', $public_map_type, 'string');
        $settings->put('public_extra_css', $public_extra_css, 'string');
        $settings->put('map_provider', $map_provider, 'string');
        $settings->put('map_tile_layer', $map_tile_layer, 'string');
        $settings->put('map_api_key', $map_api_key, 'string');

        // --BEHAVIOR-- adminEventHandlerSettingsSave
        dcCore::app()->callBehavior("adminEventHandlerSettingsSave");
        dcPage::addSuccessNotice(__('Configuration has been updated.'));

        dcCore::app()->blog->triggerBlog();

        $_SESSION['eh_tab'] = 'configuration';
        http::redirect(dcCore::app()->admin->getPageURL() . '&part=settings');
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

/** @phpstan-ignore-next-line ; define in index.php */
if ($action === 'importeventdata') {
    include __DIR__ . '/patch.eventdata.php';
}

// Display
include(__DIR__ . '/../tpl/settings.tpl');
