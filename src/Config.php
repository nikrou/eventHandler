<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of eventHandler, a plugin for Dotclear 2.
 *
 *  Copyright(c) 2014-2023 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
 *
 *  Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 *  contact@jcdenis.fr https://chez.jcdenis.fr/
 *
 *  Licensed under the GPL version 2.0 license.
 *  A copy of this license is available in LICENSE file or at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

declare(strict_types=1);

namespace Dotclear\Plugin\eventHandler;

use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use dcCore;
use Dotclear\App;
use Exception;
use form;

class Config extends Process
{
    private static string $default_tab = 'settings';

    public static function init(): bool
    {
        App::backend()->eventhandler_default_tab = self::$default_tab;

        App::backend()->eventhandler_active = (bool) My::settings()->active;
        App::backend()->eventhandler_public_posts_of_event_place = (string) My::settings()->public_posts_of_event_place;
        App::backend()->eventhandler_public_events_of_post_place = (string) My::settings()->public_events_of_post_place;
        App::backend()->eventhandler_public_events_list_sortby = (string) My::settings()->public_events_list_sortby;
        App::backend()->eventhandler_public_events_list_order = (string) My::settings()->public_events_list_order;
        App::backend()->eventhandler_public_hidden_categories = @unserialize(My::settings()->public_hidden_categories);
        if (!is_array(App::backend()->eventhandler_public_hidden_categories)) {
            App::backend()->eventhandler_public_hidden_categories = [];
        }
        try {
            App::backend()->eventhandler_categories = App::blog()->getCategories(['post_type' => 'post']);
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }
        App::backend()->eventhandler_public_map_zoom = abs((int) My::settings()->public_map_zoom);
        if (!App::backend()->eventhandler_public_map_zoom) {
            App::backend()->eventhandler_public_map_zoom = 9;
        }
        App::backend()->eventhandler_public_map_type = (string) My::settings()->public_map_type;
        App::backend()->eventhandler_public_extra_css = (string) My::settings()->public_extra_css;

        App::backend()->eventhandler_map_provider = My::settings()->map_provider;
        App::backend()->eventhandler_map_api_key = My::settings()->map_api_key;
        App::backend()->eventhandler_map_tile_layer = My::settings()->map_tile_layer;

        App::backend()->eventhandler_combo_place = [
            __('hide') => '',
            __('before content') => 'before',
            __('after content') => 'after',
        ];

        App::backend()->eventhandler_combo_list_sortby = [
            '' => null,
            __('Post title') => 'post:title',
            __('Post selected') => 'post:selected',
            __('Post author') => 'post:author',
            __('Post date') => 'post:date',
            __('Post id') => 'post:id',
            __('Event start date') => 'eventhandler:startdt',
            __('Event end date') => 'eventhandler:enddt',
        ];

        App::backend()->eventhandler_combo_list_order = [
            '' => null,
            __('Ascending') => 'asc',
            __('Descending') => 'desc',
        ];

        for ($i = 3;$i < 21;$i++) {
            $combo_map_zoom[$i] = $i;
        }
        App::backend()->eventhandler_combo_map_zoom = $combo_map_zoom;

        App::backend()->eventhandler_combo_map_type = [
            __('road map') => 'ROADMAP',
            __('satellite') => 'SATELLITE',
            __('hybrid') => 'HYBRID',
            __('terrain') => 'TERRAIN',
        ];

        App::backend()->eventhandler_combo_map_provider = [
            'GoogleMaps' => 'googlemaps',
            'OpenStreetMap' => 'osm',
        ];

        return self::status(My::checkContext(My::CONFIG));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (empty($_POST['save'])) {
            return true;
        }

        $already_active = App::backend()->eventhandler_active;

        if (!empty($_POST['save'])) {
            try {
                App::backend()->eventhandler_active = isset($_POST['active']);
                My::settings()->put('active', App::backend()->eventhandler_active, 'boolean');

                // change other settings only if they were in HTML page
                if ($already_active) {
                    if (isset($_POST['public_posts_of_event_place']) && in_array($_POST['public_posts_of_event_place'], App::backend()->eventhandler_combo_place)) {
                        App::backend()->eventhandler_public_posts_of_event_place = $_POST['public_posts_of_event_place'];
                    }

                    if (isset($_POST['public_events_of_post_place']) && in_array($_POST['public_events_of_post_place'], App::backend()->eventhandler_combo_place)) {
                        App::backend()->eventhandler_public_events_of_post_place = $_POST['public_events_of_post_place'];
                    }

                    if (isset($_POST['public_events_list_sortby']) && in_array($_POST['public_events_list_sortby'], App::backend()->eventhandler_combo_list_sortby)) {
                        App::backend()->eventhandler_public_events_list_sortby = $_POST['public_events_list_sortby'];
                    }

                    if (isset($_POST['public_events_list_order']) && in_array($_POST['public_events_list_order'], App::backend()->eventhandler_combo_list_order)) {
                        App::backend()->eventhandler_public_events_list_order = $_POST['public_events_list_order'];
                    }

                    if (isset($_POST['public_hidden_categories'])) {
                        App::backend()->eventhandler_public_hidden_categories = $_POST['public_hidden_categories'];
                    } else {
                        App::backend()->eventhandler_public_hidden_categories = [];
                    }

                    if (!empty($_POST['public_map_zoom'])) {
                        App::backend()->eventhandler_public_map_zoom = abs((int) $_POST['public_map_zoom']);
                    }

                    if (!App::backend()->eventhandler_public_map_zoom) {
                        App::backend()->eventhandler_public_map_zoom = 9;
                    }

                    if (!empty($_POST['public_map_type']) && in_array($_POST['public_map_type'], App::backend()->eventhandler_combo_map_type)) {
                        App::backend()->eventhandler_public_map_type = $_POST['public_map_type'];
                    }

                    if (!empty($_POST['public_extra_css'])) {
                        App::backend()->eventhandler_public_extra_css = $_POST['public_extra_css'];
                    }

                    if (!empty($_POST['map_provider']) && in_array($_POST['map_provider'], App::backend()->eventhandler_combo_map_provider)) {
                        App::backend()->eventhandler_map_provider = $_POST['map_provider'];
                    }

                    if (!empty($_POST['map_tile_layer'])) {
                        App::backend()->eventhandler_map_tile_layer = $_POST['map_tile_layer'];
                    }

                    if (!empty($_POST['map_api_key'])) {
                        App::backend()->eventhandler_map_api_key = $_POST['map_api_key'];
                    }

                    My::settings()->put('public_posts_of_event_place', App::backend()->eventhandler_public_posts_of_event_place, 'string');
                    My::settings()->put('public_events_of_post_place', App::backend()->eventhandler_public_events_of_post_place, 'string');
                    My::settings()->put('public_events_list_sortby', App::backend()->eventhandler_public_events_list_sortby, 'string');
                    My::settings()->put('public_events_list_order', App::backend()->eventhandler_public_events_list_order, 'string');
                    My::settings()->put('public_hidden_categories', serialize(App::backend()->eventhandler_public_hidden_categories), 'string');
                    My::settings()->put('public_map_zoom', App::backend()->eventhandler_public_map_zoom, 'integer');
                    My::settings()->put('public_map_type', App::backend()->eventhandler_public_map_type, 'string');
                    My::settings()->put('public_extra_css', App::backend()->eventhandler_public_extra_css, 'string');
                    My::settings()->put('map_provider', App::backend()->eventhandler_map_provider, 'string');
                    My::settings()->put('map_tile_layer', App::backend()->eventhandler_map_tile_layer, 'string');
                    My::settings()->put('map_api_key', App::backend()->eventhandler_map_api_key, 'string');

                    // --BEHAVIOR-- adminEventHandlerSettingsSave
                    App::behavior()->callBehavior("adminEventHandlerSettingsSave");
                }

                Notices::addSuccessNotice(__('Configuration has been updated.'));

                App::blog()->triggerBlog();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        echo '<div class="multi-part" id="settings" title="', __('Activation'), '">';
        if (dcCore::app()->auth->isSuperAdmin()) {
            echo '<div class="fieldset">';
            echo '<h3>', __('Activation'), '</h3>';
            echo '<p>';
            echo '<label>';
            echo form::checkbox(['active'], 1, App::backend()->eventhandler_active), ' ', __('Enable plugin');
            echo '</label>';
            echo '</p>';
            echo '</div>';
        }
        echo '</div>';

        if (App::backend()->eventhandler_active) {
            echo '<div class="multi-part" id="configuration" title="', __('Configuration'), '">';
            echo '<div class="fieldset">';
            echo '<h3>', __('Additionnal style sheet:'), '</h3>';
            echo '<p>';
            echo '<label class="classic">';
            echo form::textarea(['public_extra_css'], 164, 10, App::backend()->eventhandler_public_extra_css, 'maximal');
            echo '</label>';
            echo '</p>';
            echo '</div>';

            echo '<div class="fieldset" id="setting-event">';
            echo '<h3>', __('Events'), '</h3>';
            echo '<p>';
            echo '<label for="public_posts_of_event_place">', __('Show related entries on event:'), '</label>';
            echo form::combo(['public_posts_of_event_place'], App::backend()->eventhandler_combo_place, App::backend()->eventhandler_public_posts_of_event_place);
            echo '</p>';

            echo '<h3>', __('Entries'), '</h3>';
            echo '<p>';
            echo '<label for="public_events_of_post_place">', __('Show related events on entry:'), '</label>';
            echo form::combo(['public_events_of_post_place'], App::backend()->eventhandler_combo_place, App::backend()->eventhandler_public_events_of_post_place);
            echo '</p>';

            echo '<h3>', __('Events list ordering'), '</h3>';
            echo '<div class="one-box">';
            echo '<div class="box" style="margin-left:0">';
            echo '<p>';
            echo '<label for="public_events_list_sortby">', __('Default field'), '</label>';
            echo form::combo(['public_events_list_sortby'], App::backend()->eventhandler_combo_list_sortby, App::backend()->eventhandler_public_events_list_sortby);
            echo '</p>';
            echo '</div>';
            echo '<div class="box">';
            echo '<p>';
            echo '<label for="public_events_list_order">', __('Default order'), '</label>';
            echo form::combo(['public_events_list_order'], App::backend()->eventhandler_combo_list_order, App::backend()->eventhandler_public_events_list_order);
            echo '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            echo '<div class="fieldset">';
            echo '<h3>', __('Maps'), '</h3>';
            echo '<p>';
            echo '<label>', __('Default zoom on map:'), '</label>';
            echo form::combo(['public_map_zoom'], App::backend()->eventhandler_combo_map_zoom, App::backend()->eventhandler_public_map_zoom);
            echo '</p>';
            echo '<p>';
            echo '<label>', __('Default type of map:'), '</label>';
            echo form::combo(['public_map_type'], App::backend()->eventhandler_combo_map_type, App::backend()->eventhandler_public_map_type);
            echo '</p>';
            echo '<p>';
            echo '<label>', __('Map provider:'), '</label>';
            echo form::combo('map_provider', App::backend()->eventhandler_combo_map_provider, App::backend()->eventhandler_map_provider);
            echo '</p>';
            echo '<p class="map-api-key">';
            echo '<label>', __('API Key:'), '</label>';
            echo form::field(['map_api_key'], 100, 255, App::backend()->eventhandler_map_api_key);
            echo '</p>';
            echo '<p class="map-api-key form-note">';
            echo __('URL to create API Key:'), '<a href="https://console.developers.google.com/">https://console.developers.google.com/</a>';
            echo '</p>';
            echo '<p class="map-tile-layer">';
            echo '<label>', __('Map tile layer:'), '</label>';
            echo form::field(['map_tile_layer'], 100, 255, App::backend()->eventhandler_map_tile_layer);
            echo '</p>';
            echo '<p class="map-tile-layer form-note">';
            echo __('Default map tile layer for OpenStreetMap.');
            echo '</p>';
            echo '</div>';
            echo '</div>';

            echo '<div class="multi-part" id="categories" title="', __('Categories'), '">';
            if (count(App::backend()->eventhandler_categories) > 1) {
                echo '<h3>', __('Categories'), '</h3>';
                echo '<p class="info">', __('When an event has an hidden category, it will only display on its category page.'), '</p>';
                echo '<table class="clear">';
                echo '<tr>';
                echo '<th>', __('Hide'), '</th>';
                echo '<th>', __('Category'), '</th>';
                echo '<th>', __('Level'), '</th>';
                echo '<th>', __('Entries'), '</th>';
                echo '<th>', __('Events'), '</th>';
                echo '</tr>';
                while (App::backend()->eventhandler_categories->fetch()) {
                    $hidden = in_array(App::backend()->eventhandler_categories->cat_id, App::backend()->eventhandler_public_hidden_categories) || in_array(App::backend()->eventhandler_categories->cat_title, App::backend()->eventhandler_public_hidden_categories);
                    $nb_events = App::blog()->getPosts(['cat_id' => App::backend()->eventhandler_categories->cat_id, 'post_type' => 'eventhandler'], true)->f(0);
                    if ($nb_events) {
                        $nb_events = '<a href="' . My::manageUrl(['part' => 'events', 'cat_id' => App::backend()->eventhandler_categories->cat_id]) . '" ' .
                 'title="' . __('List of events related to this category') . '">' . $nb_events . '</a>';
                    }
                    $nb_posts = App::backend()->eventhandler_categories->nb_post;
                    if ($nb_posts) {
                        $nb_posts = '<a href="posts.php?cat_id=' . App::backend()->eventhandler_categories->cat_id . '" title="' . __('List of entries related to this category') . '">' . $nb_posts . '</a>';
                    }
                    echo '<tr class="line">';
                    echo '<td class="nowrap">', form::checkbox(['public_hidden_categories[]'], App::backend()->eventhandler_categories->cat_id, $hidden), '</td>';
                    echo '<td class="nowrap">';
                    echo '<a href="category.php?id=', App::backend()->eventhandler_categories->cat_id, '" title="', __('Edit this category'), '">';
                    echo Html::escapeHTML(App::backend()->eventhandler_categories->cat_title), '</a>';
                    echo '</td>';
                    echo '<td class="nowrap">', App::backend()->eventhandler_categories->level, '</td>';
                    echo '<td class="nowrap">', $nb_posts, '</td>';
                    echo '<td class="nowrap">', $nb_events, '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            echo '</div>';
        }

        /** Add a adminEventHandlerSettings behavior handler to add a custom tab to the eventhander settings page
         * and add a adminEventHandlerSettingsSave behavior handler to add save your custom settings.
         */
        App::behavior()->callBehavior("adminEventHandlerSettings");
    }
}
