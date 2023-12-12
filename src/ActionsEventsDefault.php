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

use ArrayObject;
use Dotclear\Core\Backend\Action\ActionsPostsDefault;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Html;
use Dotclear\App;
use Dotclear\Plugin\eventHandler\Listing\ListingEvents;
use Exception;
use form;

class ActionsEventsDefault
{
    public static function adminEventsActionsPage(ActionsEvents $ap): void
    {
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_PUBLISH,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
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

        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_ADMIN,
        ]), App::blog()->id())) {
            $ap->addAction(
                [__('Change') => [
                    __('Change author') => 'author', ]],
                [ActionsPostsDefault::class, 'doChangePostAuthor']
            );
        }

        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_DELETE,
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $ap->addAction(
                [__('Delete') => [
                    __('Delete') => 'delete', ]],
                [ActionsPostsDefault::class, 'doDeletePost']
            );

            $ap->addAction([__('Entries') => [__('Unbind related entries') => ActionsEvents::UNBIND_POST_ACTION]], self::doBindUnbind(...));
        }
    }

    /**
    * @param ArrayObject<string, mixed> $post
    */
    public static function doBindUnBind(ActionsPosts $ap, ArrayObject $post): void
    {
        $action = $ap->getAction();
        if (!in_array($action, [ActionsEvents::BIND_EVENT_ACTION, ActionsEvents::UNBIND_POST_ACTION])) {
            return;
        }

        if ($action === ActionsEvents::BIND_EVENT_ACTION) {
            $action_redirect = true;

            $numberOfEntries = 0;
            if (isset($post['from_id'])) {
                $params = ['post_id' => (int) $post['from_id']];
                $numberOfEntries = 1;
                $action_redirect = false;
            } else {
                $posts_ids = $ap->getIDs();
                if (empty($posts_ids)) {
                    throw new Exception(__('No entry selected'));
                }
                $numberOfEntries = count($posts_ids);
                $params = ['sql' => ' AND P.post_id ' . App::con()->in($posts_ids) . ' '];
            }
            $posts = App::blog()->getPosts($params);

            if (isset($post['events'])) {
                $params = ['sql' => 'AND P.post_id ' . App::con()->in(array_values($post['events'])) . ' '];
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
                        App::meta()->delPostMeta($posts->post_id, 'eventhandler', $meta_id);
                        App::meta()->setPostMeta($posts->post_id, 'eventhandler', $meta_id);
                    }
                }

                Notices::addSuccessNotice(
                    sprintf(
                        __(
                            '%d entry has been bound %s',
                            '%d entries have been bound %s',
                            $numberOfEntries
                        ),
                        $numberOfEntries,
                        sprintf(
                            __('to the selected event', 'to the %d selected events', $events->count()),
                            $$events->count()
                        )
                    )
                );

                if ($action_redirect) {
                    $ap->redirect(false);
                } else {
                    App::backend()->url()->redirect('admin.post', ['id' => $post['from_id']]);
                }
            } else {
                $ap->beginPage(Page::breadcrumb(
                    [
                        Html::escapeHTML(App::blog()->name()) => '',
                        __('Entries') => $ap->getRedirection(true),
                        __('Select events to link to entries') => '',
                    ]
                ), AdminBehaviors::adminCss());
                echo '<h3>' . __('Select events to link to entries') . '</h3>';

                // --BEHAVIOR-- adminEventHandlerListCustomize
                App::behavior()->callBehavior('adminEventHandlerListCustomize', ['params' => $params]);

                $params = [];
                $params['no_content'] = true;
                $params['order'] = 'event_startdt DESC';
                $params['period'] = 'notfinished';

                $eventHandler = new EventHandler();
                $events = $eventHandler->getEvents($params);
                $counter = $eventHandler->getEvents($params, true);
                $list = new ListingEvents($events, $counter->f(0));
                $list->setEntriesNames('events');

                $list->display(
                    1,
                    100,
                    '<form action="' . App::backend()->url()->get('admin.posts') . '" method="post">' .
                    '%s' .
                    '<p>' .
                    implode(
                        '',
                        array_map(fn($id) => (new Hidden('entries[]', (string) $id))->render(), array_values($ap->getIDs()))
                    ) .
                    App::nonce()->getFormNonce() .
                    form::hidden(['action'], ActionsEvents::BIND_EVENT_ACTION) .
                    '<input type="submit" value="' . __('Save') . '" /></p>' .
                    '</form>'
                );
                $ap->endPage();
            }
        }
        // Unbind all posts from selected events
        if ($action === ActionsEvents::UNBIND_POST_ACTION) {
            $posts_ids = $ap->getIDs();
            if (empty($posts_ids)) {
                throw new Exception(__('No entry selected'));
            }
            $params['sql'] = ' AND P.post_id ' . App::con()->in($posts_ids) . ' ';
            $posts = App::blog()->getPosts($params);

            if (!$posts->isEmpty()) {
                while ($posts->fetch()) {
                    App::meta()->delPostMeta($posts->post_id, 'eventhandler');
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
                foreach ($post['entries'] as $eventId) {
                    $params = ['event_id' => $eventId];
                    $posts = $eventHandler->getPostsByEvent($params);
                    $event = $eventHandler->getEvents($params);
                    if ($posts->isEmpty()) {
                        Notices::addWarningNotice(sprintf(
                            __('Event #%d (%s) has no related post to be unbound from.'),
                            $eventId,
                            $event->post_title
                        ));
                        continue;
                    }
                    while ($posts->fetch()) {
                        App::meta()->delPostMeta($posts->post_id, 'eventhandler', $eventId);
                    }

                    Notices::addSuccessNotice(sprintf(
                        __(
                            'Event #%d (%s) unbound from one related post',
                            'Event #%d (%s) unbound from %d related posts',
                            $posts->count()
                        ),
                        $eventId,
                        $event->post_title,
                        $posts->count()
                    ));
                }
            }
        }
    }
}
