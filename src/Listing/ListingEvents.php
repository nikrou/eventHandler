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

namespace Dotclear\Plugin\eventHandler\Listing;

use Dotclear\Core\Blog;
use Dotclear\Core\Backend\Listing\Listing;
use Dotclear\Core\Backend\Listing\Pager;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Html;
use Dotclear\App;
use form;

class ListingEvents extends Listing
{
    private string $entries_names = 'entries';

    public function setEntriesNames(string $name): void
    {
        $this->entries_names = $name;
    }

    public function display(int $page, int $nb_per_page, string $enclose_block = ''): void
    {
        if ($this->rs->isEmpty()) {
            echo '<p><strong>' . __('No event') . '</strong></p>';
        } else {
            $pager = new Pager($page, $this->rs_count, $nb_per_page, 10);

            $columns = [
                '<th colspan="2">' . __('Title') . '</th>',
                '<th>' . __('Start date') . '</th>',
                '<th>' . __('End date') . '</th>',
                '<th>' . __('Entries') . '</th>',
                '<th>' . __('Date') . '</th>',
                '<th>' . __('Category') . '</th>',
                '<th>' . __('Author') . '</th>',
                '<th>' . __('Status') . '</th>',
            ];

            // --BEHAVIOR-- adminEventHandlerEventsListHeaders
            App::behavior()->callBehavior('adminEventHandlerEventsListHeaders', ['columns' => &$columns]);
            $html_block = '<table class="clear"><tr>' .
                join('', $columns) .
                '</tr>%s</table>';

            if ($enclose_block) {
                $html_block = sprintf($enclose_block, $html_block);
            }

            echo $pager->getLinks();

            $blocks = explode('%s', $html_block);

            echo $blocks[0];

            while ($this->rs->fetch()) {
                echo $this->postLine();
            }

            echo $blocks[1];

            $fmt = fn($title, $image) => sprintf('<img alt="%1$s" title="%1$s" src="images/%2$s" /> %1$s', $title, $image);
            echo '<p class="info">' . __('Legend: ') .
                $fmt(__('Published'), 'check-on.png') . ' - ' .
                $fmt(__('Unpublished'), 'check-off.png') . ' - ' .
                $fmt(__('Scheduled'), 'scheduled.png') . ' - ' .
                $fmt(__('Pending'), 'check-wrn.png') . ' - ' .
                $fmt(__('Protected'), 'locker.png') . ' - ' .
                $fmt(__('In widget'), 'selected.png') . ' - ' .
                $fmt(__('Attachments'), 'attach.png') .
                '</p>';

            echo $pager->getLinks();
        }
    }

    private function postLine(): string
    {
        if (App::auth()->check(App::auth()->makePermissions([App::auth()::PERMISSION_CATEGORIES]), App::blog()->id())) {
            $cat_link = '<a href="' . App::backend()->url()->get('admin.category', ['id' => '%s'], '&amp;', true) . '">%s</a>';
        } else {
            $cat_link = '%2$s';
        }

        if ($this->rs->cat_title) {
            $cat_title = sprintf($cat_link, $this->rs->cat_id, Html::escapeHTML($this->rs->cat_title));
        } else {
            $cat_title = __('(No cat)');
        }

        $img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
        $img_status = match ((int) $this->rs->post_status) {
            Blog::POST_PUBLISHED => sprintf($img, __('published'), 'check-on.png'),
            Blog::POST_UNPUBLISHED => sprintf($img, __('unpublished'), 'check-off.png'),
            Blog::POST_SCHEDULED => sprintf($img, __('scheduled'), 'scheduled.png'),
            Blog::POST_PENDING => sprintf($img, __('pending'), 'check-wrn.png'),
            default => '',
        };

        $protected = '';
        if ($this->rs->post_password) {
            $protected = sprintf($img, __('protected'), 'locker.png');
        }

        $selected = '';
        if ($this->rs->post_selected) {
            $selected = sprintf($img, __('In widget'), 'selected.png');
        }

        $now = time();
        if (strtotime((string) $this->rs->event_startdt) > $now) {
            $event_class = 'eventhandler scheduled';
        } elseif (strtotime((string) $this->rs->event_enddt) < $now) {
            $event_class = 'eventhandler finished';
        } else {
            $event_class = 'eventhandler ongoing';
        }

        $entries = $this->rs->eventHandler->getPostsByEvent(['event_id' => $this->rs->post_id], true);
        $nb_entries = '';
        if ($entries->isEmpty()) {
            $nb_entries = 0;
        } else {
            $nb_entries = '<a href="' . App::postTypes()->getPostAdminURL($this->rs->post_type, $this->rs->post_id, true, ['tab' => 'bind-entries']) . '">' . $entries->f(0) . '</a>';
        }

        $columns = [
            '<td class="nowrap">' . form::checkbox([$this->entries_names . '[]'], $this->rs->post_id, '', '', '', !$this->rs->isEditable()) . '</td>' .
            '<td class="maximal"><a href="' . App::postTypes()->getPostAdminURL($this->rs->post_type, $this->rs->post_id) . '">' .
            Html::escapeHTML($this->rs->post_title) . '</a></td>',
            '<td class="nowrap' . ' ' . $event_class . '">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->event_startdt) . '</td>',
            '<td class="nowrap' . ' ' . $event_class . '">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->event_enddt) . '</td>',
            '<td class="nowrap">' . $nb_entries . '</td>',
            '<td class="nowrap">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->post_dt) . '</td>',
            '<td class="nowrap">' . $cat_title . '</td>',
            '<td class="nowrap">' . $this->rs->user_id . '</td>',
            '<td class="nowrap status">' . $img_status . ' ' . $selected . ' ' . $protected . '</td>',
        ];

        // --BEHAVIOR-- adminEventHandlerEventsListBody
        App::behavior()->callBehavior('adminEventHandlerEventsListBody', $this->rs, ['columns' => &$columns]);

        return
            '<tr class="line' . ($this->rs->post_status != 1 ? ' offline' : '') . ' ' . $event_class . '"' .
            ' id="p' . $this->rs->post_id . '">' .
            join("", $columns) .
            '</tr>';
    }
}
