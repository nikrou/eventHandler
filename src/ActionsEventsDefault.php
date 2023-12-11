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

use Dotclear\Core\Backend\Action\ActionsPostsDefault;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\eventHandler\Listing\ListingEventsMini;
use dcCore;
use Exception;
use form;

class ActionsEventsDefault
{
    public static function adminEventsActionsPage(ActionsEvents $ap)
    {
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_PUBLISH,
            dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Status') => [
                    __('Publish') => 'publish',
                    __('Unpublish') => 'unpublish',
                    __('Schedule') => 'schedule',
                    __('Mark as pending') => 'pending',
                ]],
                [ActionsPostsDefault::class, 'doChangePostStatus']
            );
        }

        $ap->addAction(
            [__('Mark') => [
                __('Mark as selected') => 'selected',
                __('Mark as unselected') => 'unselected',
            ]],
            [ActionsPostsDefault::class, 'doUpdateSelectedPost']
        );

        $ap->addAction([__('Change') => [__('Change category') => 'category']], [ActionsPostsDefault::class, 'doChangePostCategory']);

        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Change') => [
                    __('Change author') => 'author', ]],
                [ActionsPostsDefault::class, 'doChangePostAuthor']
            );
        }

        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcCore::app()->auth::PERMISSION_DELETE,
            dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Delete') => [
                    __('Delete') => 'delete', ]],
                [ActionsPostsDefault::class, 'doDeletePost']
            );

            $ap->addAction([__('Entries') => [__('Unbind related entries') => ActionsEvents::UNBIND_POST_ACTION]], [self::class, 'doBindUnbind']);
        }
    }

    public static function doBindUnBind(ActionsPosts $ap, $post)
    {
        $action = $ap->getAction();
        if (!in_array($action, [ActionsEvents::BIND_EVENT_ACTION, ActionsEvents::UNBIND_POST_ACTION])) {
            return;
        }

        $posts_ids = $ap->getIDs();
        if (empty($posts_ids)) {
            throw new Exception(__('No entry selected'));
        }
        $params['sql'] = ' AND P.post_id ' . dcCore::app()->con->in($posts_ids) . ' ';
        $posts = dcCore::app()->blog->getPosts($params);
        $events_id = [];

        if ($action === ActionsEvents::BIND_EVENT_ACTION) {
            if (isset($post['events'])) {
                foreach ($post['events'] as $k => $v) {
                    $events_id[$k] = (int) $v;
                }
                $params['sql'] = 'AND P.post_id ' . dcCore::app()->con->in($events_id) . ' ';
                $eventHandler = new EventHandler();
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
                Notices::addSuccessNotice(
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
                $ap->beginPage(Page::breadcrumb(
                    [
                        Html::escapeHTML(dcCore::app()->blog->name) => '',
                        __('Entries') => $ap->getRedirection(true),
                        __('Select events to link to entries') => '',
                    ]
                ), AdminBehaviors::adminCss());
                echo '<h3>' . __('Select events to link to entries') . '</h3>';
                $eventHandler = new EventHandler();

                $params = [];
                $params['no_content'] = true;
                $params['order'] = 'event_startdt DESC';
                $params['period'] = 'notfinished';

                // --BEHAVIOR-- adminEventHandlerMinilistCustomize
                dcCore::app()->callBehavior('adminEventHandlerMinilistCustomize', ['params' => $params]);

                $events = $eventHandler->getEvents($params);
                $counter = $eventHandler->getEvents($params, true);
                $list = new ListingEventsMini($events, $counter->f(0));

                echo $list->display(
                    1,
                    100,
                    '<form action="posts.php" method="post">' .

                    '%s' .

                    '<p>' .
                    $ap->getHiddenFields() .
                    implode(
                        '',
                        array_map(fn($id) => (new Hidden('entries[]', (string) $id))->render(), array_keys($ap->getIDs()))
                    ) .
                    dcCore::app()->formNonce() .
                    form::hidden(['action'], ActionsEvents::BIND_EVENT_ACTION) .
                    '<input type="submit" value="' . __('Save') . '" /></p>' .
                    '</form>'
                );
                $ap->endPage();
            }
        }
        // Unbind all posts from selected events
        if ($action === ActionsEvents::UNBIND_POST_ACTION) {
            if (!$posts->isEmpty()) { //called from posts.php
                while ($posts->fetch()) {
                    dcCore::app()->meta->delPostMeta($posts->post_id, 'eventhandler');
                }
                Notices::addSuccessNotice(sprintf(
                    __(
                        '%d post has been unbound from its events',
                        '%d posts have been unbound from their events',
                        count($posts_ids)
                    ),
                    count($posts_ids)
                ));
                $ap->redirect(false);
            } elseif (isset($post['entries'])) {
                $eventHandler = new EventHandler();
                foreach ($post['entries'] as $k => $v) {
                    $params = ['event_id' => $v];
                    $posts = $eventHandler->getPostsByEvent($params);
                    $event = $eventHandler->getEvents($params);
                    if ($posts->isEmpty()) {
                        Notices::addWarningNotice(sprintf(
                            __('Event #%d (%s) has no related post to be unbound from.'),
                            $v,
                            $event->post_title
                        ));
                        continue;
                    }
                    while ($posts->fetch()) {
                        dcCore::app()->meta->delPostMeta($posts->post_id, 'eventhandler', $v);
                    }
                    Notices::addSuccessNotice(sprintf(
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
            } else {
                throw new Exception("adminEventhandler::doBindUnBind Should never happen, $action action called with no post nor event specified.");
            }
        }
    }
}
