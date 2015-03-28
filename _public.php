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

if (!defined('DC_RC_PATH')){return;}
if (version_compare(str_replace("-r","-p",DC_VERSION),'2.5-alpha','<')){return;}

# set ns
$core->blog->settings->addNamespace('eventHandler');
# Localisation
__('scheduled');
__('ongoing');
__('finished');

# Load _wigdets.php
if ($core->blog->settings->eventHandler->active) {
    include_once(__DIR__.'/_widgets.php');
}

# Public behaviors
$core->addBehavior('publicHeadContent',array('publicEventHandler','publicHeadContent'));
$core->addBehavior('publicBeforeDocument',array('publicEventHandler','publicBeforeDocument'));
$core->addBehavior('publicEntryBeforeContent',array('publicEventHandler','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('publicEventHandler','publicEntryAfterContent'));

# Missing values
$core->tpl->addValue('BlogTimezone',array('tplEventHandler','BlogTimezone'));

# Page of events
$core->tpl->addBlock('EventsIf',array('tplEventHandler','EventsIf'));
$core->tpl->addValue('EventsMenuPeriod',array('tplEventHandler','EventsMenuPeriod'));
$core->tpl->addValue('EventsMenuSortOrder',array('tplEventHandler','EventsMenuSortOrder'));
$core->tpl->addValue('EventsFeedURL',array('tplEventHandler','EventsFeedURL'));
$core->tpl->addValue('EventsURL',array('tplEventHandler','EventsURL'));
$core->tpl->addValue('EventsPeriod',array('tplEventHandler','EventsPeriod'));
$core->tpl->addValue('EventsInterval',array('tplEventHandler','EventsInterval'));

$core->tpl->addBlock('EventsEntries',array('tplEventHandler','EventsEntries'));
$core->tpl->addBlock('EventsPagination',array('tplEventHandler','EventsPagination'));
$core->tpl->addBlock('EventsEntryIf',array('tplEventHandler','EventsEntryIf'));
$core->tpl->addBlock('EventsDateHeader',array('tplEventHandler','EventsDateHeader'));
$core->tpl->addBlock('EventsDateFooter',array('tplEventHandler','EventsDateFooter'));
$core->tpl->addValue('EventsEntryDate',array('tplEventHandler','EventsEntryDate'));
$core->tpl->addValue('EventsEntryTime',array('tplEventHandler','EventsEntryTime'));
$core->tpl->addValue('EventsEntryCategoryURL',array('tplEventHandler','EventsEntryCategoryURL'));
$core->tpl->addValue('EventsEntryAddress',array('tplEventHandler','EventsEntryAddress'));
$core->tpl->addValue('EventsEntryLatitude',array('tplEventHandler','EventsEntryLatitude'));
$core->tpl->addValue('EventsEntryLongitude',array('tplEventHandler','EventsEntryLongitude'));
$core->tpl->addValue('EventsEntryDuration',array('tplEventHandler','EventsEntryDuration'));
$core->tpl->addValue('EventsEntryPeriod',array('tplEventHandler','EventsEntryPeriod'));
$core->tpl->addValue('EventsEntryMap',array('tplEventHandler','EventsEntryMap'));

# Events of a post
$core->tpl->addBlock('EventsOfPost',array('tplEventHandler','EventsOfPost'));
$core->tpl->addBlock('EventsOfPostHeader',array('tplEventHandler','EventsOfPostHeader'));
$core->tpl->addBlock('EventsOfPostFooter',array('tplEventHandler','EventsOfPostFooter'));
$core->tpl->addBlock('EventOfPostIf',array('tplEventHandler','EventOfPostIf'));
$core->tpl->addValue('EventOfPostURL',array('tplEventHandler','EventOfPostURL'));
$core->tpl->addValue('EventOfPostTitle',array('tplEventHandler','EventOfPostTitle'));
$core->tpl->addValue('EventOfPostDate',array('tplEventHandler','EventOfPostDate'));
$core->tpl->addValue('EventOfPostTime',array('tplEventHandler','EventOfPostTime'));
$core->tpl->addValue('EventOfPostAuthorCommonName',array('tplEventHandler','EventOfPostAuthorCommonName'));
$core->tpl->addValue('EventOfPostAuthorLink',array('tplEventHandler','EventOfPostAuthorLink'));
$core->tpl->addValue('EventOfPostCategory',array('tplEventHandler','EventOfPostCategory'));
$core->tpl->addValue('EventOfPostCategoryURL',array('tplEventHandler','EventOfPostCategoryURL'));
$core->tpl->addValue('EventOfPostAddress',array('tplEventHandler','EventOfPostAddress'));
$core->tpl->addValue('EventOfPostDuration',array('tplEventHandler','EventOfPostDuration'));
$core->tpl->addValue('EventOfPostPeriod',array('tplEventHandler','EventOfPostPeriod'));

# Posts of an event
$core->tpl->addBlock('PostsOfEvent',array('tplEventHandler','PostsOfEvent'));
$core->tpl->addBlock('PostsOfEventHeader',array('tplEventHandler','PostsOfEventHeader'));
$core->tpl->addBlock('PostsOfEventFooter',array('tplEventHandler','PostsOfEventFooter'));
$core->tpl->addBlock('PostOfEventIf',array('tplEventHandler','PostOfEventIf'));
$core->tpl->addValue('PostOfEventURL',array('tplEventHandler','PostOfEventURL'));
$core->tpl->addValue('PostOfEventTitle',array('tplEventHandler','PostOfEventTitle'));
$core->tpl->addValue('PostOfEventDate',array('tplEventHandler','PostOfEventDate'));
$core->tpl->addValue('PostOfEventTime',array('tplEventHandler','PostOfEventTime'));
$core->tpl->addValue('PostOfEventAuthorCommonName',array('tplEventHandler','PostOfEventAuthorCommonName'));
$core->tpl->addValue('PostOfEventAuthorLink',array('tplEventHandler','PostOfEventAuthorLink'));
$core->tpl->addValue('PostOfEventCategory',array('tplEventHandler','PostOfEventCategory'));
$core->tpl->addValue('PostOfEventCategoryURL',array('tplEventHandler','PostOfEventCategoryURL'));

# Public behaviors
class publicEventHandler
{
	# Add some css and js to page
	public static function publicHeadContent($core)
	{
		if (!$core->blog->settings->eventHandler->active){return;}

		$public_map_zoom = abs((integer) $core->blog->settings->eventHandler->public_map_zoom);
		if (!$public_map_zoom) $public_map_zoom = 9;

		echo
		"\n<!-- JS for eventHandler maps--> \n".
		"<script type=\"text/javascript\" src=\"".
			$core->blog->getQmarkURL().'pf=eventHandler/js/event-public-map.js">'.
		"</script> \n".

		"\n<!-- JS for eventHandler calendar--> \n".
		"<script type=\"text/javascript\" src=\"".
			$core->blog->getQmarkURL().'pf=eventHandler/js/event-public-cal.js">'.
		"</script> \n".
		"<script type=\"text/javascript\"> \n".
		"//<![CDATA[\n".
		" \$(function(){if(!document.getElementById){return;} \n".
		"  \$.fn.eventHandlerCalendar.defaults.service_url = '".
		html::escapeJS($core->blog->url.$core->url->getBase('eventhandler_pubrest').'/')."'; \n".
		"  \$.fn.eventHandlerCalendar.defaults.service_func = '".
		html::escapeJS('eventHandlerCalendar')."'; \n".
		"  \$.fn.eventHandlerCalendar.defaults.blog_uid = '".
		html::escapeJS($core->blog->uid)."'; \n".
		"  \$.fn.eventHandlerCalendar.defaults.msg_wait = '".
		html::escapeJS(__('Please wait'))."'; \n".
		"  \$('.calendar-array').eventHandlerCalendar(); \n".
		" })\n".
		"//]]>\n".
		"</script>\n";

		$extra_css = $core->blog->settings->eventHandler->public_extra_css;
		if ($extra_css) {
			echo
			"\n<!-- user css for eventHandler --> \n".
			'<style type="text/css">'."\n".
			html::escapeHTML($extra_css)."\n".
			"\n</style>\n";
		}
	}

	# Before entry content
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'before');
	}

	# After entry content
	public static function publicEntryAfterContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'after');
	}

	# Add list of events / posts on entries
	protected static function publicEntryContent($core,$_ctx,$place)
	{
		$s = $core->blog->settings->eventHandler;

		if (!$s->active || $s->public_posts_of_event_place == '') return;

		$default_url_type = array(
			'eventhandler_list',
			'eventhandler_single',
			'eventhandler_preview'
		);

		# List of posts related to a event
		if (in_array($core->url->type,$default_url_type))
		{
			if ($s->public_posts_of_event_place != $place) return;

			echo $core->tpl->getData('eventhandler-postsofevent.html');
		}
		# List of events related to a post
		else
		{
			if ($s->public_events_of_post_place != $place) return;

			echo $core->tpl->getData('eventhandler-eventsofpost.html');
		}
	}
}

