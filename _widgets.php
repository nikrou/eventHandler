<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventHandler, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}
if (version_compare(str_replace("-r","-p",DC_VERSION),'2.2-alpha','<')){return;}

$core->addBehavior('initWidgets',array('eventHandlerAdminWidgets','events'));
$core->addBehavior('initWidgets',array('eventHandlerAdminWidgets','eventsOfPost'));
$core->addBehavior('initWidgets',array('eventHandlerAdminWidgets','postsOfEvent'));
$core->addBehavior('initWidgets',array('eventHandlerAdminWidgets','categories'));
$core->addBehavior('initWidgets',array('eventHandlerAdminWidgets','calendar'));
$core->addBehavior('initWidgets',array('eventHandlerAdminWidgets','map'));

# Admin side of widgets
class eventHandlerAdminWidgets
{	
	public static function events($w)
	{
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
		
		$w->create('ehEvents',__('Events'),array('eventHandlerPublicWidgets','events'));
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
		$w->ehEvents->setting('homeonly',__('Home page only'),1,'check');
	}
	
	public static function eventsOfPost($w)
	{
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
		
		$w->create('ehEventsOfPost',__('Events of an entry'),array('eventHandlerPublicWidgets','eventsOfPost'));
		$w->ehEventsOfPost->setting('title',__('Title:'),__('Related events'),'text');
		$w->ehEventsOfPost->setting('category',__('Category:'),'','combo',$combo_categories);
		$w->ehEventsOfPost->setting('limit',__('Entries limit:'),10);
		$w->ehEventsOfPost->setting('sortby',__('Order by:'),'event_startdt','combo',$combo_sortby);
		$w->ehEventsOfPost->setting('sort',__('Sort:'),'asc','combo',$combo_sort);
		$w->ehEventsOfPost->setting('period',__('Period:'),'notfinished','combo',$combo_period);
	}
	
	public static function postsOfEvent($w)
	{
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
		
		$w->create('ehPostsOfEvent',__('Entries of an event'),array('eventHandlerPublicWidgets','postsOfEvent'));
		$w->ehPostsOfEvent->setting('title',__('Title:'),__('Related entries'),'text');
		$w->ehPostsOfEvent->setting('category',__('Category:'),'','combo',$combo_categories);
		$w->ehPostsOfEvent->setting('limit',__('Entries limit:'),10);
		$w->ehPostsOfEvent->setting('sortby',__('Order by:'),'post_dt','combo',$combo_sortby);
		$w->ehPostsOfEvent->setting('sort',__('Sort:'),'desc','combo',$combo_sort);
	}
	
	public static function categories($w)
	{
		$w->create('ehCategories',__('Events categories'),array('eventHandlerPublicWidgets','categories'));
		$w->ehCategories->setting('title',__('Title:'),__('Events by categories'));
		$w->ehCategories->setting('postcount',__('With events counts'),0,'check');
		$w->ehCategories->setting('pagelink',__('Add link to events page'),1,'check');
		$w->ehCategories->setting('homeonly',__('Home page only'),1,'check');
	}
	
	public static function map($w)
	{
		for($i=3;$i<21;$i++)
		{
			$combo_map_zoom[$i] = $i;
		}
		$combo_map_type = array(
			__('road map') => 'ROADMAP',
			__('satellite') => 'SATELLITE',
			__('hybrid') => 'HYBRID',
			__('terrain') => 'TERRAIN'
		);
		
		$w->create('ehMap',__('Events map'),array('eventHandlerPublicWidgets','map'));
		$w->ehMap->setting('title',__('Title:'),__('Events on map'));
		$w->ehMap->setting('map_zoom',__('Default zoom on map:'),4,'combo',$combo_map_zoom);
		$w->ehMap->setting('map_type',__('Default type of map:'),'ROADMAP','combo',$combo_map_type);
		$w->ehMap->setting('map_width',__('Width of map: (with unit as % or px)'),'100%');
		$w->ehMap->setting('map_height',__('Height of map: (with unit as % or px)'),'250px');
		$w->ehMap->setting('map_info',__('Add tooltips'),0,'check');
		$w->ehMap->setting('pagelink',__('Add link to events page'),1,'check');
		$w->ehMap->setting('homeonly',__('Home page only'),1,'check');
	}

	public static function calendar($w)
	{
		global $core;
		
		$combo_weekstart = array(
			__('Sunday') => '0',
			__('Monday') => '1'
		);
		
		$w->create('ehCalendar',__('Events calendar'),array('eventHandlerPublicWidgets','calendar'));
		$w->ehCalendar->setting('title',__('Title:'),__('Events calendar'),'text');
		$w->ehCalendar->setting('weekstart',__('First day of week:'),'0','combo',$combo_weekstart);
		$w->ehCalendar->setting('startonly',__('Show only start date of events'),1,'check');
		$w->ehCalendar->setting('pagelink',__('Add link to events page'),1,'check');
		$w->ehCalendar->setting('homeonly',__('Home page only'),1,'check');
	}
}

