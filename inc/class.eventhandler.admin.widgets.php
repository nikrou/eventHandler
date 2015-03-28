<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2015 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
#
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# Admin side of widgets
class eventHandlerAdminWidgets
{
	public static function events($w) {
		global $core;

		$rs = $core->blog->getCategories(array('post_type'=>'eventhandler'));
		$combo_categories = array('&nbsp;' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$combo_categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.
                              html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$combo_sortby = array(
			__('Date') => 'post_dt',
			__('Title') => 'post_title',
			__('Start date') => 'event_startdt',
			__('End date') => 'event_enddt'
		);
		$combo_sort = array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'
		);
		$combo_period = array(
			__('All periods') => '',
			__('Not started') => 'scheduled',
			__('Started') => 'started',
			__('Finished') => 'finished',
			__('Not finished') => 'notfinished',
			__('Ongoing') => 'ongoing',
			__('Outgoing') => 'outgoing'
		);
		$combo_homeonly = array(
			__('All pages') => 0,
			__('Home page only') => 1,
			__('Except on home page') => 2
		);

		$w->create('ehEvents',__('EventHandler: events'),array('eventHandlerPublicWidgets','events'),
                   null,
                   __('Next events'));
		$w->ehEvents->setting('title',__('Title:'),__('Next events'),'text');
		$w->ehEvents->setting('category',__('Category:'),'','combo',$combo_categories);
		$w->ehEvents->setting('limit',__('Entries limit:'),10);
		$w->ehEvents->setting('sortby',__('Order by:'),'event_startdt','combo',$combo_sortby);
		$w->ehEvents->setting('sort',__('Sort:'),'asc','combo',$combo_sort);
		$w->ehEvents->setting('selectedonly',__('Selected entries only'),0,'check');
		$w->ehEvents->setting('period',__('Period:'),'scheduled','combo',$combo_period);
		$w->ehEvents->setting('date_format',__('Date format of events:'),__('%A, %B %e %Y'),'text');
		$w->ehEvents->setting('time_format',__('Time format of events:'),__('%H:%M'),'text');
		$w->ehEvents->setting('item_showcat',__('Show category'),1,'check');
		$w->ehEvents->setting('pagelink',__('Add link to events page'),1,'check');
		$w->ehEvents->setting('homeonly',__('Display on:'),0,'combo',$combo_homeonly);
		$w->ehEvents->setting('content_only',__('Content only'),0,'check');
		$w->ehEvents->setting('class',__('CSS class:'),'');
		$w->ehEvents->setting('offline',__('Offline'),0,'check');
	}

	public static function eventsOfPost($w) {
		global $core;

		$rs = $core->blog->getCategories(array('post_type'=>'eventhandler'));
		$combo_categories = array('&nbsp;' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$combo_categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.
                              html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$combo_sortby = array(
			__('Date') => 'post_dt',
			__('Title') => 'post_title',
			__('Start date') => 'event_startdt',
			__('End date') => 'event_enddt'
		);
		$combo_sort = array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'
		);
		$combo_period = array(
			__('All periods') => '',
			__('Not started') => 'scheduled',
			__('Started') => 'started',
			__('Finished') => 'finished',
			__('Not finished') => 'notfinished',
			__('Ongoing') => 'ongoing',
			__('Outgoing') => 'outgoing'
		);

		$w->create('ehEventsOfPost',
                   __('EventHandler: events of an entry'),
                   array('eventHandlerPublicWidgets','eventsOfPost'),
                   null,
                   __('Related events')
        );
		$w->ehEventsOfPost->setting('title',__('Title:'),__('Related events'),'text');
		$w->ehEventsOfPost->setting('category',__('Category:'),'','combo',$combo_categories);
		$w->ehEventsOfPost->setting('limit',__('Entries limit:'),10);
		$w->ehEventsOfPost->setting('sortby',__('Order by:'),'event_startdt','combo',$combo_sortby);
		$w->ehEventsOfPost->setting('sort',__('Sort:'),'asc','combo',$combo_sort);
		$w->ehEventsOfPost->setting('period',__('Period:'),'notfinished','combo',$combo_period);
		$w->ehEventsOfPost->setting('content_only',__('Content only'),0,'check');
		$w->ehEventsOfPost->setting('class',__('CSS class:'),'');
		$w->ehEventsOfPost->setting('offline',__('Offline'),0,'check');
	}

	public static function postsOfEvent($w) {
		global $core;

		$rs = $core->blog->getCategories(array('post_type'=>'post'));
		$combo_categories = array('&nbsp;' => '', __('Uncategorized') => 'null');
		while ($rs->fetch()) {
			$combo_categories[str_repeat('&nbsp;&nbsp;',$rs->level-1).'&bull; '.
                              html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		}
		$combo_sortby = array(
			__('Date') => 'post_dt',
			__('Title') => 'post_title'
		);
		$combo_sort = array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'
		);

		$w->create('ehPostsOfEvent',
                   __('EventHandler: entries of an event'),
                   array('eventHandlerPublicWidgets','postsOfEvent'),
                   null,
                   __('Related entries')
        );
		$w->ehPostsOfEvent->setting('title',__('Title:'),__('Related entries'),'text');
		$w->ehPostsOfEvent->setting('category',__('Category:'),'','combo',$combo_categories);
		$w->ehPostsOfEvent->setting('limit',__('Entries limit:'),10);
		$w->ehPostsOfEvent->setting('sortby',__('Order by:'),'post_dt','combo',$combo_sortby);
		$w->ehPostsOfEvent->setting('sort',__('Sort:'),'desc','combo',$combo_sort);
		$w->ehPostsOfEvent->setting('content_only',__('Content only'),0,'check');
		$w->ehPostsOfEvent->setting('class',__('CSS class:'),'');
		$w->ehPostsOfEvent->setting('offline',__('Offline'),0,'check');
	}

