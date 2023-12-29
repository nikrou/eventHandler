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

use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsElement;
use Dotclear\App;
use record;

class WidgetsTemplate
{
    public static function events(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (!My::settings()->active) {
            return '';
        }

        if ($w->homeonly == 1 && App::url()->getType() != 'default' || $w->homeonly == 2 && App::url()->getType() == 'default') {
            return '';
        }

        $params['sql'] = '';

        if ($w->period) {
            $params['event_period'] = $w->period;
        }

        $params['order'] = ($w->sortby && in_array($w->sortby, ['post_title', 'post_dt', 'event_startdt', 'event_enddt'])) ? $w->sortby . ' ' : 'event_startdt ';
        $params['order'] .= $w->sort == 'desc' ? 'desc' : 'asc';

        if ($w->limit !== '') {
            $params['limit'] = abs((int) $w->limit);
        }

        $params['no_content'] = true;
        $params['post_type'] = EventHandler::POST_TYPE;

        if ($w->selectedonly) {
            $params['post_selected'] = 1;
        }

        if ($w->category) {
            if ($w->category == 'null') {
                $params['sql'] .= ' AND P.cat_id IS NULL ';
            } elseif (is_numeric($w->category)) {
                $params['cat_id'] = (int) $w->category;
            } else {
                $params['cat_url'] = $w->category;
            }
        } else { // If no paricular category is selected, remove unlisted categories
            $public_hidden_categories = @unserialize(My::settings()->public_hidden_categories);
            if (is_array($public_hidden_categories) && !empty($public_hidden_categories)) {
                foreach ($public_hidden_categories as $k => $cat_id) {
                    $params['sql'] .= " AND P.cat_id != '$cat_id' ";
                }
            }
        }

        $eventHandler = new EventHandler();
        $rs = $eventHandler->getEvents($params);

        if ($rs->isEmpty()) {
            return '';
        }

        // Display
        $res = ($w->content_only ? '' : '<div class="widget eventhandler-events' . ($w->class ? ' ' . Html::escapeHTML($w->class) : '') . '">') .
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') .
            // Events events
            '<ul>';

        while ($rs->fetch()) {
            // If same day
            if ($rs->isOnSameDay()) {
                $over_format = __('On %sd from %st to %et');
            } else {
                $over_format = __('From %sd, %st to %ed, %et');
            }

            // Format items
            $fsd = Date::dt2str($w->date_format, $rs->event_startdt);
            $fst = Date::dt2str($w->time_format, $rs->event_startdt);
            $fed = Date::dt2str($w->date_format, $rs->event_enddt);
            $fet = Date::dt2str($w->time_format, $rs->event_enddt);

            // Replacement
            $over = str_replace(
                ['%sd', '%st', '%ed', '%et', '%%'],
                [$fsd, $fst, $fed, $fet, '%'],
                (string) $over_format
            );
            $title = '<a href="' . $rs->getURL() . '" title="' . $over . '">' . Html::escapeHTML($rs->post_title) . '</a>';
            $cat = '';
            if ($w->item_showcat && $rs->cat_id) {
                $cat = sprintf(
                    ' (<a href="%s" title="%s">%s</a>)',
                    $rs->getCategoryURL(),
                    __('go to this category'),
                    Html::escapeHTML($rs->cat_title)
                );
            }

            $res .= '<li>' . $title . $cat . '</li>';
        }
        $res .= '</ul>';

        if ($w->pagelink) {
            $res .=
                '<p><strong><a href="' .
                App::blog()->url() . App::url()->getBase('eventhandler_list') .
                '" >' . __('All events') . '</a></strong></p>';
        }

        $res .= ($w->content_only ? '' : '</div>');

        return $res;
    }

