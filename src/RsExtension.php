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
use context;
use dcCore;
use rsExtPost;

class RsExtension extends rsExtPost
{
    // Duration is on same real day
    public static function isOnSameDay($rs)
    {
        return Date::dt2str('%Y%j', $rs->event_startdt) == Date::dt2str('%Y%j', $rs->event_enddt);
    }

    // Duration is less than 24 hours
    public static function isOnOneDay($rs)
    {
        return (strtotime($rs->event_enddt) - strtotime($rs->event_startdt)) < 86401;
    }

    public static function getEventTS($rs, $type = '')
    {
        if ($type == 'upddt') {
            return strtotime($rs->post_upddt);
        } elseif ($type == 'creadt') {
            return strtotime($rs->post_creadt);
        } elseif ($type == 'startdt') {
            return strtotime($rs->event_startdt);
        } elseif ($type == 'enddt') {
            return strtotime($rs->event_enddt);
        } else {
            return strtotime($rs->post_dt);
        }
    }

    public static function getEventISO8601Date($rs, $type = '')
    {
        if (in_array($type, ['upddt', 'creadt'])) {
            return Date::iso8601($rs->getTS($type) + Date::getTimeOffset($rs->post_tz), $rs->post_tz);
        } else {
            return Date::iso8601($rs->getTS(), $rs->post_tz);
        }
    }

    public static function getEventRFC822Date($rs, $type = '')
    {
        if (in_array($type, ['upddt', 'creadt'])) {
            return Date::rfc822($rs->getTS($type) + Date::getTimeOffset($rs->post_tz), $rs->post_tz);
        } else {
            return Date::rfc822($rs->getTS($type), $rs->post_tz);
        }
    }

    public static function getEventDate($rs, $format, $type = '')
    {
        if (!$format) {
            $format = dcCore::app()->blog->settings->system->date_format;
        }

        if ($type == 'upddt') {
            return Date::dt2str($format, $rs->post_upddt, $rs->post_tz);
        } elseif ($type == 'creadt') {
            return Date::dt2str($format, $rs->post_creadt, $rs->post_tz);
        } elseif ($type == 'startdt') {
            return Date::dt2str($format, $rs->event_startdt);
        } elseif ($type == 'enddt') {
            return Date::dt2str($format, $rs->event_enddt);
        } else {
            return Date::dt2str($format, $rs->post_dt);
        }
    }

    public static function getEventTime($rs, $format, $type = '')
    {
        if (!$format) {
            $format = dcCore::app()->blog->settings->system->time_format;
        }

        if ($type == 'upddt') {
            return Date::dt2str($format, $rs->post_upddt, $rs->post_tz);
        } elseif ($type == 'creadt') {
            return Date::dt2str($format, $rs->post_creadt, $rs->post_tz);
        } elseif ($type == 'startdt') {
            return Date::dt2str($format, $rs->event_startdt);
        } elseif ($type == 'enddt') {
            return Date::dt2str($format, $rs->event_enddt);
        } else {
            return Date::dt2str($format, $rs->post_dt);
        }
    }

    public static function getPeriod($rs)
    {
        $now = date('Y-m-d H:i:s');

        if ($rs->event_startdt > $now) {
            return 'scheduled';
        } elseif ($rs->event_enddt < $now) {
            return 'finished';
        } else {
            return 'ongoing';
        }
    }

    public static function firstEventOfDay($rs, $type = '')
    {
        if ($rs->isStart()) {
            return true;
        }

        if ($type == 'upddt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->post_upddt, $rs->post_tz);
        } elseif ($type == 'creadt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->post_creadt, $rs->post_tz);
        } elseif ($type == 'startdt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->event_startdt);
        } elseif ($type == 'enddt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->event_enddt);
        } else {
            $cdate = $rs->post_dt;
        }
        $rs->movePrev();

        if ($type == 'upddt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->post_upddt, $rs->post_tz);
        } elseif ($type == 'creadt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->post_creadt, $rs->post_tz);
        } elseif ($type == 'startdt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->event_startdt);
        } elseif ($type == 'enddt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->event_enddt);
        } else {
            $ndate = $rs->post_dt;
        }
        $rs->moveNext();

        return $ndate != $cdate;
    }

    public static function lastEventOfDay($rs, $type = '')
    {
        if ($rs->isEnd()) {
            return true;
        }

        if ($type == 'upddt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->post_upddt, $rs->post_tz);
        } elseif ($type == 'creadt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->post_creadt, $rs->post_tz);
        } elseif ($type == 'startdt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->event_startdt);
        } elseif ($type == 'enddt') {
            $cdate = Date::dt2str('%Y%m%d', $rs->event_enddt);
        } else {
            $cdate = $rs->post_dt;
        }
        $rs->moveNext();

        if ($type == 'upddt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->post_upddt, $rs->post_tz);
        } elseif ($type == 'creadt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->post_creadt, $rs->post_tz);
        } elseif ($type == 'startdt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->event_startdt);
        } elseif ($type == 'enddt') {
            $ndate = Date::dt2str('%Y%m%d', $rs->event_enddt);
        } else {
            $ndate = $rs->post_dt;
        }
        $rs->movePrev();

        return $ndate != $cdate;
    }

    // Not best place for next functions but work fine here!

