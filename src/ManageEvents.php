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
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\eventHandler\Listing\ListingEvents;
use form;

class ManageEvents extends Process
{
    private static $from_id = null;

    private static $from_post = null;

    public static function init(): bool
    {
        if (My::checkContext(My::MANAGE)) {
            self::status(empty($_REQUEST['part']) || $_REQUEST['part'] === 'events');
        }

        return self::status();
    }

    public static function process(): bool
    {
        if (!empty($_REQUEST['from_id'])) {
            try {
                self::$from_id = (int) $_REQUEST['from_id'];
                self::$from_post = dcCore::app()->blog->getPosts(['post_id' => self::$from_id, 'post_type' => '']);
                if (self::$from_post->isEmpty()) {
                    self::$from_id = self::$from_post = null;
                    throw new \Exception(__('No such post ID'));
                }
            } catch (\Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        dcCore::app()->admin->events_filter = new FilterEvents();

        $params = dcCore::app()->admin->events_filter->params();
        $params['post_type'] = 'eventhandler';
        $params['no_content'] = true;

        dcCore::app()->admin->events_list = null;
        try {
            $eventHandler = new EventHandler();
            $events = $eventHandler->getEvents($params);
            $counter = $eventHandler->getEvents($params, true);
            dcCore::app()->admin->events_list = new ListingEvents($events, $counter->f(0));
            dcCore::app()->callBehavior('adminEventHandlerEventsListCustom', [$events, $counter, dcCore::app()->admin->events_list]);
        } catch (\Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        dcCore::app()->admin->events_actions = new ActionsEvents(My::manageUrl(['part' => 'events']));
        if (self::$from_id) {
            dcCore::app()->admin->events_actions->addAction([ActionsEvents::BIND_EVENT_ACTION], [ActionsEvents::class, 'bindEvents']);
        }

        dcCore::app()->admin->events_actions_rendered = null;
        if (dcCore::app()->admin->events_actions->process()) {
            dcCore::app()->admin->events_actions_rendered = true;
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        if (dcCore::app()->admin->events_actions_rendered) {
            dcCore::app()->admin->events_actions->render();

            return;
        }

        Page::openModule(
            __('Events'),
            Page::jsLoad('js/_posts_list.js') .
            dcCore::app()->admin->events_filter->js(My::manageUrl())
        );

        echo Page::breadcrumb([Html::escapeHTML(dcCore::app()->blog->name) => '',
            '<a href="' . My::manageUrl(['part' => 'events']) . '">' . __('Events') . '</a>' => ''
        ]);

        echo Notices::getNotices();

        if (!dcCore::app()->error->flag()) {
            echo '<p class="top-add"><a class="button add" href="', My::manageUrl(['part' => 'event']), '">', __('New event'), '</a></p>';

            if (self::$from_id) {
                echo '<p class="info">', sprintf(__('Attach events to "%s" post.'), self::$from_post->post_title), '</p>';
            }

            dcCore::app()->admin->events_filter->display('admin.plugin.' . My::id());

            $form_end = '';
            if (self::$from_id) {
                $form_end = '<input type="submit" value="' . __('Attach selected events') . '" />' .
                form::hidden('action', ActionsEvents::BIND_EVENT_ACTION) .
                form::hidden(['from_id'], self::$from_id);
            } else {
                $form_end = '<label for="action" class="classic">' . __('Selected events action:') . '</label>' .
                form::combo('action', dcCore::app()->admin->events_actions->getCombo()) .
                '<input id="do-action" type="submit" value="' . __('ok') . '" />';
            }

            dcCore::app()->admin->events_list->display(
                dcCore::app()->admin->events_filter->page,
                dcCore::app()->admin->events_filter->nb,
                '<form action="' . My::manageUrl() . '" method="post" id="form-entries">' .
                '%s' .
                '<div class="two-cols">' .
                '<p class="col checkboxes-helpers"></p>' .
                '<p class="col right">' . $form_end .
                dcCore::app()->admin->url->getHiddenFormFields('admin.plugin.' . My::id(), dcCore::app()->admin->events_filter->values()) .
                dcCore::app()->formNonce() .
                '</p></div>' .
                '</form>'
            );
        }

        echo '<hr class="clear"/><p class="right">';
        if (dcCore::app()->auth->check('admin', dcCore::app()->blog->id)) {
            echo '<a class="button" href="' . My::manageUrl(['part' => 'settings']), '">' . __('Settings') . '</a> - ';
        }
        echo 'eventHandler - ', dcCore::app()->plugins->moduleInfo('eventHandler', 'version');
        echo '<img alt="',  __('Event handler'), '" src="index.php?pf=eventHandler/icon.svg" width="16px" />';
        echo '</p>';

        Page::helpBlock(My::id());
        Page::closeModule();
    }
}
