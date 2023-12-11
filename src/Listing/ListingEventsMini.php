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

use Dotclear\Core\Backend\Listing\Listing;
use Dotclear\Core\Backend\Listing\Pager;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Html;
use dcBlog;
use dcCore;
use form;

class ListingEventsMini extends Listing
{
    public function display($page, $nb_per_page, $enclose_block = '')
    {
        if ($this->rs->isEmpty()) {
            echo '<p><strong>' . __('No event') . '</strong></p>';
        } else {
            $pager = new Pager($page, $this->rs_count, $nb_per_page, 10);
            $pager->html_prev = $this->html_prev;
            $pager->html_next = $this->html_next;
            $pager->var_page = 'page';

            $columns = ['<th colspan="2">' . __('Title') . '</th>',
                '<th>' . __('Period') . '</th>',
                '<th>' . __('Start date') . '</th>',
                '<th>' . __('End date') . '</th>',
                '<th>' . __('Status') . '</th>'];

            // --BEHAVIOR-- adminEventHandlerEventsListHeader
            dcCore::app()->callBehavior('adminEventHandlerEventsListHeaders', ['columns' => &$columns], true);
            $html_block =
                '<table class="clear"><tr>' .
            join('', $columns) .
            '</tr>%s</table>';

            if ($enclose_block) {
                $html_block = sprintf($enclose_block, $html_block);
            }

            echo '<p>' . __('Page(s)') . ' : ' . $pager->getLinks() . '</p>';

            $blocks = explode('%s', $html_block);

            echo $blocks[0];

            while ($this->rs->fetch()) {
                echo $this->postLine();
            }

            echo $blocks[1];

            echo '<p>' . __('Page(s)') . ' : ' . $pager->getLinks() . '</p>';
        }
    }

    private function postLine()
    {
        $img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
        $img_status = match ((int) $this->rs->post_status) {
            dcBlog::POST_PUBLISHED => sprintf($img, __('published'), 'check-on.png'),
            dcBlog::POST_UNPUBLISHED => sprintf($img, __('unpublished'), 'check-off.png'),
            dcBlog::POST_SCHEDULED => sprintf($img, __('scheduled'), 'scheduled.png'),
            dcBlog::POST_PENDING => sprintf($img, __('pending'), 'check-wrn.png'),
            default => '',
        };

        $protected = '';
        if ($this->rs->post_password) {
            $protected = sprintf($img, __('protected'), 'locker.png');
        }

        $selected = '';
        if ($this->rs->post_selected) {
            $selected = sprintf($img, __('selected'), 'selected.png');
        }

        $period = $this->rs->getPeriod();
        $style = ' eventhandler-' . $period;

        $columns = [
            '<td class="nowrap">' . form::checkbox(['events[]'], $this->rs->post_id, '', '', '', !$this->rs->isEditable()) . '</td>' .
            '<td><a href="' . dcCore::app()->getPostAdminURL($this->rs->post_type, $this->rs->post_id) . '" ' .
            'title="' . Html::escapeHTML($this->rs->getURL()) . '">' . Html::escapeHTML($this->rs->post_title) . '</a></td>',
            '<td class="nowrap' . $style . '">' . __($period) . '</td>',
            '<td class="nowrap">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->event_startdt) . '</td>',
            '<td class="nowrap">' . Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->event_enddt) . '</td>',
            '<td class="nowrap status">' . $img_status . ' ' . $selected . ' ' . $protected . '</td>',
        ];

        // --BEHAVIOR-- adminEventHandlerEventsListBody
        dcCore::app()->callBehavior('adminEventHandlerEventsListBody', $this->rs, ['columns' => &$columns], true);

        return
            '<tr class="line' . ($this->rs->post_status != 1 ? ' offline' : '') . $style . '" id="e' . $this->rs->post_id . '">' .
            join("", $columns) .
            '</tr>';
    }
}