    public static function eventsOfPost(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        // Plugin active
        if (!My::settings()->active) {
            return '';
        }
        // Post page only
        if (App::url()->getType() != 'post') {
            return '';
        }

        $params['sql'] = '';
        // Period
        if ($w->period) {
            $params['event_period'] = $w->period;
        }
        // Sort field
        $params['order'] = ($w->sortby && in_array($w->sortby, ['post_title', 'post_dt', 'event_startdt', 'event_enddt'])) ?
            $w->sortby . ' ' : 'event_startdt ';
        // Sort order
        $params['order'] .= $w->sort == 'desc' ? 'desc' : 'asc';
        // Rows number
        if ('' !== $w->limit) {
            $params['limit'] = abs((int) $w->limit);
        }
        // No post content
        $params['no_content'] = true;
        // Post id
        $params['post_id'] = App::frontend()->context()->posts->post_id;
        // Event type
        $params['event_type'] = 'eventhandler';
        // Category
        if ($w->category) {
            if ($w->category == 'null') {
                $params['sql'] .= ' AND P.cat_id IS NULL ';
            } elseif (is_numeric($w->category)) {
                $params['cat_id'] = (int) $w->category;
            } else {
                $params['cat_url'] = $w->category;
            }
        } else { // If no paricular category is selected, remove unlisted categories
            $public_hidden_categories = @unserialize(My::settings()->public_hidden_categories);
            if (is_array($public_hidden_categories) && !empty($public_hidden_categories)) {
                foreach ($public_hidden_categories as $cat_id) {
                    $params['sql'] .= " AND P.cat_id != '$cat_id' ";
                }
            }
        }
        // Get posts
        $eventHandler = new EventHandler();
        $rs = $eventHandler->getEventsByPost($params);
        // No result
        if ($rs->isEmpty()) {
            return '';
        }

        // Display
        $res = ($w->content_only ? '' : '<div class="widget eventhandler-eventsofpost' . ($w->class ? ' ' . Html::escapeHTML($w->class) : '') . '">') .
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') .
            // Events eventsofpost
            '<ul>';
        while ($rs->fetch()) {
            // If same day
            if ($rs->isOnSameDay()) {
                $over_format = __('On %sd from %st to %et');
            } else {
                $over_format = __('From %sd, %st to %ed, %et');
            }

            // Format items
            $fsd = Date::dt2str(App::blog()->settings()->system->date_format, $rs->event_startdt);
            $fst = Date::dt2str(App::blog()->settings()->system->time_format, $rs->event_startdt);
            $fed = Date::dt2str(App::blog()->settings()->system->date_format, $rs->event_enddt);
            $fet = Date::dt2str(App::blog()->settings()->system->time_format, $rs->event_enddt);

            // Replacement
            $over = str_replace(
                ['%sd', '%st', '%ed', '%et', ],
                [$fsd, $fst, $fed, $fet],
                (string) $over_format
            );
            $item = Html::escapeHTML($rs->post_title);

            $res .= '<li><a href="' . $rs->getURL() . '" title="' . $over . '">' . $item . '</a></li>';
        }
        $res .= '</ul>';

        if ($w->pagelink) {
            $res .=
                '<p><strong><a href="' .
                App::blog()->url() . App::url()->getBase('eventhandler_list') .
                '" >' . __('All events') . '</a></strong></p>';
        }

        $res .= ($w->content_only ? '' : '</div>');

        return $res;
    }

    public static function postsOfEvent(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        // Plugin active
        if (!My::settings()->active) {
            return '';
        }
        // Event page only
        if (App::url()->getType() != 'eventhandler_single') {
            return '';
        }

        $params['sql'] = '';
        // Sort field
        $params['order'] = ($w->sortby && in_array($w->sortby, ['post_title', 'post_dt'])) ?
            $w->sortby . ' ' : 'post_dt ';
        // Sort order
        $params['order'] .= $w->sort == 'asc' ? 'asc' : 'desc';
        // Rows number
        if ('' !== $w->limit) {
            $params['limit'] = abs((int) $w->limit);
        }
        // No post content
        $params['no_content'] = true;
        // Event id
        $params['event_id'] = App::frontend()->context()->posts->post_id;
        // Event type
        $params['event_type'] = 'eventhandler';
        // Category
        if ($w->category) {
            if ($w->category == 'null') {
                $params['sql'] = ' AND P.cat_id IS NULL ';
            } elseif (is_numeric($w->category)) {
                $params['cat_id'] = (int) $w->category;
            } else {
                $params['cat_url'] = $w->category;
            }
        }
        // Get posts
        $eventHandler = new EventHandler();
        $rs = $eventHandler->getPostsByEvent($params);
        // No result
        if ($rs->isEmpty()) {
            return '';
        }

        // Display
        $res = ($w->content_only ? '' : '<div class="widget eventhandler-postsofevent' . ($w->class ? ' ' . Html::escapeHTML($w->class) : '') . '">') .
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') .
            // Events postsofevent
            '<ul>';

        while ($rs->fetch()) {
            $res .= '<li><a href="' . $rs->getURL() . '">' .
                Html::escapeHTML($rs->post_title) . '</a></li>';
        }
        $res .= '</ul>';

        $res .= ($w->content_only ? '' : '</div>');

        return $res;
    }

    public static function categories(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (!My::settings()->active) {
            return '';
        }

        if ($w->homeonly == 1 && App::url()->getType() != 'default'
            || $w->homeonly == 2 && App::url()->getType() == 'default') {
            return '';
        }

        // Display
        $res = ($w->content_only ? '' : '<div class="widget eventhandler-categories' . ($w->class ? ' ' . Html::escapeHTML($w->class) : '') . '">') .
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '');
        // Events categories
        $rs = App::blog()->getCategories(['post_type' => EventHandler::POST_TYPE]);
        if ($rs->isEmpty()) {
            return '';
        }

        $ref_level = $level = $rs->level - 1;
        while ($rs->fetch()) {
            $class = '';
            if ((App::url()->getType() == 'catevents' && App::frontend()->context()->categories instanceof record && App::frontend()->context()->categories->cat_id == $rs->cat_id)
                || (App::url()->getType() == 'event' && App::frontend()->context()->posts instanceof record && App::frontend()->context()->posts->cat_id == $rs->cat_id)) {
                $class = ' class="category-current"';
            }

            if ($rs->level > $level) {
                $res .= str_repeat('<ul><li' . $class . '>', $rs->level - $level);
            } elseif ($rs->level < $level) {
                $res .= str_repeat('</li></ul>', -($rs->level - $level));
            }

            if ($rs->level <= $level) {
                $res .= '</li><li' . $class . '>';
            }

            $res .=
                '<a href="' . App::blog()->url() . App::url()->getBase('eventhandler_list') .
                '/category/' . $rs->cat_url . '">' .
                Html::escapeHTML($rs->cat_title) . '</a>' .
                ($w->postcount ? ' (' . $rs->nb_post . ')' : '');

            $level = $rs->level;
        }

