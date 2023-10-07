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
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;

class ActionsEvents extends ActionsPosts
{
    public const BIND_EVENT_ACTION = 'eventhandler_bind_event';
    public const UNBIND_POST_ACTION = 'eventhandler_unbind_post';

    protected $use_render = true;

    public function __construct(?string $uri, array $redirect_args = [])
    {
        parent::__construct($uri, $redirect_args);

        $this->redirect_fields = ['p', 'part'];
        $this->caller_title = __('Events');
    }

    public function beginPage(string $breadcrumb = '', string $head = ''): void
    {
        Page::openModule(__('Events'), Page::jsLoad('js/_posts_actions.js') . $head);
        echo $breadcrumb, '<p><a class="back" href="' . $this->getRedirection(true) . '">' . __('Back to events list') . '</a></p>';
    }

    public function endPage(): void
    {
        Page::closeModule();
    }

    public function process()
    {
        $this->from['post_type'] = 'eventhandler';

        return parent::process();
    }

    public static function bindEvents(ActionsPosts $ap): void
    {
        if ($ap->getAction() !== self::BIND_EVENT_ACTION) {
            return;
        }

        if (empty($ap->from['from_id'])) {
            throw new \Exception(__('No entry selected'));
        }

        $params['sql'] = ' AND P.post_id ' . dcCore::app()->con->in($ap->from['from_id']) . ' ';
        $posts = dcCore::app()->blog->getPosts($params);

        if ($posts->isEmpty()) {
            throw new \Exception(__('No such post'));
        }
        $events_id = [];

        if (isset($ap->from['entries'])) {
            foreach ($ap->from['entries'] as $k => $v) {
                $events_id[$k] = (integer) $v;
            }
            $params['sql'] = 'AND P.post_id ' . dcCore::app()->con->in($events_id) . ' ';
            $eventHandler = new EventHandler();
            $events = $eventHandler->getEvents($params);
            if ($events->isEmpty()) {
                throw new \Exception(__('No such event'));
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
                __(
                    'entry has been bound to the selected event',
                    'entry has been bound to the selected events',
                    $events->count()
                )
            );
            $ap->redirect(true);
        }
    }

    public static function unbindEvents(ActionsPosts $ap): void
    {
        if ($ap->getAction() !== self::UNBIND_POST_ACTION) {
            return;
        }

        $posts_ids = $ap->getIDs();
        if (empty($posts_ids)) {
            throw new \Exception(__('No entry selected'));
        }

        $params['sql'] = ' AND P.post_id ' . dcCore::app()->con->in($posts_ids) . ' ';
        $posts = dcCore::app()->blog->getPosts($params);

        if (!$posts->isEmpty()) {
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
        } elseif ($ap->from['entries']) {
            $eventHandler = new EventHandler();
            foreach ($ap->from['entries'] as $k => $v) {
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
            $ap->redirect(false);
        }

        $ap->redirect(true);
    }
}
