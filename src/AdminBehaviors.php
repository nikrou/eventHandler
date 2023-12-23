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

use Dotclear\Core\Auth;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Html;
use ArrayObject;
use Dotclear\App;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;
use Exception;
use form;

class AdminBehaviors
{
    public static function adminDashboardFavs(Favorites $favorites): void
    {
        $favorites->register(My::id(), [
            'title' => My::name(),
            'url' => My::manageUrl([], '&'),
            'small-icon' => My::icons(),
            'large-icon' => My::icons(),
            'permissions' => App::auth()->makePermissions([
                Auth::PERMISSION_USAGE, Auth::PERMISSION_CONTENT_ADMIN,
            ]),
            'dashboard_cb' => function (ArrayObject $icon) {
                $params = [];
                $params['post_type'] = EventHandler::POST_TYPE;
                $events_count = (int) App::blog()->getPosts($params, true)->f(0);
                if ($events_count > 0) {
                    $icon['title'] = sprintf($events_count > 1 ? __('%d events') : __('one event'), $events_count);
                }
            },
        ]);
    }

    /**
     * @param ArrayObject<string, mixed> $csp
     */
    public static function adminPageHTTPHeaderCSP(ArrayObject $csp): void
    {
        if (My::settings()->map_provider === 'googlemaps') {
            $host_map_provider = 'csi.gstatic.com maps.google.com maps.googleapis.com';
            if (isset($csp['img-src'])) {
                $csp['img-src'] .= ' csi.gstatic.com';
            } else {
                $csp['img-src'] = 'csi.gstatic.com';
            }

            if (isset($csp['img-src'])) {
                $csp['img-src'] .= ' *.google.com *.gstatic.com *.googleapis.com';
            } else {
                $csp['img-src'] = '*.google.com *.gstatic.com *.googleapis.com';
            }

            if (isset($csp['script-src'])) {
                $csp['script-src'] .= ' ' . $host_map_provider;
            } else {
                $csp['script-src'] = $host_map_provider;
            }
        } else { // osm
            $host_map_provider = 'nominatim.openstreetmap.org';

            if (isset($csp['img-src'])) {
                $csp['img-src'] .= ' tile.openstreetmap.org';
            } else {
                $csp['img-src'] = 'tile.openstreetmap.org';
            }
        }

        if (isset($csp['connect-src'])) {
            $csp['connect-src'] .= ' ' . $host_map_provider;
        } else {
            $csp['connect-src'] = $csp['default-src'] . ' ' . $host_map_provider;
        }
    }

    public static function adminPostHeaders(): string
    {
        return self::adminCss() . Page::jsLoad(Page::getPF('eventHandler/js/post.js'));
    }

    public static function pluginsToolsHeadersV2(): string
    {
        return Page::jsPageTabs();
    }

    public static function adminPostsActions(ActionsPosts $ap): void
    {
        if (App::auth()->check(App::auth()->makePermissions([
            Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_USAGE,
        ]), App::blog()->id())) {
            $ap->addAction([__('Events') => [__('Bind events') => ActionsEvents::BIND_EVENT_ACTION]], ActionsEventsDefault::doBindUnbind(...));
            $ap->addAction([__('Events') => [__('Unbind events') => ActionsEvents::UNBIND_POST_ACTION]], ActionsEventsDefault::doBindUnbind(...));
        }
    }

    /**
     * @param ArrayObject<string, mixed> $main_items
     * @param ArrayObject<string, mixed> $sidebar_items
     */
    public static function adminPostFormItems(ArrayObject $main_items, ArrayObject $sidebar_items, MetaRecord $post = null): void
    {
        if ($post === null) {
            return;
        }

        // Get linked events
        $events = null;
        $params = [];
        $params['post_id'] = $post->post_id;
        $params['no_content'] = true;

        try {
            $eventHandler = new EventHandler();
            $events = $eventHandler->getEventsByPost($params);
            if ($events->isEmpty()) {
                $events = null;
            }
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        $res = '<div>';
        $res .= '<h5 id="eventhandler-form-title">' . __('Events') . '</h5>';
        $res .= '<div id="eventhandler-form-content">';

        // Related events
        if ($events) {
            $res .= '<ul class="event-list">';

            while ($events->fetch()) {
                $res .= '<li class="event-node event-node-' . $events->getPeriod() . '">';
                $res .= '<label title="' . __('Check to unbind') . '" class="classic">';
                $res .= form::checkbox(['eventhandler_events[]'], $events->post_id, '', 'event-node-value');
                $res .= Html::escapeHTML($events->post_title) . '</label></li>';
            }

            $res .= '</ul>';
        }

        // Bind a event to this post
        $res .= '<p><a href="' . My::manageUrl(['part' => 'events', 'from_id' => $post->post_id]) . '">' . __('Bind events') . '</a>';

        if (App::auth()->check(App::auth()->makePermissions([Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_PUBLISH]), App::blog()->id())) {
            $res .= '<p><a href="' . My::manageUrl(['part' => 'event', 'from_id' => $post->post_id]);
            $res .= '" title="' . __('Change this entry into an event') . '">' . __('Change into event') . '</a>';
        }

        $res .= '</p></div></div>';

        $sidebar_items['metas-box']['items']['eventhandler'] = $res;
    }

    // This delete relation between this post and ckecked related event (without javascript)
    public static function adminAfterPostSave(Cursor $cur, int $post_id): void
    {
        if (!$post_id) {
            return;
        }

        if (empty($_POST['eventhandler_events']) || !is_array($_POST['eventhandler_events'])) {
            return;
        }

        try {
            foreach ($_POST['eventhandler_events'] as $event_id) {
                $event_id = abs((int) $event_id);
                if (!$event_id) {
                    continue;
                }

                App::meta()->delPostMeta($post_id, 'eventhandler', (string) $event_id);
            }
        } catch (Exception) {
            //App::error()->add($e->getMessage());
        }
    }

    public static function adminBeforePostDelete(int $post_id): void
    {
        if (!$post_id) {
            return;
        }

        try {
            App::meta()->delPostMeta($post_id, 'eventhandler');
        } catch (Exception) {
            //App::error()->add($e->getMessage());
        }
    }

    public static function adminCss(): string
    {
        $style = "style.css";
        if (App::userPreferences()->interface->darkmode == 1) {
            $style = "dark-style.css";
        }

        return '<link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/css/' . $style . '" />' . "\n";
    }
}