# URL handler
class urlEventHandler extends dcUrlHandlers
{

	# Call service from public for ajax request
	public static function eventService($args)
	{
		global $core;
		$core->rest->addFunction('eventHandlerCalendar',
			array('eventHandlerPublicRest','calendar'));
		$core->rest->serve();
		exit;
	}

	# Single event page
	public static function eventSingle($args)
	{
		global $core, $_ctx;

		if ($args == ''
		 || !$_ctx->preview && !$core->blog->settings->eventHandler->active)
		{
			self::p404();
			return;
		}
		else
		{
			$is_ical = self::isIcalDocument($args);
			$is_hcal = self::isHcalDocument($args);
			$is_gmap = self::isGmapDocument($args);

			$core->blog->withoutPassword(false);

			$params = new ArrayObject();
			$params['post_type'] = 'eventhandler';
			$params['post_url'] = $args;

			$_ctx->eventHandler = new eventHandler($core);
			$_ctx->posts = $_ctx->eventHandler->getEvents($params);

			$_ctx->comment_preview = new ArrayObject();
			$_ctx->comment_preview['content'] = '';
			$_ctx->comment_preview['rawcontent'] = '';
			$_ctx->comment_preview['name'] = '';
			$_ctx->comment_preview['mail'] = '';
			$_ctx->comment_preview['site'] = '';
			$_ctx->comment_preview['preview'] = false;
			$_ctx->comment_preview['remember'] = false;

			$core->blog->withoutPassword(true);


			if ($_ctx->posts->isEmpty())
			{
				# The specified page does not exist.
				self::p404();
			}
			else
			{
				$post_id = $_ctx->posts->post_id;
				$post_password = $_ctx->posts->post_password;

				# Password protected entry
				if ($post_password != '' && !$_ctx->preview)
				{
					# Get passwords cookie
					if (isset($_COOKIE['dc_passwd'])) {
						$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
					} else {
						$pwd_cookie = array();
					}

					# Check for match
					if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
					|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
					{
						$pwd_cookie[$post_id] = $post_password;
						setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
					}
					else
					{
						self::serveDocument('password-form.html','text/html',false);
						return;
					}
				}

				if ($is_ical)
				{
					self::serveIcalDocument($_ctx->posts,$args);
				}
				elseif ($is_hcal)
				{
					self::serveHcalDocument($_ctx->posts,$args);
				}
				elseif ($is_gmap)
				{
					self::serveGmapDocument($_ctx->posts,$args);
				}
				else
				{
					self::serveDocument('eventhandler-single.html');
				}
			}
		}
		return;
	}

	# Preview single event from admin side
	public static function eventPreview($args)
	{
		global $core, $_ctx;

		if (!preg_match('#^(.+?)/([0-9a-z]{40})/(.+?)$#',$args,$m)) {
			# The specified Preview URL is malformed.
			self::p404();
		}
		else
		{
			$user_id = $m[1];
			$user_key = $m[2];
			$post_url = $m[3];
			if (!$core->auth->checkUser($user_id,null,$user_key)) {
				# The user has no access to the entry.
				self::p404();
			}
			else
			{
				$_ctx->preview = true;
				self::eventSingle($post_url);
			}
		}
	}

	# Multiple events page
	public static function eventList($args)
	{
		global $core, $_ctx;

		$n = self::getPageNumber($args);
		$is_ical = self::isIcalDocument($args);
		$is_hcal = self::isHcalDocument($args);
		$is_gmap = self::isGmapDocument($args);

		$_ctx->event_params = self::getEventsParams($args);

		if ($n)
		{
			$GLOBALS['_page_number'] = $n;
		}

		# If it is ical do all job here
		if ($is_hcal || $is_ical || $is_gmap)
		{
			$params = array();
			// force limit on gmap
			if ($is_gmap)
			{
				$params['limit'] = array(0,30);
			}
			else
			{
				$pn = $n ? $n : 1;
				$nbppf = $core->blog->settings->system->nb_post_per_feed;
				$params['limit'] = array((($pn-1)*$nbppf),$nbppf);
			}
			if ($_ctx->exists("categories"))
			{
				$params['cat_id'] = $_ctx->categories->cat_id;
			}
			$params = array_merge($params,$_ctx->event_params);

			$eventHandler = new eventHandler($core);
			$rs = $eventHandler->getEvents($params);

			if ($is_ical)
			{
				self::serveIcalDocument($rs,$args);
			}
			elseif ($is_hcal)
			{
				self::serveHcalDocument($rs,$args);
			}
			elseif ($is_gmap)
			{
				self::serveGmapDocument($rs,$args);
			}
		}
		# Else serve normal document
		else
		{
			self::serveDocument('eventhandler-list.html');
		}
		return;
	}

