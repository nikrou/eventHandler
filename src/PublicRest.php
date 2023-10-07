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
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\XmlTag;

class PublicRest
{
    public static function calendar($get, $post)
    {
        $blog_uid = isset($post['blogId']) ? $post['blogId'] : null;
        $current_ym = isset($post['curDate']) ? $post['curDate'] : '';
        $direction = isset($post['reqDirection']) ? $post['reqDirection'] : 'prev';
        $weekstart = isset($post['weekStart']) ? (boolean) $post['weekStart'] : false;
        $startonly = isset($post['startOnly']) ? (boolean) $post['startOnly'] : true;

        $rsp = new XmlTag();
        $table = '<p>' . __('An error occured') . '</p>';

        if (!dcCore::app()->blog->settings->eventHandler->active) {
            throw new \Exception(__('Event is disabled on this blog'));
        }

        try {
            $year = $cyear = (int) substr($current_ym, 0, 4);
            $month = $cmonth = (int) substr($current_ym, 4, 2);

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

            $table = Calendar::parseArray($calendar, $weekstart, $startonly, $rest = true);
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to get calendar'));
        }

        $xc = new XmlTag('calendar');
        $xc->blog = $blog_uid;
        $xc->CDATA($table);
        $rsp->insertNode($xc);

        return $rsp;
    }
}
