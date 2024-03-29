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

use Dotclear\Core\Process;
use Dotclear\Database\Structure;
use Dotclear\App;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        $new_version = App::plugins()->moduleInfo(My::id(), 'version');
        $old_version = App::version()->getVersion(My::id());

        if (version_compare((string) $old_version, $new_version, '>=')) {
            return true;
        }

        try {
            $t = new Structure(App::con(), App::con()->prefix());
            $t->eventhandler
              ->post_id('bigint', 0, false)
              ->event_startdt('timestamp', 0, false, 'now()')
              ->event_enddt('timestamp', 0, false, 'now()')
              ->event_address('text', null, true, null)
              ->event_latitude('varchar', 25, true, null)
              ->event_longitude('varchar', 25, true, null)
              ->event_zoom('integer', 0, true, 0)

              ->index('idx_event_post_id', 'btree', 'post_id')
              ->index('idx_event_event_start', 'btree', 'event_startdt')
              ->index('idx_event_event_end', 'btree', 'event_enddt')
              ->reference('fk_event_post', 'post_id', 'post', 'post_id', 'cascade', 'cascade');

            // Schema installation
            $ti = new Structure(App::con(), App::con()->prefix());
            $changes = $ti->synchronize($t);

            // Settings options
            App::blog()->settings()->addWorkspace('eventHandler');

            $extra_css = file_get_contents(dirname(__DIR__) . '/css/default-eventhandler.css');

            My::settings()->put('active', false, 'boolean', 'Enabled eventHandler extension', false, true);
            My::settings()->put('public_events_of_post_place', 'after', 'string', 'Display related events on entries', false, true);
            My::settings()->put('public_posts_of_event_place', 'after', 'string', 'Display related posts on events', false, true);
            My::settings()->put('public_events_list_sortby', '', 'string', 'Default field for ordering events list', false, true);
            My::settings()->put('public_events_list_order', '', 'string', 'Default order (asc or desc) for events list', false, true);
            My::settings()->put('public_hidden_categories', '', 'string', 'List of categories to hide from post content and widgets', false, true);
            My::settings()->put('public_map_zoom', 9, 'integer', 'Default zoom of map', false, true);
            My::settings()->put('public_map_type', 'ROADMAP', 'string', 'Default type of map', false, true);
            My::settings()->put('public_extra_css', $extra_css, 'string', 'Custom CSS', false, true);
            My::settings()->put('map_provider', 'googlemaps', 'string', 'Map provider', false, true);
            My::settings()->put('map_tile_layer', 'http://{s}.tile.osm.org/{z}/{x}/{y}.png', 'string', 'Tile Layer for OSM', false, true);
            My::settings()->put('map_api_key', '', 'string', 'Map API Key', false, true);

            // Set version
            App::version()->setVersion('eventHandler', $new_version);

            return true;
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }
}