    public static function getIcalVEVENT($rs)
    {
        $l = [];
        $l[] = "BEGIN:VEVENT";
        $l[] = "TRANSP:OPAQUE";
        $l[] = "SEQUENCE:0";
        $l[] = "PRIORITY:5";
        $l[] = "CLASS:PUBLIC";
        $l[] = "SUMMARY;CHARSET=UTF-8:" . $rs->post_title;
        $l[] = "URL:" . $rs->getURL();
        $l[] = "UID:" . $rs->post_id;
        $l[] = "DTSTAMP;TZID=" . dcCore::app()->blog->settings->system->blog_timezone . ":" . Date::dt2str("%Y%m%dT%H%M%S", $rs->post_upddt, $rs->post_tz);
        $l[] = "DTSTART;TZID=" . dcCore::app()->blog->settings->system->blog_timezone . ":" . Date::dt2str("%Y%m%dT%H%M%S", $rs->event_startdt);
        $l[] = "DTEND;TZID=" . dcCore::app()->blog->settings->system->blog_timezone . ":" . Date::dt2str("%Y%m%dT%H%M%S", $rs->event_enddt);
        $l[] = "DESCRIPTION;CHARSET=UTF-8:" . $rs->post_title . " - " . ($rs->isExtended() ? context::global_filter($rs->getExcerpt(), 1, 1, '250', 0, 0, '') : context::global_filter($rs->getContent(), 1, 1, '250', 0, 0, '')) . " - " . $rs->getURL();
        if ($rs->event_address) {
            $l[] = "LOCATION;CHARSET=UTF-8:" . context::global_filter($rs->event_address, 1, 1, '250', 0, 0, '');
        }
        if ($rs->cat_id) {
            $l[] = "CATEGORIES;CHARSET=UTF-8:" . $rs->cat_title;
        }
        $l[] = "END:VEVENT";

        $res = '';
        foreach ($l as $k => $line) {
            $res .= implode("\r\n ", str_split(trim($line), 70)) . "\r\n";
        }
        return $res;
    }

    public static function getHcalVEVENT($rs)
    {
        $res =
        '<div class="vevent">' . "\n" .
        '<h2 class="summary">' . Html::escapeHTML($rs->post_title) . '</h2>' . "\n" .
        '<ul>' . "\n" .
        '<li>' . __('Start date:') . ' <abbr class="dtstart" title="' . $rs->getEventISO8601Date() . '">' . $rs->getEventDate('', 'startdt') . ', ' . $rs->getEventTime('', 'startdt') . '</abbr></li>' . "\n" .
        '<li>' . __('End date:') . ' <abbr class="dtend" title="' . $rs->getEventISO8601Date() . '">' . $rs->getEventDate('', 'enddt') . ', ' . $rs->getEventTime('', 'enddt') . '</abbr></li>' . "\n";
        if ($rs->event_address) {
            $res .= '<li>' . __('Address:') . ' <abbr class="location">' . Html::escapeHTML($rs->event_address) . '</abbr></li>' . "\n";
        }
        if ($rs->event_latitude && $rs->event_longitude) {
            $res .= '<li>' . __('Location:') . ' <abbr class="geo" title="' . $rs->event_latitude . ';' . $rs->event_longitude . '">' . __('latitude:') . ' ' . $rs->event_latitude . ', ' . __('longitude:') . ' ' . $rs->event_longitude . '</abbr></li>' . "\n";
        }
        if ($rs->cat_id) {
            $res .= '<li>' . __('Category:') . ' <abbr class="categories">' . Html::escapeHTML($rs->cat_title) . '</abbr></li>' . "\n";
        }
        $res .=
        '</ul>' . "\n" .
        '<p class="description">' . ($rs->isExtended() ? context::global_filter($rs->getExcerpt(), 1, 1, '250', 0, 0, '') : context::global_filter($rs->getContent(), 1, 1, '250', 0, 0, '')) . '</p>' . "\n" .
        '<p><a class="url" href="' . $rs->getURL() . '">' . __('Read more') . '</a> <abbr class="uid">' . dcCore::app()->blog->id . $rs->post_id . '</abbr></p>' . "\n" .
        '</div>';

        return $res;
    }

    public static function getMapVEvent($rs)
    {
        return
            '<div style="display:none;" class="event-map-marker">' . "\n" .
            '<p class="event-map-marker-latitude">' . $rs->event_latitude . '</p>' . "\n" .
            '<p class="event-map-marker-longitude">' . $rs->event_longitude . '</p>' . "\n" .
            '<p class="event-map-marker-title">' . Html::escapeHTML($rs->post_title) . '</p>' . "\n" .
            '<p class="event-map-marker-startdt" title="' . Html::escapeHTML($rs->getEventDate("", "startdt") . ", " . $rs->getEventTime("", "startdt")) . '">' . $rs->event_startdt . '</p>' . "\n" .
            '<p class="event-map-marker-enddt" title="' . Html::escapeHTML($rs->getEventDate("", "enddt") . ", " . $rs->getEventTime("", "enddt")) . '">' . $rs->event_enddt . '</p>' . "\n" .
            '<p class="event-map-marker-address">' . Html::escapeHTML($rs->event_address) . '</p>' . "\n" .
            '<p class="event-map-marker-link">' . $rs->getURL() . '</p>' . "\n" .
            '</div>';
    }
}
