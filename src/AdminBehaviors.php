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

use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Html;
use dcAuth;
use dcCore;
use ArrayObject;
use Exception;
use form;

class AdminBehaviors
{
    public static function adminDashboardIcons($name, $icons)
    {
        if ($name === 'eventHandler') {
            $icons['eventHandler'] = new ArrayObject([
                __('Event handler'),
                'plugin.php?p=eventHandler',
                'index.php?pf=eventHandler/icon.svg',
            ]);
        }
    }

    public static function adminDashboardFavs(Favorites $favorites)
    {
        $favorites->register('eventHandler', [
            'title' => 'Event handler',
            'url' => 'plugin.php?p=eventHandler',
            'small-icon' => [Page::getPF('eventHandler/icon.svg'), Page::getPF('eventHandler/icon-dark.svg')],
            'large-icon' => [Page::getPF('eventHandler/icon.svg'), Page::getPF('eventHandler/icon-dark.svg')],
            'permissions' => dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_USAGE, dcAuth::PERMISSION_CONTENT_ADMIN,
            ]),
        ]);
    }

    public static function adminPageHTTPHeaderCSP($csp)
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

    public static function adminPostHeaders()
    {
        return  self::adminCss() . Page::jsLoad(Page::getPF('eventHandler/js/post.js'));
    }

    public static function pluginsToolsHeadersV2()
    {
        return Page::jsPageTabs();
    }

    public static function adminPostsActions(ActionsPosts $ap)
    {
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN, dcAuth::PERMISSION_USAGE,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction([__('Events') => [__('Bind events') => ActionsEvents::BIND_EVENT_ACTION]], [ActionsEventsDefault::class, 'doBindUnbind']);
            $ap->addAction([__('Events') => [__('Unbind events') => ActionsEvents::UNBIND_POST_ACTION]], [ActionsEventsDefault::class, 'doBindUnbind']);
        }
    }

    public static function adminPostFormItems($main_items, $sidebar_items, $post = null)
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
            dcCore::app()->error->add($e->getMessage());
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
        $res .= '<p><a href="plugin.php?p=eventHandler&amp;part=events&amp;from_id=' . $post->post_id . '">' . __('Bind events') . '</a>';

        // Change post into event publish,contenadmin
        if (dcCore::app()->auth->check('publish,contentadmin', dcCore::app()->blog->id)) {
            $res .= '<p><a href="plugin.php?p=eventHandler';
            $res .= '&amp;part=event&amp;from_id=' . $post->post_id;
            $res .= '" title="' . __('Change this entry into an event') . '">' . __('Change into event') . '</a>';
        }

        $res .= '</p></div></div>';

        $sidebar_items['metas-box']['items']['eventhandler'] = $res;
    }

    // post.php
    // This delete relation between this post and ckecked related event (without javascript)
    public static function adminAfterPostSave($cur, $post_id)
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

                dcCore::app()->meta->delPostMeta($post_id, 'eventhandler', (string) $event_id);
            }
        } catch (Exception) {
            //dcCore::app()->error->add($e->getMessage());
        }
    }

    public static function adminBeforePostDelete($post_id)
    {
        if (!$post_id) {
            return;
        }

        try {
            dcCore::app()->meta->delPostMeta($post_id, 'eventhandler');
        } catch (Exception) {
            //dcCore::app()->error->add($e->getMessage());
        }
    }

    public static function adminCss()
    {
        $style = "style.css";
        if (dcCore::app()->auth->user_prefs->interface->darkmode == 1) {
            $style = "dark-style.css";
        }

        return '<link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/css/' . $style . '" />' . "\n";
    }
}