	# Classic feed
	public static function eventFeed($args)
	{
		$type = null;
		$cat_url = false;
		$params = array();
		$subtitle = '';

		$mime = 'application/xml';

		global $core, $_ctx;

		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$args,$m))
		{
			$params['lang'] = $m[1];
			$args = $m[3];

			$_ctx->langs = $core->blog->getLangs($params);

			if ($_ctx->langs->isEmpty())
			{
				# The specified language does not exist.
				self::p404();
				return;
			}
			else
			{
				$_ctx->cur_lang = $m[1];
			}
		}

		if (preg_match('#^rss2/xslt$#',$args,$m))
		{
			# RSS XSLT stylesheet
			self::serveDocument('rss2.xsl','text/xml');
			return;
		}
		elseif (preg_match('#^(?:category/(.+)/)?(atom|rss2)$#',$args,$m))
		{
			# All posts or comments feed
			$type = $m[2];
			if (!empty($m[1]))
			{
				$cat_url = $m[1];
			}
		}
		else
		{
			# The specified Feed URL is malformed.
			self::p404();
			return;
		}

		if ($cat_url)
		{
			$params['cat_url'] = $cat_url;
			$params['post_type'] = 'eventhandler';
			$_ctx->categories = $core->blog->getCategories($params);

			if ($_ctx->categories->isEmpty())
			{
				# The specified category does no exist.
				self::p404();
				return;
			}

			$subtitle = ' - '.$_ctx->categories->cat_title;
		}

		$tpl = 'eventhandler-'.$type.'.xml';

		if ($type == 'atom')
		{
			$mime = 'application/atom+xml';
		}

		$_ctx->nb_entry_per_page = $core->blog->settings->system->nb_post_per_feed;
		$_ctx->short_feed_items = $core->blog->settings->system->short_feed_items;
		$_ctx->feed_subtitle = $subtitle;

		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		self::serveDocument($tpl,$mime);
		if (!$cat_url)
		{
			$core->blog->publishScheduledEntries();
		}
	}

	# Parse URI for multiple events page
	public static function getEventsParams($args)
	{
		global $core, $_ctx;

		$params = array();
		$params['post_type'] = 'eventhandler';

		# Know period
		$default_period_list = array(
			'all',
			'ongoing',
			'outgoing',
			'scheduled',
			'started',
			'notfinished',
			'finished'
		);
		# Know order
		$default_order_list = array(
			'title' => 'LOWER(post_title)',
			'selected' => 'post_selected',
			'author' => 'LOWER(user_id)',
			'date' => 'post_dt',
			'startdt' => 'event_startdt',
			'enddt' =>'event_enddt'
		);

		# Test URI
		if (!preg_match('#^'.
			'((/category/([^/]+))|)'.
			'('.
			 '(/('.implode('|',$default_period_list).'))|'. // period
			 '(/(on|in|of)/([0-9]{4})(/([0-9]{1,2})|)(/([0-9]{1,2})|)(/([0-9]{4})(/([0-9]{1,2})|)(/([0-9]{1,2})|)|))|'. // interval
			')'.
			'(/('.implode('|',array_keys($default_order_list)).')(/(asc|desc)|)|)'. // order
			'(/ical.ics|/hcal.html|/gmap|/|)$#i',$args,$m))
		{
			self::p404();
			return;
		}

		# Get category
		if (!empty($m[3]))
		{
			$cat_params['cat_url'] = $m[3];
			$cat_params['post_type'] = 'eventhandler';
			$_ctx->categories = $core->blog->getCategories($cat_params);

			if ($_ctx->categories->isEmpty())
			{
				# The specified category does no exist.
				self::p404();
				return;
			}
		}

		# Get period
		if (!empty($m[6]))
		{
			$params['event_period'] = $m[6];
		}
		# Get interval
		elseif (!empty($m[8]))
		{
			$params['event_interval'] = $m[8];

			# Make start date
			if (!empty($m[13]))
			{
				$start =  date('Y-m-d 00:00:00',mktime(0,0,0,$m[11],$m[13],$m[9]));
				$end =  date('Y-m-d 00:00:00',mktime(0,0,0,$m[11],($m[13]+1),$m[9]));
			}
			elseif (!empty($m[11]))
			{
				$start = date('Y-m-d 00:00:00',mktime(0,0,0,$m[11],1,$m[9]));
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,($m[11]+1),1,$m[9]));
			}
			elseif (!empty($m[9]))
			{
				$start = date('Y-m-d 00:00:00',mktime(0,0,0,1,1,$m[9]));
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,1,1,($m[9]+1)));
			}
			# Make end date
			if (!empty($m[19]))
			{
				$end =  date('Y-m-d 00:00:00',mktime(0,0,0,$m[17],$m[19],$m[15]));
			}
			elseif (!empty($m[17]))
			{
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,$m[17],1,$m[15]));
			}
			elseif (!empty($m[15]))
			{
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,1,1,$m[15]));
			}
			# Make interval
			if ($m[8] == 'on')
			{
				$params['event_period'] = 'ongoing';
				$params['event_startdt'] = $end;
				$params['event_enddt'] = $start;
			}
			elseif($m[8] == 'in')
			{
				$params['event_period'] = 'ongoing';
				$params['event_startdt'] = $start;
				$params['event_enddt'] = $end;
			}
			else
			{
				if (!empty($m[9]))
				{
					$params['event_start_year'] = $m[9];
				}
				if (!empty($m[11]))
				{
					$params['event_start_month'] = $m[11];
				}
				if (!empty($m[13]))
				{
					$params['event_start_day'] = $m[13];
				}
			}
		}
		else
		{
			$params['event_period'] = 'scheduled'; // default
		}
		# Get order
		$params['order'] = 'event_startdt ASC'; // default
		if (!empty($m[21]))
		{
			$sortorder = 'ASC';
			if (!empty($m[23]))
			{
				$sortorder = strtoupper($m[23]);
			}
			$params['order'] = $default_order_list[$m[21]].' '.$sortorder;
		}

		return $params;
	}

	# Test if request url is a ical
	protected static function isIcalDocument(&$args)
	{
		if (preg_match('#/ical\.ics$#',$args,$m))
		{
			$args = preg_replace('#/ical\.ics$#','',$args);
			return true;
		}
		return false;
	}

	# Test if request url is a hcal
	protected static function isHcalDocument(&$args)
	{
		if (preg_match('#/hcal\.html$#',$args,$m))
		{
			$args = preg_replace('#/hcal\.html$#','',$args);
			return true;
		}
		return false;
	}

	# Test if request url is a gmap
	protected static function isGmapDocument(&$args)
	{
		if (preg_match('#/gmap$#',$args,$m))
		{
			$args = preg_replace('#/gmap$#','',$args);
			return true;
		}
		return false;
	}

	# Serve special ical document
	public static function serveIcalDocument($rs,$x_dc_folder='')
	{
		global $core;

		if ($rs->isEmpty())
		{
			self::p404();
			return;
		}

		$res =
		"BEGIN:VCALENDAR\r\n".
		"PRODID:-//eventHandler for Dotclear//eventHandler 1.0-alpha7//EN\r\n".
		"VERSION:2.0\r\n".
		"METHOD:PUBLISH\r\n".
		"CALSCALE:GREGORIAN\r\n".
		implode("\r\n ",str_split(trim("X-DC-BLOGNAME:".$core->blog->name),70))."\r\n";

		if ($x_dc_folder)
		{
			$res .=
			implode("\r\n ",str_split(trim("X-DC-FOLDER:".$x_dc_folder),70))."\r\n";
		}

		while($rs->fetch())
		{
			# See lib.eventhandler.rs.extension.php
			$res .= $rs->getIcalVEVENT();
		}

		$res .= "END:VCALENDAR\r\n";

		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Length: '.strlen($res));
		header('Content-Disposition: attachment; filename="events.ics"');
		echo $res;
		exit;
	}

	# Serve special hcal document
	public static function serveHcalDocument($rs,$x_dc_folder='')
	{
		global $core;

		if ($rs->isEmpty())
		{
			self::p404();
			return;
		}

		$res =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n".
		'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n".
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.
		$core->blog->settings->system->lang.'" lang="'.$core->blog->settings->system->lang.'">'."\n".
		'<head>'."\n".
		'<title>'.html::escapeHTML($core->blog->name).' - '.__('Events').'</title>'."\n".
		'<style type="text/css" media="screen">'."\n".
		'@import url('.$core->blog->getQmarkURL().
		'pf=eventHandler/default-templates/event-hcalendar.css);'."\n".
		'</style>'."\n".
		'</head>'."\n".
		'<body>'."\n".
		'<div id="page">'."\n".
		'<div id="top">'."\n".
		'<h1><a href="'.$core->blog->url.'">'.html::escapeHTML($core->blog->name).
		' - '.__('Events').'</a></h1>'."\n";

		if ($x_dc_folder)
		{
			$res .= '<p>'.__('Directory:').' <a href="'.
			$core->blog->url.$core->url->getBase('eventhandler_list').$x_dc_folder.'">'.
			$x_dc_folder.'</a></p>'."\n";
		}

		$res .=
		'</div>';

		while($rs->fetch())
		{
			# See lib.eventhandler.rs.extension.php
			$res .=
			'<div id="items">'."\n".
			$rs->getHcalVEVENT().
			'</div>'."\n";
		}

		$res .=
		'<div id="footer">'."\n".
		'<p>'.__('This page is powered by Dotclear and eventHandler').'</p>'."\n".
		'</div>'."\n".
		'</div>'."\n".
		'</body></html>';

		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Length: '.strlen($res));
		echo $res;
		exit;
	}

	# Serve special gmap document
	public static function serveGmapDocument($rs,$x_dc_folder='')
	{
		global $core;

		$res =
		'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n".
		'"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n".
		'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.
		$core->blog->settings->system->lang.'" lang="'.$core->blog->settings->system->lang.'">'."\n".
		'<head>'."\n".
		'<title>'.html::escapeHTML($core->blog->name).' - '.__('Events').'</title>'."\n".
		'<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />'."\n".
		'<script type="text/javascript" src="'.$core->blog->settings->system->themes_url.
		"/".$core->blog->settings->system->theme.'/../default/js/jquery.js"></script>'."\n".
		'<script type="text/javascript" src="'.$core->blog->settings->system->themes_url.
		"/".$core->blog->settings->system->theme.'/../default/js/jquery.cookie.js"></script>'."\n".
		"<script type=\"text/javascript\" src=\"".$core->blog->getQmarkURL().
		'pf=eventHandler/js/event-public-map.js"></script>'."\n".
		'<style type="text/css">'.
		'html { height: 100%; } body { height: 100%; margin: 0px; padding: 0px; } '.
		'.event-gmap, .event-gmap-place { height: 100%; } h2 { margin: 2em;}</style>'."\n".
		'</head>'.
		'<body>';

		if ($rs->count()) {
			$total_lat = $total_lng = 0;
			$markers = '';
			while($rs->fetch())
			{
				$total_lat += (float) $rs->event_latitude;
				$total_lng += (float) $rs->event_longitude;
				$markers .= $rs->getGmapVEVENT();
			}
			$lat = round($total_lat / $rs->count(), 7);
			$lng = round($total_lng / $rs->count(), 7);

			$res .= eventHandler::getGmapContent(
				'',
				'',
				$core->blog->settings->eventHandler->public_map_type,
				2,
				1,
				$lat,
				$lng,
				$markers
			);
		}
		else {
			$res .= '<h2>'.__("There's no event at this time.").'</h2>';
		}

		$res .=
		'</body>'.
		'</html>';

		header('Content-Type: text/html; charset=UTF-8');
		header('Content-Length: '.strlen($res));
		echo $res;
		exit;
	}
}

