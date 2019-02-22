<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2019 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
#
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class eventHandlerPublicRest
{
	public static function calendar($core,$get,$post)
	{
		$blog_uid = isset($post['blogId']) ? $post['blogId'] : null;
		$current_ym = isset($post['curDate']) ? $post['curDate'] : null;
		$direction = isset($post['reqDirection']) ? $post['reqDirection'] : 'prev';
		$weekstart = isset($post['weekStart']) ? (boolean) $post['weekStart'] : false;
		$startonly = isset($post['startOnly']) ? (boolean) $post['startOnly'] : true;

		$rsp = new xmlTag();
		$table = '<p>'.__('An error occured').'</p>';

		if (!$core->blog->settings->eventHandler->active)
		{
			throw new Exception(__('Event is disabled on this blog'));
		}

		try {

			$year = $cyear = substr($current_ym,0,4);
			$month = $cmonth = substr($current_ym,4,2);

			$prev = date('Y-m-01 00:00:00',mktime(0,0,0,$cmonth - 1,1,$cyear));
			$next = date('Y-m-01 00:00:00',mktime(0,0,0,$cmonth + 1,1,$cyear));

			if ($direction == 'prev') {
				$year = dt::str('%Y',strtotime($prev));
				$month = dt::str('%m',strtotime($prev));
			}
			else {
				$year = dt::str('%Y',strtotime($next));
				$month = dt::str('%m',strtotime($next));
			}

			$calendar = eventHandlerCalendar::getArray($year,$month,$weekstart);

			$table = eventHandlerCalendar::parseArray($calendar,$weekstart,$startonly,$rest=true);

		}
		catch (Exception $e) {
			throw New Exception(__('Failed to get calendar'));
		}

		$xc = new xmlTag('calendar');
		$xc->blog = $blog_uid;
		$xc->CDATA($table);
		$rsp->insertNode($xc);

		return $rsp;
	}
}