        if ($ref_level - $level < 0) {
            $res .= str_repeat('</li></ul>', -($ref_level - $level));
        }

        if ($w->pagelink) {
            $res .=
                '<p><strong><a href="' .
                App::blog()->url() . App::url()->getBase('eventhandler_list') .
                '" >' . __('All events') . '</a></strong></p>';
        }

        $res .= ($w->content_only ? '' : '</div>');

        return $res;
    }

    public static function map(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (!My::settings()->active
            || $w->homeonly == 1 && App::url()->getType() != 'default'
            || $w->homeonly == 2 && App::url()->getType() == 'default') {
            return '';
        }

        $params['sql'] = '';
        // Period
        if ($w->period) {
            $params['event_period'] = $w->period;
        }
        // Sort field
        $params['order'] = ($w->sortby && in_array($w->sortby, ['post_title', 'post_dt', 'event_startdt', 'event_enddt'])) ?
            $w->sortby . ' ' : 'event_startdt ';
        // Sort order
        $params['order'] .= $w->sort == 'desc' ? 'desc' : 'asc';
        $params['limit'] = 10;
        $params['no_content'] = true;
        $params['post_type'] = EventHandler::POST_TYPE;
        $params['sql'] .= "AND event_latitude != '' ";
        $params['sql'] .= "AND event_longitude != '' ";

        $public_hidden_categories = @unserialize(My::settings()->public_hidden_categories);
        if (is_array($public_hidden_categories) && !empty($public_hidden_categories)) {
            foreach ($public_hidden_categories as $k => $cat_id) {
                $params['sql'] .= " AND P.cat_id != '$cat_id' ";
            }
        }

        // Get posts
        $eventHandler = new EventHandler();
        $rs = $eventHandler->getEvents($params);
        // No result
        if ($rs->isEmpty()) {
            return '';
        }

        $total_lat = $total_lng = 0;
        $markers = '';
        while ($rs->fetch()) {
            $total_lat += (float) $rs->event_latitude;
            $total_lng += (float) $rs->event_longitude;

            $markers .= $rs->getMapVEvent();
        }

        $lat = round($total_lat / $rs->count(), 7);
        $lng = round($total_lng / $rs->count(), 7);

        // Display
        $res = ($w->content_only ? '' : '<div class="widget eventhandler-map' . ($w->class ? ' ' . Html::escapeHTML($w->class) : '') . '">') .
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') .
            // Events map
            EventHandler::getMapContent(
                $w->map_width,
                $w->map_height,
                $w->map_type,
                (int) $w->map_zoom,
                ((int) $w->map_info),
                $lat,
                $lng,
                $markers
            );

        if ($w->pagelink) {
            $res .=
                '<p><strong><a href="' .
                App::blog()->url() . App::url()->getBase('eventhandler_list') .
                '" >' . __('All events') . '</a></strong></p>';
        }

        $res .= ($w->content_only ? '' : '</div>');

        return $res;
    }

    public static function calendar(WidgetsElement $w): string
    {
        if ($w->offline) {
            return '';
        }

        if (!My::settings()->active
            || $w->homeonly == 1 && App::url()->getType() != 'default'
            || $w->homeonly == 2 && App::url()->getType() == 'default') {
            return '';
        }

        $year = date('Y');
        $month = date('m');

        if (App::frontend()->context()->exists('event_params') && !empty(App::frontend()->context()->event_params['event_start_month'])) {
            $year = App::frontend()->context()->event_params['event_start_year'];
            $month = App::frontend()->context()->event_params['event_start_month'];
        } elseif (App::frontend()->context()->exists('event_params') && !empty(App::frontend()->context()->event_params['event_startdt'])) {
            $year = date('Y', strtotime((string) App::frontend()->context()->event_params['event_startdt']));
            $month = date('m', strtotime((string) App::frontend()->context()->event_params['event_startdt']));
        }

        // Generic calendar Object
        $calendar = Calendar::getArray($year, $month, $w->weekstart !== 'O');

        return
            // Display
            $res = ($w->content_only ? '' : '<div class="widget eventhandler-calendar' . ($w->class ? ' ' . Html::escapeHTML($w->class) : '') . '">') .
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') .
            // Events calendar
            Calendar::parseArray($calendar, $w->weekstart !== '0', $w->startonly !== '0') .
            (
                $w->pagelink ?
             '<p><strong><a href="' .
             App::blog()->url() . App::url()->getBase('eventhandler_list') .
             '" >' . __('All events') . '</a></strong></p>' : ''
            ) .
            ($w->content_only ? '' : '</div>');
    }
}