# Public side of widgets
class eventHandlerPublicWidgets
{	
	public static function events($w)
	{
		global $core;

		# Plugin active
		if (!$core->blog->settings->eventHandler->active) return;
		# Home only
		if ($w->homeonly && $core->url->type != 'default') return;
		$params['sql'] = '';
		# Period
		if ($w->period)
		{
			$params['event_period'] = $w->period;
		}
		# Sort field
		$params['order'] = ($w->sortby && in_array($w->sortby,array('post_title','post_dt','event_startdt','event_enddt'))) ? 
			$w->sortby.' ' : 'event_startdt ';
		# Sort order
		$params['order'] .= $w->sort == 'desc' ? 'desc' : 'asc';
		# Rows number
		if ('' !== $w->limit)
		{
			$params['limit'] = abs((integer) $w->limit);
		}
		# No post content
		$params['no_content'] = true;
		# Post type
		$params['post_type'] = 'eventhandler';
		# Selected post only
		if ($w->selectedonly) {	$params['post_selected'] = 1; }
		# Category
		if ($w->category)
		{
			if ($w->category == 'null')
			{
				$params['sql'] .= ' AND P.cat_id IS NULL ';
			}
			elseif (is_numeric($w->category))
			{
				$params['cat_id'] = (integer) $w->category;
			}
			else
			{
				$params['cat_url'] = $w->category;
			}
		}
		# If no paricular category is selected, remove unlisted categories
		else
		{
			$public_hidden_categories = @unserialize($core->blog->settings->eventHandler->public_hidden_categories);
			if (is_array($public_hidden_categories) && !empty($public_hidden_categories))
			{
				foreach($public_hidden_categories AS $k => $cat_id)
				{
					$params['sql'] .= " AND P.cat_id != '$cat_id' ";
				}
			}
		}
		# Get posts
		$eventHandler = new eventHandler($core);
		$rs = $eventHandler->getEvents($params);
		# No result
		if ($rs->isEmpty())
		{
			return;
		}
		# Display
		$res =
		'<div class="eventhandler-events">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		while ($rs->fetch())
		{
			# If same day
			if ($rs->isOnSameDay())	{
				$over_format = __('On %sd from %st to %et');
			}
			else {
				$over_format = __('From %sd, %st to %ed, %et');
			}

			# Format items
			$fsd = dt::dt2str($w->date_format,$rs->event_startdt);
			$fst = dt::dt2str($w->time_format,$rs->event_startdt);
			$fed = dt::dt2str($w->date_format,$rs->event_enddt);
			$fet = dt::dt2str($w->time_format,$rs->event_enddt);

			# Replacement
			$over = str_replace(
				array('%sd','%st','%ed','%et','%%'),
				array($fsd,$fst,$fed,$fet,'%'),
				$over_format
			);
			$title = '<a href="'.$rs->getURL().'" title="'.$over.'">'.
				html::escapeHTML($rs->post_title).'</a>';
			$cat = $w->item_showcat ? ' (<a href="'.$rs->getCategoryURL().
				'" title="'.__('go to this category').'">'.
				html::escapeHTML($rs->cat_title).'</a>)' : '';

			$res .= '<li>'.$title.$cat.'</li>';
		}
		$res .= '</ul>';
		
		if ($w->pagelink)
		{
			$res .= 
			'<p><strong><a href="'.
			$core->blog->url.$core->url->getBase('eventhandler_list').
			'" >'.__('All events').'</a></strong></p>';
		}
		
		$res .= '</div>';

		return $res;
	}
	
