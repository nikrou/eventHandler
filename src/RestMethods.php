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

use Dotclear\App;
use Dotclear\Helper\Date;
use Exception;

class RestMethods
{
    /**
     * @param array<string, mixed> $get
     * @param array<string, mixed> $post
     *
     * @return  array<string, mixed>
     */
    public static function unbindEventOfPost(array $get, array $post): array
    {
        $post_id = $post['postId'] ?? null;
        $event_id = $post['eventId'] ?? null;

        if (is_null($post_id)) {
            throw new Exception(__('No such post ID'));
        }

        if (is_null($event_id)) {
            throw new Exception(__('No such event ID'));
        }

        try {
            App::meta()->delPostMeta($post_id, 'eventhandler', $event_id);
        } catch (Exception) {
            throw new Exception(__('An error occured when trying de unbind event'));
        }

        return ['message' => __('Event removed from post')];
    }

    /**
     * @param array<string, mixed> $get
     *
     * @return  array<string, mixed>
     */
    public static function calendar(array $get): array
    {
        $current_ym = $get['curDate'] ?? '';
        $direction = $get['reqDirection'] ?? 'prev';
        $weekstart = isset($get['weekStart']) ? (bool) $get['weekStart'] : false;
        $startonly = isset($get['startOnly']) ? (bool) $get['startOnly'] : true;

        $table = '<p>' . __('An error occured') . '</p>';

        if (!My::settings()->active) {
            throw new Exception(__('Event is disabled on this blog'));
        }

        try {
            $year = $cyear = (int) substr((string) $current_ym, 0, 4);
            $month = $cmonth = (int) substr((string) $current_ym, 4, 2);

            $prev = date('Y-m-01 00:00:00', mktime(0, 0, 0, $cmonth - 1, 1, $cyear));
            $next = date('Y-m-01 00:00:00', mktime(0, 0, 0, $cmonth + 1, 1, $cyear));

            if ($direction == 'prev') {
                $year = Date::str('%Y', strtotime($prev));
                $month = Date::str('%m', strtotime($prev));
            } else {
                $year = Date::str('%Y', strtotime($next));
                $month = Date::str('%m', strtotime($next));
            }

            $calendar = Calendar::getArray($year, $month, $weekstart);

            $table = Calendar::parseArray($calendar, $weekstart, $startonly, true);
        } catch (Exception) {
            throw new Exception(__('Failed to get calendar'));
        }

        return ['calendar' => $table];
    }
}