# Template values
class tplEventHandler
{
	#
	# Missing values
	#

	public static function BlogTimezone($a)
	{
		return self::tplValue($a,'$core->blog->settings->system->blog_timezone');
	}

	#
	# Events page
	#

	# URL of page of events list
	public static function EventsURL($a)
	{
		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_list")');
	}

	# Feed Url
	public static function EventsFeedURL($a)
	{
		$type = !empty($a['type']) ? $a['type'] : 'atom';

		if (!preg_match('#^(rss2|atom)$#',$type))
		{
			$type = 'atom';
		}

		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_feed").($_ctx->exists("categories") ? "/category/".$_ctx->categories->cat_url : "")."/'.$type.'"');
	}

	# Navigation menu
	public static function EventsMenuPeriod($attr,$content)
	{
		$menus = !empty($attr['menus']) ? $attr['menus'] : '';
		$separator = !empty($attr['separator']) ? $attr['separator'] : '';
		$list = !empty($attr['list']) ? $attr['list'] : '';
		$item = !empty($attr['item']) ? $attr['item'] : '';
		$active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';

		return "<?php echo tplEventHandler::EventsMenuPeriodHelper('".addslashes($menus)."','".addslashes($separator)."','".addslashes($list)."','".addslashes($item)."','".addslashes($active_item)."'); ?>";
	}

	# Navigation menu helper
	public static function EventsMenuPeriodHelper($menus,$separator,$list,$item,$active_item)
	{
		global $core, $_ctx;

		$default_menu = array(
			'all' => __('All') ,
			'ongoing' => __('Ongoing'),
			'outgoing' => __('Outgoing'),
			'scheduled' => __('Scheduled'),
			'started' => __('Started'),
			'notfinished' => __('Not finished'),
			'finished' => __('Finished')
		);
		# Only requested menus
		$menu = $default_menu;
		if (!empty($menus))
		{
			$final_menu = array();
			$menus = explode(',',$menus);
			foreach($menus AS $k)
			{
				if (isset($default_menu[$k]))
				{
					$final_menu[$k] = $default_menu[$k];
				}
			}
			if (!empty($final_menu))
			{
				$menu = $final_menu;
			}
		}

		$separator = $separator ? html::decodeEntities($separator) : '';
		$list = $list ? html::decodeEntities($list) : '<ul>%s</ul>';
		$item = $item ? html::decodeEntities($item) : '<li><a href="%s">%s</a>%s</li>';
		$active_item = $active_item ? html::decodeEntities($active_item) : '<li class="nav-active"><a href="%s">%s</a>%s</li>';
		$url = $core->blog->url.$core->url->getBase("eventhandler_list").'/';
		if ($_ctx->exists('categories'))
		{
			$url .= 'category/'.$_ctx->categories->cat_url.'/';
		}

		$i = 1;
		$res = '';
		foreach($menu AS $id => $name)
		{
			$i++;
			$sep = $separator && $i < count($menu)+1 ? $separator : '';

			if (isset($_ctx->event_params['event_period']) && $_ctx->event_params['event_period'] == $id)
			{
				$res .= sprintf($active_item,$url.$id,$name,$sep);
			}
			else
			{
				$res .= sprintf($item,$url.$id,$name,$sep);
			}
		}

		return '<div id="eventhandler-menu-period">'.sprintf($list,$res).'</div>';
	}

	# Sort order menu
	public static function EventsMenuSortOrder($attr)
	{
		$menus = !empty($attr['menus']) ? $attr['menus'] : '';
		$separator = !empty($attr['separator']) ? $attr['separator'] : '';
		$list = !empty($attr['list']) ? $attr['list'] : '';
		$item = !empty($attr['item']) ? $attr['item'] : '';
		$active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';

		return "<?php echo tplEventHandler::EventsMenuSortOrdertHelper('".addslashes($menus)."','".addslashes($separator)."','".addslashes($list)."','".addslashes($item)."','".addslashes($active_item)."'); ?>";
	}