	public static function eventsOfPost($w)
	{
		global $core, $_ctx;

		# Plugin active
		if (!$core->blog->settings->eventHandler->active) return;
		# Post page only
		if ($core->url->type != 'post') return;
		$params['sql'] = '';
		# Period
		if ($w->period)
		{
			$params['event_period'] = $w->period;
		}
		# Sort field
		$params['order'] = ($w->sortby && in_array($w->sortby,array('post_title','post_dt','event_startdt','event_enddt'))) ? 
			$w->sortby.' ' : 'event_startdt ';
		# Sort order
		$params['order'] .= $w->sort == 'desc' ? 'desc' : 'asc';
		# Rows number
		if ('' !== $w->limit)
		{
			$params['limit'] = abs((integer) $w->limit);
		}
		# No post content
		$params['no_content'] = true;
		# Post id
		$params['post_id'] = $_ctx->posts->post_id;
		# Event type
		$params['event_type'] = 'eventhandler';
		# Category
		if ($w->category)
		{
			if ($w->category == 'null')
			{
				$params['sql'] .= ' AND P.cat_id IS NULL ';
			}
			elseif (is_numeric($w->category))
			{
				$params['cat_id'] = (integer) $w->category;
			}
			else
			{
				$params['cat_url'] = $w->category;
			}
		}
		# If no paricular category is selected, remove unlisted categories
		else
		{
			$public_hidden_categories = @unserialize($core->blog->settings->eventHandler->public_hidden_categories);
			if (is_array($public_hidden_categories) && !empty($public_hidden_categories))
			{
				foreach($public_hidden_categories AS $k => $cat_id)
				{
					$params['sql'] .= " AND P.cat_id != '$cat_id' ";
				}
			}
		}
		# Get posts
		$eventHandler = new eventHandler($core);
		$rs = $eventHandler->getEventsByPost($params);
		# No result
		if ($rs->isEmpty())
		{
			return;
		}
		# Display
		$res =
		'<div class="eventhandler-eventsofpost">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		while ($rs->fetch())
		{
			# If same day
			if ($rs->isOnSameDay())
			{
				$over_format = __('On %sd from %st to %et');
			}
			else
			{
				$over_format = __('From %sd, %st to %ed, %et');
			}
			
			# Format items
			$fsd = dt::dt2str($core->blog->settings->system->date_format,$rs->event_startdt);
			$fst = dt::dt2str($core->blog->settings->system->time_format,$rs->event_startdt);
			$fed = dt::dt2str($core->blog->settings->system->date_format,$rs->event_enddt);
			$fet = dt::dt2str($core->blog->settings->system->time_format,$rs->event_enddt);
			
			# Replacement
			$over = str_replace(
				array('%sd','%st','%ed','%et',),
				array($fsd,$fst,$fed,$fet),
				$over_format
			);
			$item = html::escapeHTML($rs->post_title);
			
			$res .= '<li><a href="'.$rs->getURL().'" title="'.$over.'">'.$item.'</a></li>';
		}
		$res .= '</ul>';
		
		if ($w->pagelink)
		{
			$res .= 
			'<p><strong><a href="'.
			$core->blog->url.$core->url->getBase('eventhandler_list').
			'" >'.__('All events').'</a></strong></p>';
		}
		
		$res .= '</div>';

		return $res;
	}
	
	public static function postsOfEvent($w)
	{
		global $core, $_ctx;

		# Plugin active
		if (!$core->blog->settings->eventHandler->active) return;
		# Event page only
		if ($core->url->type != 'eventhandler_single') return;
		
		$params['sql'] = '';
		# Sort field
		$params['order'] = ($w->sortby && in_array($w->sortby,array('post_title','post_dt'))) ? 
			$w->sortby.' ' : 'post_dt ';
		# Sort order
		$params['order'] .= $w->sort == 'asc' ? 'asc' : 'desc';
		# Rows number
		if ('' !== $w->limit)
		{
			$params['limit'] = abs((integer) $w->limit);
		}
		# No post content
		$params['no_content'] = true;
		# Event id
		$params['event_id'] = $_ctx->posts->post_id;
		# Event type
		$params['event_type'] = 'eventhandler';
		# Category
		if ($w->category)
		{
			if ($w->category == 'null') {
				$params['sql'] = ' AND P.cat_id IS NULL ';
			} elseif (is_numeric($w->category)) {
				$params['cat_id'] = (integer) $w->category;
			} else {
				$params['cat_url'] = $w->category;
			}
		}
		# Get posts
		$eventHandler = new eventHandler($core);
		$rs = $eventHandler->getPostsByEvent($params);
		# No result
		if ($rs->isEmpty())
		{
			return;
		}
		# Display
		$res =
		'<div class="eventhandler-postsofevent">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= '<li><a href="'.$rs->getURL().'">'.
			html::escapeHTML($rs->post_title).'</a></li>';
		}
		
		$res .= '</ul></div>';
		