	public static function categories($w) {
		$combo_homeonly = array(
			__('All pages') => 0,
			__('Home page only') => 1,
			__('Except on home page') => 2
		);

		$w->create('ehCategories',
                   __('EventHandler: events categories'),
                   array('eventHandlerPublicWidgets','categories'),
                   null,
                   __('Events by categories')
        );
		$w->ehCategories->setting('title',__('Title:'),__('Events by categories'));
		$w->ehCategories->setting('postcount',__('With events counts'),0,'check');
		$w->ehCategories->setting('pagelink',__('Add link to events page'),1,'check');
		$w->ehCategories->setting('homeonly',__('Display on:'),0,'combo',$combo_homeonly);
		$w->ehCategories->setting('content_only',__('Content only'),0,'check');
		$w->ehCategories->setting('class',__('CSS class:'),'');
		$w->ehCategories->setting('offline',__('Offline'),0,'check');
	}

	public static function map($w) {
		for ($i=3;$i<21;$i++) {
			$combo_map_zoom[$i] = $i;
		}
		$combo_map_type = array(
			__('road map') => 'ROADMAP',
			__('satellite') => 'SATELLITE',
			__('hybrid') => 'HYBRID',
			__('terrain') => 'TERRAIN'
		);
		$combo_sortby = array(
			__('Date') => 'post_dt',
			__('Title') => 'post_title',
			__('Start date') => 'event_startdt',
			__('End date') => 'event_enddt'
		);
		$combo_sort = array(
			__('Ascending') => 'asc',
			__('Descending') => 'desc'
		);
		$combo_period = array(
			__('All periods') => '',
			__('Not started') => 'scheduled',
			__('Started') => 'started',
			__('Finished') => 'finished',
			__('Not finished') => 'notfinished',
			__('Ongoing') => 'ongoing',
			__('Outgoing') => 'outgoing'
		);
		$combo_homeonly = array(
			__('All pages') => 0,
			__('Home page only') => 1,
			__('Except on home page') => 2
		);

		$w->create('ehMap',
                   __('EventHandler: events map'),
                   array('eventHandlerPublicWidgets','map'),
                   null,
                   __('Events on map')
        );
		$w->ehMap->setting('title',__('Title:'),__('Events on map'));
		$w->ehMap->setting('map_zoom',__('Default zoom on map:'),4,'combo',$combo_map_zoom);
		$w->ehMap->setting('map_type',__('Default type of map:'),'ROADMAP','combo',$combo_map_type);
		$w->ehMap->setting('map_width',__('Width of map (with unit as % or px):'),'100%');
		$w->ehMap->setting('map_height',__('Height of map (with unit as % or px):'),'250px');
		$w->ehMap->setting('map_info',__('Add tooltips'),0,'check');
		$w->ehMap->setting('sortby',__('Order by:'),'event_startdt','combo',$combo_sortby);
		$w->ehMap->setting('sort',__('Sort:'),'asc','combo',$combo_sort);
		$w->ehMap->setting('period',__('Period:'),'scheduled','combo',$combo_period);
		$w->ehMap->setting('pagelink',__('Add link to events page'),1,'check');
		$w->ehMap->setting('homeonly',__('Display on:'),0,'combo',$combo_homeonly);
		$w->ehMap->setting('content_only',__('Content only'),0,'check');
		$w->ehMap->setting('class',__('CSS class:'),'');
		$w->ehMap->setting('offline',__('Offline'),0,'check');
	}

	public static function calendar($w) {
		$combo_weekstart = array(
			__('Sunday') => '0',
			__('Monday') => '1'
		);
		$combo_homeonly = array(
			__('All pages') => 0,
			__('Home page only') => 1,
			__('Except on home page') => 2
		);

		$w->create('ehCalendar',
                   __('EventHandler: events calendar'),
                   array('eventHandlerPublicWidgets','calendar'),
                   null,
                   __('Events calendar')
        );
		$w->ehCalendar->setting('title',__('Title:'),__('Events calendar'),'text');
		$w->ehCalendar->setting('weekstart',__('First day of week:'),'0','combo',$combo_weekstart);
		$w->ehCalendar->setting('startonly',__('Show only start date of events'),1,'check');
		$w->ehCalendar->setting('pagelink',__('Add link to events page'),1,'check');
		$w->ehCalendar->setting('homeonly',__('Display on:'),0,'combo',$combo_homeonly);
		$w->ehCalendar->setting('content_only',__('Content only'),0,'check');
		$w->ehCalendar->setting('class',__('CSS class:'),'');
		$w->ehCalendar->setting('offline',__('Offline'),0,'check');
	}
}