	# Sort order menu helper
	public static function EventsMenuSortOrdertHelper($menus,$separator,$list,$item,$active_item)
	{
		global $core, $_ctx;

		$default_sort_id = array(
			'title' => 'LOWER(post_title)',
			'selected' => 'post_selected',
			'author' => 'LOWER(user_id)',
			'date' => 'post_dt',
			'startdt' => 'event_startdt',
			'enddt' =>'event_enddt'
		);
		$default_sort_text = array(
			'title' => __('Title'),
			'selected' => __('Selected'),
			'author' => __('Author'),
			'date' => __('Published date'),
			'startdt' => __('Start date'),
			'enddt' => __('End date')
		);

		# Only requested menus
		$menu = $default_sort_id;
		if (!empty($menus))
		{
			$final_menu = array();
			$menus = explode(',',$menus);
			foreach($menus AS $k)
			{
				if (isset($default_sort_id[$k]))
				{
					$final_menu[$k] = $default_sort_id[$k];
				}
			}
			if (!empty($final_menu))
			{
				$menu = $final_menu;
			}
		}

		$separator = $separator ? html::decodeEntities($separator) : '';
		$list = $list ?
			html::decodeEntities($list) :
			'<ul>%s</ul>';
		$item = $item ?
			html::decodeEntities($item) :
			'<li><a href="%s">%s</a>%s</li>';
		$active_item = $active_item ?
			html::decodeEntities($active_item) :
			'<li class="nav-active"><a href="%s">%s</a>%s</li>';
		$period = !empty($_ctx->event_params['event_period']) ?
			$_ctx->event_params['event_period'] :
			'all';
		$url = $core->blog->url.$core->url->getBase("eventhandler_list").'/';
		if ($_ctx->exists('categories')) {
			$url .= 'category/'.$_ctx->categories->cat_url.'/';
		}
		$url .= $period;

		$sortstr = $sortby = $sortorder = null;
		# Must quote array
		$quoted_default_sort_id = array();
		foreach($default_sort_id as $k => $v)
		{
			$quoted_default_sort_id[$k] = preg_quote($v);
		}

		if (isset($_ctx->event_params['order']) && preg_match('/('.implode('|',$quoted_default_sort_id).')\s(ASC|DESC)/i',$_ctx->event_params['order'],$sortstr))
		{
			$sortby = in_array($sortstr[1],$default_sort_id) ? $sortstr[1]: '';
			$sortorder = preg_match('#ASC#i',$sortstr[2]) ? 'asc' : 'desc';
		}

		$i = 1;
		$res = '';
		foreach($menu AS $id => $name)
		{
			$i++;
			$sep = $separator && $i < count($menu)+1 ? $separator : '';

			if ($sortby == $name)
			{
				$ord = $sortorder == 'asc' ? 'desc' : 'asc';
				$res .= sprintf($active_item,$url.'/'.$id.'/'.$ord,$default_sort_text[$id],$sep);
			}
			else
			{
				$ord = $sortorder == 'desc' ? 'desc' : 'asc';
				$res .= sprintf($item,$url.'/'.$id.'/'.$ord,$default_sort_text[$id],$sep);
			}
		}

		return '<div id="eventhandler-menu-sortorder">'.sprintf($list,$res).'</div>';
	}

	# Period info
	public static function EventsPeriod($attr)
	{
		if (!isset($attr['fulltext']))
		{
			$fulltext = '0';
		}
		elseif(empty($attr['fulltext']))
		{
			$fulltext = '1';
		}
		else
		{
			$fulltext = '2';
		}

		return "<?php echo tplEventHandler::EventsPeriodHelper('".$fulltext."'); ?>";
	}

	# Period helper
	public static function EventsPeriodHelper($fulltext)
	{
		global $_ctx;

		if ($fulltext == 2)
		{
			$text = array(
				'all' => __('All events'),
				'ongoing' => __('Current events'),
				'outgoing' => __('Event not being'),
				'scheduled' => __('Scheduled events'),
				'started' => __('Started events'),
				'notfinished' => __('Unfinished events'),
				'finished' => __('Completed events')
			);
		}
		elseif ($fulltext == 1)
		{
			$text = array(
				'all' => __('All'),
				'ongoing' => __('Ongoing'),
				'outgoing' => __('Outgoing'),
				'scheduled' => __('Scheduled'),
				'started' => __('Started'),
				'notfinished' => __('Not finished'),
				'finished' => __('Finished')
			);
		}
		else
		{
			$text = array(
				'all' => 'all',
				'ongoing' => 'ongoing',
				'outgoing' => 'outgoing',
				'scheduled' => 'scheduled',
				'started' => 'started',
				'notfinished' => 'notfinished',
				'finished' => 'finished'
			);
		}
		return isset($_ctx->event_params['event_period']) && isset($text[$_ctx->event_params['event_period']]) ? $text[$_ctx->event_params['event_period']] : $text['all'];
	}

	# Interval info
	public static function EventsInterval($attr)
	{
		$format = !empty($attr['format']) ? addslashes($attr['format']) : __('%m %d %Y');

		return "<?php echo tplEventHandler::EventsIntervalHelper('".$format."'); ?>";
	}

	# Interval info helper
	public static function EventsIntervalHelper($format)
	{
		global $_ctx;

		if (!empty($_ctx->event_params['event_start_year']))
		{
			if (!empty($_ctx->event_params['event_start_day']))
			{
				$dt = dt::str($format,mktime(0,0,0,$_ctx->event_params['event_start_month'],$_ctx->event_params['event_start_day'],$_ctx->event_params['event_start_year']));
				return sprintf(__('For the day of %s'),$dt);
			}
			elseif (!empty($_ctx->event_params['event_start_month']))
			{
				$dt = dt::str(__('%m %Y'),mktime(0,0,0,$_ctx->event_params['event_start_month'],1,$_ctx->event_params['event_start_year']));
				return sprintf(__('For the month of %s'),$dt);
			}
			elseif (!empty($_ctx->event_params['event_start_year']))
			{
				return sprintf(__('For the year of %s'),$_ctx->event_params['event_start_year']);
			}
		}
		else
		{
			$start = dt::dt2str($format,$_ctx->event_params['event_startdt']);
			$end = dt::dt2str($format,$_ctx->event_params['event_enddt']);

			if (strtotime($_ctx->event_params['event_startdt']) < strtotime($_ctx->event_params['event_enddt']))
			{
				return sprintf(__('For the period between %s and %s'),$start,$end);
			}
			else
			{
				return sprintf(__('For the period through %s and %s'),$end,$start);
			}
		}
	}

