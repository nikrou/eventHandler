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

class adminEventHandler
{
    public const BIND_EVENT_ACTION = 'eventhandler_bind_event';
    public const UNBIND_POST_ACTION = 'eventhandler_unbind_post';

    // Dashboard icon
    public static function adminDashboardIcons($name, $icons)
    {
        if ($name === 'eventHandler') {
            $icons['eventHandler'] = new ArrayObject([
                __('Event handler'),
                'plugin.php?p=eventHandler',
                'index.php?pf=eventHandler/icon.svg'
            ]);
        }
    }

    // Dashboard fav icon
    public static function adminDashboardFavs(dcFavorites $favorites)
    {
        $favorites->register('eventHandler', [
            'title' => 'Event handler',
            'url' => 'plugin.php?p=eventHandler',
            'small-icon' => [dcPage::getPF('eventHandler/icon.svg'), dcPage::getPF('eventHandler/icon-dark.svg')],
            'large-icon' => [dcPage::getPF('eventHandler/icon.svg'), dcPage::getPF('eventHandler/icon-dark.svg')],
            'permissions' => dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_USAGE, dcAuth::PERMISSION_CONTENT_ADMIN
            ])
        ]);
    }

    public static function adminPageHTTPHeaderCSP($csp)
    {
        if (dcCore::app()->blog->settings->eventHandler->map_provider === 'googlemaps') {
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

    // post.php
    // Headers, jQuery features to remove events from a post
    public static function adminPostHeaders()
    {
        return
        self::adminCss() .
        dcPage::jsLoad(dcPage::getPF('eventHandler/js/post.js'));
    }

    // posts.php
    // Combo of actions on multiple posts
    public static function adminPostsActions(dcPostsActions $ap)
    {
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN, dcAuth::PERMISSION_USAGE
        ]), dcCore::app()->blog->id)) {
            $ap->addAction([__('Events') => [__('Bind events') => self::BIND_EVENT_ACTION]], [adminEventHandler::class, 'doBindUnbind']);
            $ap->addAction([__('Events') => [__('Unbind events') => self::UNBIND_POST_ACTION]], [adminEventHandler::class, 'doBindUnbind']);
        }
    }

    public static function adminPostsActionsPage(dcPostsActionsPage $ap)
    {
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN, dcAuth::PERMISSION_USAGE
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Events') => [
                    __('Bind events') => self::BIND_EVENT_ACTION,
                    __('Unbind events') => self::UNBIND_POST_ACTION
                ]],
                [adminEventHandler::class, 'doBindUnbind']
            );
        }
    }

    public static function doBindUnBind(dcPostsActions $ap, $post)
    {
        $action = $ap->getAction();
        if (!in_array($action, [self::BIND_EVENT_ACTION, self::UNBIND_POST_ACTION])) {
            return;
        }

        $posts_ids = $ap->getIDs();
        if (empty($posts_ids)) {
            throw new Exception(__('No entry selected'));
        }
        $params['sql'] = ' AND P.post_id ' . dcCore::app()->con->in($posts_ids) . ' ';
        $posts = dcCore::app()->blog->getPosts($params);
        $events_id = [];

        if ($action === self::BIND_EVENT_ACTION) {
            if (isset($post['events'])) {
                foreach ($post['events'] as $k => $v) {
                    $events_id[$k] = (integer) $v;
                }
                $params['sql'] = 'AND P.post_id ' . dcCore::app()->con->in($events_id) . ' ';
                $eventHandler = new eventHandler();
                $events = $eventHandler->getEvents($params);
                if ($events->isEmpty()) {
                    throw new Exception(__('No such event'));
                }
                $meta_ids = [];
                while ($events->fetch()) {
                    $meta_ids[] = $events->post_id;
                }

                while ($posts->fetch()) {
                    foreach ($meta_ids as $meta_id) {
                        dcCore::app()->meta->delPostMeta($posts->post_id, 'eventhandler', $meta_id);
                        dcCore::app()->meta->setPostMeta($posts->post_id, 'eventhandler', $meta_id);
                    }
                }
                dcPage::addSuccessNotice(
                    sprintf(
                        __(
                            '%d entry has been bound %s',
                            '%d entries have been bound %s',
                            count($posts_ids)
                        ),
                        count($posts_ids),
                        __('to the selected event', 'to the selected events', $events->count())
                    )
                );
                $ap->redirect(true);
            } else {
                $ap->beginPage(dcPage::breadcrumb(
                    [
                        html::escapeHTML(dcCore::app()->blog->name) => '',
                        __('Entries') => $ap->getRedirection(true),
                        __('Select events to link to entries') => '',
                    ]
                ), self::adminCss());
                echo '<h3>' . __('Select events to link to entries') . '</h3>';
                $eventHandler = new eventHandler();

                $params = [];
                $params['no_content'] = true;
                $params['order'] = 'event_startdt DESC';
                $params['period'] = 'notfinished';

                // --BEHAVIOR-- adminEventHandlerMinilistCustomize
                dcCore::app()->callBehavior('adminEventHandlerMinilistCustomize', ['params' => $params]);

                $events = $eventHandler->getEvents($params);
                $counter = $eventHandler->getEvents($params, true);
                $list = new adminEventHandlerMiniList(dcCore::app(), $events, $counter->f(0));

                echo $list->display(
                    1,
                    100,
                    '<form action="posts.php" method="post">' .

                    '%s' .

                    '<p>' .
                    $ap->getHiddenFields() .
                    $ap->getIDsHidden() .
                    dcCore::app()->formNonce() .
                    form::hidden(['action'], self::BIND_EVENT_ACTION) .
                    '<input type="submit" value="' . __('Save') . '" /></p>' .
                    '</form>'
                );
                $ap->endPage();
            }
        }
        // Unbind all posts from selected events
        if ($action === self::UNBIND_POST_ACTION) {
            if (!$posts->isEmpty()) { //called from posts.php
                while ($posts->fetch()) {
                    dcCore::app()->meta->delPostMeta($posts->post_id, 'eventhandler');
                }
                dcPage::addSuccessNotice(sprintf(
                    __(
                        '%d post has been unbound from its events',
                        '%d posts have been unbound from their events',
                        count($posts_ids)
                    ),
                    count($posts_ids)
                ));
            } elseif (isset($post['entries'])) {
                $eventHandler = new eventHandler();
                foreach ($post['entries'] as $k => $v) {
                    $params = ['event_id' => $v];
                    $posts = $eventHandler->getPostsByEvent($params);
                    $event = $eventHandler->getEvents($params);
                    if ($posts->isEmpty()) {
                        dcPage::addWarningNotice(sprintf(
                            __('Event #%d (%s) has no related post to be unbound from.'),
                            $v,
                            $event->post_title
                        ));
                        continue;
                    }
                    while ($posts->fetch()) {
                        dcCore::app()->meta->delPostMeta($posts->post_id, 'eventhandler', $v);
                    }
                    dcPage::addSuccessNotice(sprintf(
                        __(
                            'Event #%d (%s) unbound from %d related post',
                            'Event #%d (%s) unbound from %d related posts',
                            $posts->count()
                        ),
                        $v,
                        $event->post_title,
                        $posts->count()
                    ));
                }
                $ap->redirect(false);
            } else {
                throw new Exception("adminEventhandler::doBindUnBind Should never happen, $action action called with no post nor event specified.");
            }
            $ap->redirect(true);
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
            $eventHandler = new eventHandler();
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
                $res .= html::escapeHTML($events->post_title) . '</label></li>';
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
                $event_id = abs((integer) $event_id);
                if (!$event_id) {
                    continue;
                }

                dcCore::app()->meta->delPostMeta($post_id, 'eventhandler', $event_id);
            }
        } catch (Exception $e) {
            //dcCore::app()->error->add($e->getMessage());
        }
    }

    // post.php
    // This delete relation between this post and all there events
    public static function adminBeforePostDelete($post_id)
    {
        if (!$post_id) {
            return;
        }

        try {
            dcCore::app()->meta->delPostMeta($post_id, 'eventhandler');
        } catch (Exception $e) {
            //dcCore::app()->error->add($e->getMessage());
        }
    }

        // Returns the admin css according to the darkmode setting
        public static function adminCss()
        {
            $style = "style.css";
            if (dcCore::app()->auth->user_prefs->interface->darkmode == 1) {
                $style = "dark-style.css";
            }

            return '<link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/css/' . $style . '" />' . "\n";
        }
}