		return $res;
	}
	
	public static function categories($w)
	{
		global $core, $_ctx;
		
		if ($w->homeonly && $core->url->type != 'default') return;
		
		$res =
		'<div class="eventhandler-categories">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		
		$rs = $core->blog->getCategories(array('post_type'=>'eventhandler'));
		if ($rs->isEmpty())
		{
			return;
		}
		
		$ref_level = $level = $rs->level-1;
		while ($rs->fetch())
		{
			$class = '';
			if (($core->url->type == 'catevents' && $_ctx->categories instanceof record && $_ctx->categories->cat_id == $rs->cat_id)
			 || ($core->url->type == 'event' && $_ctx->posts instanceof record && $_ctx->posts->cat_id == $rs->cat_id))
			{
				$class = ' class="category-current"';
			}
			
			if ($rs->level > $level)
			{
				$res .= str_repeat('<ul><li'.$class.'>',$rs->level - $level);
			}
			elseif ($rs->level < $level)
			{
				$res .= str_repeat('</li></ul>',-($rs->level - $level));
			}
			
			if ($rs->level <= $level)
			{
				$res .= '</li><li'.$class.'>';
			}
			
			$res .=
			'<a href="'.$core->blog->url.$core->url->getBase('eventhandler_list').
			'/category/'.$rs->cat_url.'">'.
			html::escapeHTML($rs->cat_title).'</a>'.
			($w->postcount ? ' ('.$rs->nb_post.')' : '');
			
			
			$level = $rs->level;
		}
		
		if ($ref_level - $level < 0)
		{
			$res .= str_repeat('</li></ul>',-($ref_level - $level));
		}
		
		if ($w->pagelink)
		{
			$res .= 
			'<p><strong><a href="'.
			$core->blog->url.$core->url->getBase('eventhandler_list').
			'" >'.__('All events').'</a></strong></p>';
		}
		
		$res .= '</div>';
		
		return $res;
	}
	
	public static function map($w)
	{
		global $core;

		# Plugin active
		if (!$core->blog->settings->eventHandler->active) return;
		# Home only
		if ($w->homeonly && $core->url->type != 'default') return;
		
		$params['sql'] = '';
		$params['event_period'] = 'all';
		$params['order'] = 'event_startdt ASC';
		$params['limit'] = 10;
		$params['no_content'] = true;
		$params['post_type'] = 'eventhandler';
		$params['sql'] .= "AND event_latitude != '' ";
		$params['sql'] .= "AND event_longitude != '' ";
		
		$public_hidden_categories = @unserialize($core->blog->settings->eventHandler->public_hidden_categories);
		if (is_array($public_hidden_categories) && !empty($public_hidden_categories))
		{
			foreach($public_hidden_categories AS $k => $cat_id)
			{
				$params['sql'] .= " AND P.cat_id != '$cat_id' ";
			}
		}
		
		# Get posts
		$eventHandler = new eventHandler($core);
		$rs = $eventHandler->getEvents($params);
		# No result
		if ($rs->isEmpty())
		{
			return;
		}
		
		$total_lat = $total_lng = 0;
		$markers = '';
		while ($rs->fetch())
		{
			$total_lat += (float) $rs->event_latitude;
			$total_lng += (float) $rs->event_longitude;
			
			$markers .= $rs->getGmapVEVENT();
		}
		
		$lat = round($total_lat / $rs->count(), 7);
		$lng = round($total_lng / $rs->count(), 7);
		
		# Display
		$res =
		'<div class="eventhandler-map">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		eventHandler::getGmapContent(
			$w->map_width,
			$w->map_height,
			$w->map_type,
			$w->map_zoom,
			((integer) $w->map_info),
			$lat,
			$lng,
			$markers
		);
		
		if ($w->pagelink)
		{
			$res .= 
			'<p><strong><a href="'.
			$core->blog->url.$core->url->getBase('eventhandler_list').
			'" >'.__('All events').'</a></strong></p>';
		}
		
		$res .= '</div>';

		return $res;
	}
	
	public static function calendar($w)
	{
		global $core, $_ctx;
		
		if ($w->homeonly && $core->url->type != 'default') return;
		
		$year = date('Y');
		$month = date('m');
		
		if ($_ctx->exists('event_params') && !empty($_ctx->event_params['event_start_month']))
		{
			$year = $_ctx->event_params['event_start_year'];
			$month = $_ctx->event_params['event_start_month'];
		}
		elseif ($_ctx->exists('event_params') && !empty($_ctx->event_params['event_startdt']))
		{
			$year = date('Y',strtotime($_ctx->event_params['event_startdt']));
			$month = date('m',strtotime($_ctx->event_params['event_startdt']));
		}
		
		# Generic calendar Object
		$calendar = eventHandlerCalendar::getArray($year,$month,$w->weekstart);
		
		return 
		'<div class="eventhandler-calendar">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		# Events calendar
		eventHandlerCalendar::parseArray($calendar,$w->weekstart,$w->startonly).
		($w->pagelink ?
			'<p><strong><a href="'.
			$core->blog->url.$core->url->getBase('eventhandler_list').
			'" >'.__('All events').'</a></strong></p>' : ''
		).
		'</div>';
	}
}
?>