	# Conditions
	public static function EventsIf($attr,$content)
	{
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['has_interval']))
		{
			$sign = (boolean) $attr['has_interval'] ? '!' : '';
			$if[] = $sign.'empty($_ctx->event_params["event_interval"])';
		}

		if (isset($attr['has_category']))
		{
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->exists("categories")';
		}

		if (isset($attr['has_period']))
		{
			if ($attr['has_period'])
			{
				$if[] = '!empty($_ctx->event_params["event_period"]) && $_ctx->event_params["event_period"] != "all"';
			}
			else
			{
				$if[] = 'empty($_ctx->event_params["event_period"]) || !empty($_ctx->event_params["event_period"]) && $_ctx->event_params["event_period"] == "all"';
			}
		}

		if (isset($attr['period']))
		{
			$if[] =
			'(!empty($_ctx->event_params["event_period"]) && $_ctx->event_params["event_period"] == "'.addslashes($attr['period']).'" '.
			'|| empty($_ctx->event_params["event_period"]) && ("" == "'.addslashes($attr['period']).'" || "all" == "'.addslashes($attr['period']).'")))';
		}

		if (!empty($if))
		{
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		}
		else
		{
			return $content;
		}
	}

	#
	# Entries (on events page)
	#

	public static function EventsEntries($attr,$content)
	{
		global $core;

		$lastn = -1;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}

		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";

		if ($lastn != 0) {
			if ($lastn > 0) {
				$p .= "\$params['limit'] = ".$lastn.";\n";
			} else {
				$p .= "\$params['limit'] = \$_ctx->nb_entry_per_page;\n";
			}

			if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
				$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
			} else {
				$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
			}
		}

		if (isset($attr['author'])) {
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}

		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}

		if (isset($attr['no_category']) && $attr['no_category']) {
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}

		if (!empty($attr['type'])) {
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}

		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}

		if (isset($attr['period'])) {
			$p .= "\$params['event_period'] = '".addslashes($attr['period'])."';\n";
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("users")) { '.
				"\$params['user_id'] = \$_ctx->users->user_id; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("archives")) { '.
				"\$params['post_year'] = \$_ctx->archives->year(); ".
				"\$params['post_month'] = \$_ctx->archives->month(); ";
			if (!isset($attr['lastn'])) {
				$p .= "unset(\$params['limit']); ";
			}
			$p .=
			"}\n";

			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['post_lang'] = \$_ctx->langs->post_lang; ".
			"}\n";

			$p .=
			'if (isset($_search)) { '.
				"\$params['search'] = \$_search; ".
			"}\n";

			$p .=
			'if ($_ctx->exists("event_params")) { '.
				"\$params = array_merge(\$params,\$_ctx->event_params); ".
			"}\n";
		}

		if (!empty($attr['order']) || !empty($attr['sortby'])) {
			$p .=
			"\$params['order'] = '".$core->tpl->getSortByStr($attr,'eventhandler')."';\n";
		}

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		if (isset($attr['selected'])) {
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}

		if (isset($attr['age'])) {
			$age = $core->tpl->getAge($attr);
			$p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'".$age."\'';\n" : '';
		}

		return
		"<?php\n".
		'if(!isset($eventHandler)) { $eventHandler = new eventHandler($core); } '."\n".
		'$params = array(); '."\n".
		$p.
		'$_ctx->post_params = $params; '."\n".
		'$_ctx->posts = $eventHandler->getEvents($params); unset($params); '."\n".
		"?>\n".
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
	}

	# Pagination
	public static function EventsPagination($attr,$content)
	{
		$p =
		"<?php\n".
		'if(!isset($eventHandler)) { $eventHandler = new eventHandler($core); } '."\n".
		'$params = $_ctx->post_params; '."\n".
		'$_ctx->pagination = $eventHandler->getEvents($params,true); unset($params); '."\n".
		"?>\n";

		if (isset($attr['no_context']) && $attr['no_context']) {
			return $p.$content;
		}

		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}

	# Conditions
	public static function EventsEntryIf($attr,$content)
	{
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['has_category']))
		{
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->cat_id';
		}

		if (isset($attr['has_address']))
		{
			$sign = (boolean) $attr['has_address'] ? '!' : '=';
			$if[] = "'' ".$sign.'= $_ctx->posts->event_address';
		}

		if (isset($attr['has_geo']))
		{
			$sign = (boolean) $attr['has_geo'] ? '' : '!';
			$if[] = $sign.'("" != $_ctx->posts->event_latitude && "" != $_ctx->posts->event_longitude)';
		}

		if (isset($attr['period']))
		{
			$if[] = '$_ctx->posts->getPeriod() == "'.addslashes($attr['period']).'"';
		}

		if (isset($attr['sameday']))
		{
			$sign = (boolean) $attr['sameday'] ? '' : '!';
			$if[] = $sign."\$_ctx->posts->isOnSameDay()";
		}

		if (isset($attr['oneday']))
		{
			$sign = (boolean) $attr['oneday'] ? '' : '!';
			$if[] = $sign."\$_ctx->posts->isOnOneDay()";
		}

		if (!empty($attr['orderedby']))
		{
			if (substr($attr['orderedby'],0,1) == '!') {
				$sign = '!';
				$orderedby = substr($attr['orderedby'],1);
			}
			else {
				$sign = '';
				$orderedby = $attr['orderedby'];
			}

			$default_sort = array(
				'date' => 'post_dt',
				'startdt' => 'event_startdt',
				'enddt' =>'event_enddt'
			);

			if (isset($default_sort[$orderedby])) {
				$orderedby = $default_sort[$orderedby];

				$if[] = $sign."strstr(\$_ctx->post_params['order'],'".addslashes($orderedby)."')";
			}
		}

		if (!empty($if))
		{
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		}
		else
		{
			return $content;
		}
	}

	# First event date
	public static function EventsDateHeader($attr,$content)
	{
		$type = '';
		if (!empty($attr['creadt'])) { $type = 'creadt'; }
		if (!empty($attr['upddt'])) { $type = 'upddt'; }
		if (!empty($attr['enddt'])) { $type = 'enddt'; }
		if (!empty($attr['startdt'])) { $type = 'startdt'; }

		return
		"<?php ".
		'if ($_ctx->posts->firstEventOfDay("'.$type.'")) : ?>'.
		$content.
		"<?php endif; ?>";
	}

	# Last event date
	public static function EventsDateFooter($attr,$content)
	{
		$type = '';
		if (!empty($attr['creadt'])) { $type = 'creadt'; }
		if (!empty($attr['upddt'])) { $type = 'upddt'; }
		if (!empty($attr['enddt'])) { $type = 'enddt'; }
		if (!empty($attr['startdt'])) { $type = 'startdt'; }

		return
		"<?php ".
		'if ($_ctx->posts->lastEventOfDay("'.$type.'")) : ?>'.
		$content.
		"<?php endif; ?>";
	}

	# Date of selected type
	public static function EventsEntryDate($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$iso8601 = !empty($a['iso8601']);
		$rfc822 = !empty($a['rfc822']);

		$type = '';
		if (!empty($a['creadt'])) { $type = 'creadt'; }
		if (!empty($a['upddt'])) { $type = 'upddt'; }
		if (!empty($a['enddt'])) { $type = 'enddt'; }
		if (!empty($a['startdt'])) { $type = 'startdt'; }

		if ($rfc822) {
			return self::tplValue($a,"\$_ctx->posts->getEventRFC822Date('".$type."')");
		} elseif ($iso8601) {
			return self::tplValue($a,"\$_ctx->posts->getEventISO8601Date('".$type."')");
		} else {
			return self::tplValue($a,"\$_ctx->posts->getEventDate('".$format."','".$type."')");
		}
	}

	# Time of selected type
	public static function EventsEntryTime($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$type = '';
		if (!empty($a['creadt'])) { $type = 'creadt'; }
		if (!empty($a['upddt'])) { $type = 'upddt'; }
		if (!empty($a['enddt'])) { $type = 'enddt'; }
		if (!empty($a['startdt'])) { $type = 'startdt'; }

		return self::tplValue($a,"\$_ctx->posts->getEventTime('".$format."','".$type."')");
	}

	# Category url
	public static function EventsEntryCategoryURL($a)
	{
		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_list")."/category/".html::sanitizeURL($_ctx->posts->cat_url)');
	}

	# Address
	public static function EventsEntryAddress($a)
	{
		$ics = !empty($a['ics']) ? '"LOCATION;CHARSET=UTF-8:".' : '';

		return self::tplValue($a,$ics.'$_ctx->posts->event_address');
	}

	# Latitude
	public static function EventsEntryLatitude($a)
	{
		return self::tplValue($a,'$_ctx->posts->event_latitude');
	}

	# Longitude
	public static function EventsEntryLongitude($a)
	{
		return self::tplValue($a,'$_ctx->posts->event_longitude');
	}

	# Duration
	public static function EventsEntryDuration($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';

		return self::tplValue($a,"eventHandler::getReadableDuration((strtotime(\$_ctx->posts->event_enddt) - strtotime(\$_ctx->posts->event_startdt)),'".$format."')");
	}

	# Period
	public static function EventsEntryPeriod($attr)
	{
		$scheduled = isset($attr['scheduled']) ? $attr['scheduled'] : 'scheduled';
		if (empty($attr['strict'])) $scheduled = __($scheduled);

		$ongoing = isset($attr['ongoing']) ? $attr['ongoing'] : 'ongoing';
		if (empty($attr['strict'])) $ongoing = __($ongoing);

		$finished = isset($attr['finished']) ? $attr['finished'] : 'finished';
		if (empty($attr['strict'])) $finished = __($finished);

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
		"<?php \$time = time() + dt::getTimeOffset(\$_ctx->posts->post_tz)*2;\n".
		"if (\$_ctx->posts->getEventTS('startdt') > \$time) {\n".
		" echo ".sprintf($f,"'".$scheduled."'")."; }\n".
		"elseif (\$_ctx->posts->getEventTS('startdt') < \$time && \$_ctx->posts->getEventTS('enddt') > \$time) {\n".
		" echo ".sprintf($f,"'".$ongoing."'")."; }\n".
		"elseif (\$_ctx->posts->getEventTS('enddt') < \$time) {\n".
		" echo ".sprintf($f,"'".$finished."'")."; }\n".
		"unset(\$time); ?>\n";
	}

	# Map
	public static function EventsEntryMap($attr)
	{
		$map_zoom = !empty($attr['map_zoom']) ? abs((integer) $attr['map_zoom']) : '$core->blog->settings->eventHandler->public_map_zoom';
		$map_type = !empty($attr['map_type']) ? '"'.html::escapeHTML($attr['map_type']).'"' : '$core->blog->settings->eventHandler->public_map_type';
		$map_info = isset($attr['map_info']) && $attr['map_info'] == '0' ? '0' : '1';

		return '<?php echo eventHandler::getGmapContent("","",'.$map_type.','.$map_zoom.','.$map_info.',$_ctx->posts->event_latitude,$_ctx->posts->event_longitude,$_ctx->posts->getGmapVEVENT()); ?>';
	}

	#
	# Events of an entry (on posts context)
	#

	public static function EventsOfPost($attr,$content)
	{
		global $core;

		$p = '';

		$lastn = -1;
		if (isset($attr['lastn']))
		{
			$lastn = abs((integer) $attr['lastn'])+0;
			if ($lastn > 0)
			{
				$p .= "\$params['limit'] = ".$lastn.";\n";
			}
		}

		if (isset($attr['event']))
		{
			$p .= "\$params['event_id'] = '".abs((integer) $attr['event'])."';\n";
		}

		if (isset($attr['author']))
		{
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}

		if (isset($attr['category']))
		{
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}

		if (isset($attr['no_category']) && $attr['no_category'])
		{
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}

		if (isset($attr['post']))
		{
			$p .= "\$params['post_id'] = '".abs((integer) $attr['post'])."';\n";
		}

		if (!empty($attr['type']))
		{
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}

		$p .= "\$params['order'] = '".$core->tpl->getSortByStr($attr,'post')."';\n";

		if (isset($attr['no_content']) && $attr['no_content'])
		{
			$p .= "\$params['no_content'] = true;\n";
		}

		if (isset($attr['selected']))
		{
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}

		if (isset($attr['age']))
		{
			$age = $core->tpl->getAge($attr);
			$p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'".$age."\'';\n" : '';
		}

		return
		"<?php\n".
		'if(!isset($eventHandler)) { $eventHandler = new eventHandler($core); } '."\n".
		'$params = array(); '."\n".
		'$public_hidden_categories = @unserialize($core->blog->settings->eventHandler->public_hidden_categories); '.
		'if (is_array($public_hidden_categories)) { '.
		' foreach($public_hidden_categories as $hidden_cat) { '.
		'  @$params[\'sql\'] .= " AND C.cat_id != \'".$core->con->escape($hidden_cat)."\' "; '.
		' } '.
		"} \n".
		'if ($_ctx->exists("posts") && $_ctx->posts->post_id) { '.
		'$params["post_id"] = $_ctx->posts->post_id; '.
		"} \n".
		$p.
		'if (!empty($params["post_id"])) { '."\n".
		'$_ctx->eventsofpost_params = $params;'."\n".
		'$_ctx->eventsofpost = $eventHandler->getEventsByPost($params); unset($params); '."\n".
		'while ($_ctx->eventsofpost->fetch()) : ?>'.$content.'<?php endwhile; '.
		'} '."\n".
		'$_ctx->eventsofpost = null; $_ctx->eventsofpost_params = null; ?>';
	}

	public static function EventsOfPostHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->eventsofpost->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function EventsOfPostFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->eventsofpost->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function EventOfPostIf($attr,$content)
	{
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['has_category']))
		{
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->eventsofpost->cat_id';
		}

		if (isset($attr['has_address']))
		{
			$sign = (boolean) $attr['has_address'] ? '!' : '=';
			$if[] = "'' ".$sign.'= $_ctx->eventsofpost->event_address';
		}

		if (isset($attr['period']))
		{
			$if[] = '$_ctx->eventsofpost->getPeriod() == "'.addslashes($attr['period']).'"';
		}

		if (isset($attr['sameday']))
		{
			$sign = (boolean) $attr['sameday'] ? '' : '!';
			$if[] = $sign."\$_ctx->eventsofpost->isOnSameDay()";
		}

		if (isset($attr['oneday']))
		{
			$sign = (boolean) $attr['oneday'] ? '' : '!';
			$if[] = $sign."\$_ctx->eventsofpost->isOnOneDay()";
		}

		if (!empty($attr['orderedby']))
		{
			if (substr($attr['orderedby'],0,1) == '!') {
				$sign = '!';
				$orderedby = substr($attr['orderedby'],1);
			}
			else {
				$sign = '';
				$orderedby = $attr['orderedby'];
			}

			$default_sort = array(
				'date' => 'post_dt',
				'startdt' => 'event_startdt',
				'enddt' =>'event_enddt'
			);

			if (isset($default_sort[$orderedby])) {
				$orderedby = $default_sort[$orderedby];

				$if[] = $sign."strstr(\$_ctx->eventsofpost['order'],'".addslashes($orderedby)."')";
			}
		}

		if (!empty($if))
		{
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		}
		else
		{
			return $content;
		}
	}

	public static function EventOfPostTitle($a)
	{
		return self::tplValue($a,'$_ctx->eventsofpost->post_title');
	}

	public static function EventOfPostURL($a)
	{
		return self::tplValue($a,'$_ctx->eventsofpost->getURL()');
	}

	public static function EventOfPostDate($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$iso8601 = !empty($a['iso8601']);
		$rfc822 = !empty($a['rfc822']);

		$type = '';
		if (!empty($a['creadt'])) { $type = 'creadt'; }
		if (!empty($a['upddt'])) { $type = 'upddt'; }
		if (!empty($a['enddt'])) { $type = 'enddt'; }
		if (!empty($a['startdt'])) { $type = 'startdt'; }

		if ($rfc822) {
			return self::tplValue($a,"\$_ctx->eventsofpost->getEventRFC822Date('".$type."')");
		} elseif ($iso8601) {
			return self::tplValue($a,"\$_ctx->eventsofpost->getEventISO8601Date('".$type."')");
		} else {
			return self::tplValue($a,"\$_ctx->eventsofpost->getEventDate('".$format."','".$type."')");
		}
	}

	public static function EventOfPostTime($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$type = '';
		if (!empty($a['creadt'])) { $type = 'creadt'; }
		if (!empty($a['upddt'])) { $type = 'upddt'; }
		if (!empty($a['enddt'])) { $type = 'enddt'; }
		if (!empty($a['startdt'])) { $type = 'startdt'; }

		return self::tplValue($a,"\$_ctx->eventsofpost->getEventTime('".$format."','".$type."')");
	}

	public static function EventOfPostAuthorCommonName($a)
	{
		return self::tplValue($a,'$_ctx->eventsofpost->getAuthorCN()');
	}

	public static function EventOfPostAuthorLink($a)
	{
		return self::tplValue($a,'$_ctx->eventsofpost->getAuthorLink()');
	}

	public static function EventOfPostCategory($a)
	{
		return self::tplValue($a,'$_ctx->eventsofpost->cat_title');
	}

	public static function EventOfPostCategoryURL($a)
	{
		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_list")."/category/".html::sanitizeURL($_ctx->eventsofpost->cat_url)');
	}

	public static function EventOfPostAddress($a)
	{
		return self::tplValue($a,'$_ctx->eventsofpost->event_address');
	}

	public static function EventOfPostDuration($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';

		return self::tplValue($a,"eventHandler::getReadableDuration((strtotime(\$_ctx->eventsofpost->event_enddt) - strtotime(\$_ctx->eventsofpost->event_startdt)),'".$format."')");
	}

	public static function EventOfPostPeriod($attr)
	{
		$scheduled = isset($attr['scheduled']) ? $attr['scheduled'] : 'scheduled';
		if (empty($attr['strict'])) $scheduled = __($scheduled);

		$ongoing = isset($attr['ongoing']) ? $attr['ongoing'] : 'ongoing';
		if (empty($attr['strict'])) $ongoing = __($ongoing);

		$finished = isset($attr['finished']) ? $attr['finished'] : 'finished';
		if (empty($attr['strict'])) $finished = __($finished);

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
		"<?php \$time = time() + dt::getTimeOffset(\$_ctx->eventsofpost->post_tz)*2;\n".
		"if (\$_ctx->eventsofpost->getEventTS('startdt') > \$time) {\n".
		" echo ".sprintf($f,"'".$scheduled."'")."; }\n".
		"elseif (\$_ctx->eventsofpost->getEventTS('startdt') < \$time && \$_ctx->eventsofpost->getEventTS('enddt') > \$time) {\n".
		" echo ".sprintf($f,"'".$ongoing."'")."; }\n".
		"elseif (\$_ctx->eventsofpost->getEventTS('enddt') < \$time) {\n".
		" echo ".sprintf($f,"'".$finished."'")."; }\n".
		"unset(\$time); ?>\n";
	}

	#
	# Entries of an event (on events context)
	#

	public static function PostsOfEvent($attr,$content)
	{
		global $core;

		$p = '';

		$lastn = -1;
		if (isset($attr['lastn']))
		{
			$lastn = abs((integer) $attr['lastn'])+0;
			if ($lastn > 0)
			{
				$p .= "\$params['limit'] = ".$lastn.";\n";
			}
		}

		if (isset($attr['event']))
		{
			$p .= "\$params['event_id'] = '".abs((integer) $attr['event'])."';\n";
		}

		if (isset($attr['author']))
		{
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}

		if (isset($attr['category']))
		{
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}

		if (isset($attr['no_category']) && $attr['no_category'])
		{
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}

		if (!empty($attr['type']))
		{
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}

		$p .= "\$params['order'] = '".$core->tpl->getSortByStr($attr,'post')."';\n";

		if (isset($attr['no_content']) && $attr['no_content'])
		{
			$p .= "\$params['no_content'] = true;\n";
		}

		if (isset($attr['selected']))
		{
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}

		if (isset($attr['age']))
		{
			$age = $core->tpl->getAge($attr);
			$p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'".$age."\'';\n" : '';
		}

		return
		"<?php\n".
		"\$postsofeventHandler = new eventHandler(\$core); \n".
		'if ($_ctx->exists("posts") && $_ctx->posts->post_id) { '.
		" \$params['event_id'] = \$_ctx->posts->post_id; ".
		"} \n".
		$p.
		'$_ctx->postsofevent_params = $params;'."\n".
		'$_ctx->postsofevent = $postsofeventHandler->getPostsByEvent($params); unset($params);'."\n".
		"?>\n".
		'<?php while ($_ctx->postsofevent->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->postsofevent = null; $_ctx->postsofevent_params = null; $postsofeventHandler = null; ?>';

		return $res;
	}

	public static function PostsOfEventHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->postsofevent->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function PostsOfEventFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->postsofevent->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function PostOfEventIf($attr,$content)
	{
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['type']))
		{
			$type = trim($attr['type']);
			$type = !empty($type)?$type:'post';
			$if[] = '$_ctx->postsofevent->post_type == "'.addslashes($type).'"';
		}

		if (isset($attr['has_category']))
		{
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->postsofevent->cat_id';
		}

		if (!empty($if))
		{
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		}
		else
		{
			return $content;
		}
	}

	public static function PostOfEventTitle($a)
	{
		return self::tplValue($a,'$_ctx->postsofevent->post_title');
	}

	public static function PostOfEventURL($a)
	{
		return self::tplValue($a,'$_ctx->postsofevent->getURL()');
	}

	public static function PostOfEventDate($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$iso8601 = !empty($a['iso8601']);
		$rfc822 = !empty($a['rfc822']);
		$type = (!empty($a['creadt']) ? 'creadt' : '');
		$type = (!empty($a['upddt']) ? 'upddt' : '');

		if ($rfc822)
		{
			return self::tplValue($a,"\$_ctx->postsofevent->getRFC822Date('".$type."')");
		}
		elseif ($iso8601)
		{
			return self::tplValue($a,"\$_ctx->postsofevent->getISO8601Date('".$type."')");
		}
		else
		{
			return self::tplValue($a,"\$_ctx->postsofevent->getDate('".$format."','".$type."')");
		}
	}

	public static function PostOfEventTime($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$type = (!empty($a['creadt']) ? 'creadt' : '');
		$type = (!empty($a['upddt']) ? 'upddt' : '');

		return self::tplValue($a,"\$_ctx->postsofevent->getTime('".$format."','".$type."')");
	}

	public static function PostOfEventAuthorCommonName($a)
	{
		return self::tplValue($a,'$_ctx->postsofevent->getAuthorCN()');
	}

	public static function PostOfEventAuthorLink($a)
	{
		return self::tplValue($a,'$_ctx->postsofevent->getAuthorLink()');
	}

	public static function PostOfEventCategory($a)
	{
		return self::tplValue($a,'$_ctx->postsofevent->cat_title');
	}

	public static function PostOfEventCategoryURL($a)
	{
		return self::tplValue($a,'$_ctx->postsofevent->getCategoryURL()');
	}

	# Generic template value
	protected static function tplValue($a,$v)
	{
		return '<?php echo '.sprintf($GLOBALS['core']->tpl->getFilters($a),$v).'; ?>';
	}
}
