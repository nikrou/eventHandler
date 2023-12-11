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
use ArrayObject;
use dcCore;

class Calendar
{
    // claim timestamp of sunday
    public const SUNDAY_TS = 1_042_329_600;

    // Prepare structure of the calendar
    public static function getArray($year = null, $month = null, $weekstart = 0)
    {
        /** @var array<string, string> $calendar */
        $calendar = new ArrayObject();

        // Parse date in
        $weekstart = abs((int) $weekstart) + 0;

        if (null === $year || 4 != strlen($year)) {
            $year = date('Y', time());
        }
        if (null === $month || 2 != strlen($month)) {
            $month = date('m', time());
        }
        $day = date('d', time());

        // ts
        $dt = date('Y-m-01 00:00:00', mktime(0, 0, 0, (int) $month, 1, (int) $year));
        $ts = strtotime($dt);
        $prev_dt = date('Y-m-01 00:00:00', mktime(0, 0, 0, (int) $month - 1, 1, (int) $year));
        $next_dt = date('Y-m-01 00:00:00', mktime(0, 0, 0, (int) $month + 1, 1, (int) $year));

        $calendar['year'] = (int) $year;
        $calendar['month'] = (int) $month;
        $calendar['day'] = (int) $day;

        // caption
        $calendar['caption'] = [
            'prev_url' => Date::dt2str('%Y/%m', $prev_dt),
            'prev_txt' => Date::str('%B %Y', strtotime($prev_dt)),
            'current' => Date::str('%B %Y', $ts),
            'current_url' => Date::str('%Y/%m', $ts),
            'current_dt' => Date::str('%Y%m', $ts),
            'next_url' => Date::dt2str('%Y/%m', $next_dt),
            'next_txt' => Date::str('%B %Y', strtotime($next_dt)),
        ];

        // days of week
        $first_ts = self::SUNDAY_TS + ((int) $weekstart * 86400);
        $last_ts = $first_ts + (6 * 86400);
        $first = date('w', $ts);
        $first = ($first == 0) ? 7 : $first;
        $first = $first - $weekstart;
        $limit = date('t', $ts);

        $i = 0;
        for ($j = $first_ts; $j <= $last_ts; $j = $j + 86400) {
            $calendar['head'][$i]['day_txt'] = Date::str('%a', $j);
            $i++;
        }

        // each days
        $d = 1;
        $i = $row = $field = 0;
        $dstart = false;

        while ($i < 42) {
            if ($i % 7 == 0) {
                $row++;
                $field = 0;
            }
            if ($i == $first) {
                $dstart = true;
            }
            if ($dstart && !checkdate((int) $month, $d, (int) $year)) {
                $dstart = false;
            }
            $calendar['rows'][$row][$field] = $dstart ? $d : ' ';
            $field++;

            if (($i + 1) % 7 == 0 && $d >= $limit) {
                $i = 42;
            }

            if ($dstart) {
                $d++;
            }

            $i++;
        }

        return $calendar;
    }

    // Fill calendar
    public static function parseArray($calendar, $weekstart, $startonly, $rest = false)
    {
        $eventHandler = new EventHandler();

        // Additional class for js params
        $class = $weekstart ? ' weekstart' : '';
        $class .= $startonly ? ' startonly' : '';

        $res = '';
        if (!$rest) {
            $res .= "\n<div class=\"calendar-array" . $class . "\">";
        }
        $res .= "<table summary=\"" . __('Calendar') . "\">\n";

        // Caption
        $base = '';
        if ($calendar['caption']) {
            $base = dcCore::app()->blog->url . dcCore::app()->url->getBase('eventhandler_list') . '/' . ($startonly ? 'of' : 'on') . '/';

            $res .= " <caption title=\"" . $calendar['caption']['current_dt'] . "\">\n";
            if (!empty($calendar['caption']['prev_url'])) {
                $res .= "  <a class=\"prev\" href=\"" . $base . $calendar['caption']['prev_url'] . "\" title=\"" . $calendar['caption']['prev_txt'] . "\">&#171;</a> \n";
            }

            $res .= "  <a class=\"current\" href=\"" . $base . $calendar['caption']['current_url'] . "\" title=\"" . __('Detail') . "\">" . $calendar['caption']['current'] . "</a>\n";

            if (!empty($calendar['caption']['next_url'])) {
                $res .= "  <a class=\"next\" href=\"" . $base . $calendar['caption']['next_url'] . "\" title=\"" . $calendar['caption']['next_txt'] . "\">&#187;</a> \n";
            }

            $res .= " </caption>\n";
        }

        // Head line
        if ($calendar['head']) {
            $res .= " <thead>\n  <tr>\n";
            foreach ($calendar['head'] as $d) {
                $res .= "   <th>" . $d['day_txt'] . "</th>\n";
            }
            $res .= "  </tr>\n </thead>\n";
        }

        // Rows
        if ($calendar['rows']) {
            $res .= " <tbody>\n";

            foreach ($calendar['rows'] as $days) {
                $res .= "  <tr>\n";
                foreach ($days as $day) {
                    if (' ' != $day) {
                        $params = [];
                        if (!$startonly) {
                            $params['event_period'] = 'ongoing';
                            $params['event_startdt'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $calendar['month'], (int) $day + 1, $calendar['year']));
                            $params['event_enddt'] = date('Y-m-d H:i:s', mktime(0, 0, 0, $calendar['month'], (int) $day, $calendar['year']));
                        } else {
                            $params['event_start_year'] = $calendar['year'];
                            $params['event_start_month'] = $calendar['month'];
                            $params['event_start_day'] = $day;
                        }

                        $ev_rs = $eventHandler->getEvents($params);
                        $count = $ev_rs->count();

                        if ($count == 1) {
                            $day =
                            '<a href="' . $ev_rs->getURL() . '" title="' .
                            Html::escapeHTML($ev_rs->post_title) . '">' . $day . '</a>';
                        } elseif ($count > 1) {
                            $day =
                            '<a href="' . $base . $calendar['year'] . '/' . $calendar['month'] . '/' . $day .
                            '" title="' .
                            ($count == 1 ? __('one event') : sprintf(__('%s events'), $count)) .
                            '">' . $day . '</a>';
                        }
                    }
                    $res .= "   <td" . (2 < strlen((string) $day) ? ' class="eventsday"' : '') . ">" . $day . "</td>\n";
                }
                $res .= "  </tr>\n";
            }
            $res .= " </tbody>\n";
        }
        $res .= "</table>";
        if (!$rest) {
            $res .= "</div>\n";
        }

        return $res;
    }
}
