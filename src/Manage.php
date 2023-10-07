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

use dcCore;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Process;

class Manage extends Process
{
    private static $active_part = 'settings';

    public static function init(): bool
    {
        if (My::checkContext(My::MANAGE)) {
            $default_part = My::settings()->active ? 'events' : 'settings';
            self::$active_part = $_REQUEST['part'] ?? $default_part;
            dcCore::app()->admin->eventhandler_default_tab = self::$active_part;

            if (self::$active_part === 'events') {
                self::status(ManageEvents::init());
            } elseif (self::$active_part === 'event') {
                self::status(ManageEvent::init());
            } else {
                self::status(true);
            }
        }

        return self::status();
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (self::$active_part === 'events') {
            self::status(ManageEvents::process());
        } elseif (self::$active_part === 'event') {
            self::status(ManageEvent::process());
        }

        dcCore::app()->admin->eventhandler_combo_place = [
            __('hide') => '',
            __('before content') => 'before',
            __('after content') => 'after'
        ];

        dcCore::app()->admin->eventhandler_combo_list_sortby = [
            '' => null,
            __('Post title') => 'post:title',
            __('Post selected') => 'post:selected',
            __('Post author') => 'post:author',
            __('Post date') => 'post:date',
            __('Post id') => 'post:id',
            __('Event start date') => 'eventhandler:startdt',
            __('Event end date') => 'eventhandler:enddt',
        ];

        dcCore::app()->admin->eventhandler_combo_list_order = [
            '' => null,
            __('Ascending') => 'asc',
            __('Descending') => 'desc'
        ];

        for ($i = 3;$i < 21;$i++) {
            $combo_map_zoom[$i] = $i;
        }
        dcCore::app()->admin->eventhandler_combo_map_zoom = $combo_map_zoom;

        dcCore::app()->admin->eventhandler_combo_map_type = [
            __('road map') => 'ROADMAP',
            __('satellite') => 'SATELLITE',
            __('hybrid') => 'HYBRID',
            __('terrain') => 'TERRAIN'
        ];

        try {
            dcCore::app()->admin->eventhandler_categories = dcCore::app()->blog->getCategories(['post_type' => 'post']);
        } catch (\Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        dcCore::app()->admin->eventhandler_combo_map_provider = [
            'GoogleMaps' => 'googlemaps',
            'OpenStreetMap' => 'osm'
        ];

        dcCore::app()->admin->eventhandler_active = (boolean) My::settings()->active;
        dcCore::app()->admin->eventhandler_public_posts_of_event_place = (string) My::settings()->public_posts_of_event_place;
        dcCore::app()->admin->eventhandler_public_events_of_post_place = (string) My::settings()->public_events_of_post_place;
        dcCore::app()->admin->eventhandler_public_events_list_sortby = (string) My::settings()->public_events_list_sortby;
        dcCore::app()->admin->eventhandler_public_events_list_order = (string) My::settings()->public_events_list_order;
        dcCore::app()->admin->eventhandler_public_hidden_categories = @unserialize(My::settings()->public_hidden_categories);
        if (!is_array(dcCore::app()->admin->eventhandler_public_hidden_categories)) {
            dcCore::app()->admin->eventhandler_public_hidden_categories = [];
        }
        dcCore::app()->admin->eventhandler_public_map_zoom = abs((integer) My::settings()->public_map_zoom);
        if (!dcCore::app()->admin->eventhandler_public_map_zoom) {
            dcCore::app()->admin->eventhandler_public_map_zoom = 9;
        }
        dcCore::app()->admin->eventhandler_public_map_type = (string) My::settings()->public_map_type;
        dcCore::app()->admin->eventhandler_public_extra_css = (string) My::settings()->public_extra_css;

        dcCore::app()->admin->eventhandler_map_provider = My::settings()->map_provider;
        dcCore::app()->admin->eventhandler_map_api_key = My::settings()->map_api_key;
        dcCore::app()->admin->eventhandler_map_tile_layer = My::settings()->map_tile_layer;

        if (!empty($_POST['action']) && $_POST['action'] === 'savesettings') {
            $already_active = dcCore::app()->admin->eventhandler_active;

            try {
                dcCore::app()->admin->eventhandler_active = isset($_POST['active']);
                My::settings()->put('active', dcCore::app()->admin->eventhandler_active, 'boolean');

                // change other settings only if they were in HTML page
                if ($already_active) {
                    if (isset($_POST['public_posts_of_event_place']) && in_array($_POST['public_posts_of_event_place'], dcCore::app()->admin->eventhandler_combo_place)) {
                        dcCore::app()->admin->eventhandler_public_posts_of_event_place = $_POST['public_posts_of_event_place'];
                    }

                    if (isset($_POST['public_events_of_post_place']) && in_array($_POST['public_posts_of_post_place'], dcCore::app()->admin->eventhandler_combo_place)) {
                        dcCore::app()->admin->eventhandler_public_events_of_post_place = $_POST['public_events_of_post_place'];
                    }

                    if (isset($_POST['public_events_list_sortby']) && in_array($_POST['public_events_list_sortby'], dcCore::app()->admin->eventhandler_combo_list_sortby)) {
                        dcCore::app()->admin->eventhandler_public_events_list_sortby = $_POST['public_events_list_sortby'];
                    }

                    if (isset($_POST['public_events_list_order']) && in_array($_POST['public_events_list_order'], dcCore::app()->admin->eventhandler_combo_list_order)) {
                        dcCore::app()->admin->eventhandler_public_events_list_order = $_POST['public_events_list_order'];
                    }

                    if (isset($_POST['public_hidden_categories'])) {
                        dcCore::app()->admin->eventhandler_public_hidden_categories = $_POST['public_hidden_categories'];
                    } else {
                        dcCore::app()->admin->eventhandler_public_hidden_categories = [];
                    }

                    if (!empty($_POST['public_map_zoom'])) {
                        dcCore::app()->admin->eventhandler_public_map_zoom = abs((integer) $_POST['public_map_zoom']);
                    }

                    if (!dcCore::app()->admin->eventhandler_public_map_zoom) {
                        dcCore::app()->admin->eventhandler_public_map_zoom = 9;
                    }

                    if (!empty($_POST['public_map_type']) && in_array($_POST['public_map_type'], dcCore::app()->admin->eventhandler_combo_map_type)) {
                        dcCore::app()->admin->eventhandler_public_map_type = $_POST['public_map_type'];
                    }

                    if (!empty($_POST['public_extra_css'])) {
                        dcCore::app()->admin->eventhandler_public_extra_css = $_POST['public_extra_css'];
                    }

                    if (!empty($_POST['map_provider']) && in_array($_POST['map_provider'], dcCore::app()->admin->eventhandler_combo_map_provider)) {
                        dcCore::app()->admin->eventhandler_map_provider = $_POST['map_provider'];
                    }

                    if (!empty($_POST['map_tile_layer'])) {
                        dcCore::app()->admin->eventhandler_map_tile_layer = $_POST['map_tile_layer'];
                    }

                    if (!empty($_POST['map_api_key'])) {
                        dcCore::app()->admin->eventhandler_map_api_key = $_POST['map_api_key'];
                    }

                    My::settings()->put('public_posts_of_event_place', dcCore::app()->admin->eventhandler_public_posts_of_event_place, 'string');
                    My::settings()->put('public_events_of_post_place', dcCore::app()->admin->eventhandler_public_events_of_post_place, 'string');
                    My::settings()->put('public_events_list_sortby', dcCore::app()->admin->eventhandler_public_events_list_sortby, 'string');
                    My::settings()->put('public_events_list_order', dcCore::app()->admin->eventhandler_public_events_list_order, 'string');
                    My::settings()->put('public_hidden_categories', serialize(dcCore::app()->admin->eventhandler_public_hidden_categories), 'string');
                    My::settings()->put('public_map_zoom', dcCore::app()->admin->eventhandler_public_map_zoom, 'integer');
                    My::settings()->put('public_map_type', dcCore::app()->admin->eventhandler_public_map_type, 'string');
                    My::settings()->put('public_extra_css', dcCore::app()->admin->eventhandler_public_extra_css, 'string');
                    My::settings()->put('map_provider', dcCore::app()->admin->eventhandler_map_provider, 'string');
                    My::settings()->put('map_tile_layer', dcCore::app()->admin->eventhandler_map_tile_layer, 'string');
                    My::settings()->put('map_api_key', dcCore::app()->admin->eventhandler_map_api_key, 'string');

                    // --BEHAVIOR-- adminEventHandlerSettingsSave
                    dcCore::app()->callBehavior("adminEventHandlerSettingsSave");
                }

                Notices::addSuccessNotice(__('Configuration has been updated.'));
                dcCore::app()->blog->triggerBlog();

                My::redirect(['part' => 'settings'], '#configuration');
            } catch (\Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        if (self::$active_part === 'events') {
            ManageEvents::render();
        } elseif (self::$active_part === 'event') {
            ManageEvent::render();
        } else {
            include(__DIR__ . '/../tpl/settings.tpl');
        }
    }
}
