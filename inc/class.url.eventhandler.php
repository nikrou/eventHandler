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


# URL handler
class urlEventHandler extends dcUrlHandlers
{
	# Call service from public for ajax request
	public static function eventService($args) {
		global $core;
		$core->rest->addFunction('eventHandlerCalendar', array('eventHandlerPublicRest','calendar'));
		$core->rest->serve();
		exit;
	}

	# Single event page
	public static function eventSingle($args) {
		global $core, $_ctx;

		if ($args == '' || !$_ctx->preview && !$core->blog->settings->eventHandler->active) {
			self::p404();
			return;
		} else {
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


			if ($_ctx->posts->isEmpty()) {
				# The specified page does not exist.
				self::p404();
			} else {
				$post_id = $_ctx->posts->post_id;
				$post_password = $_ctx->posts->post_password;

				# Password protected entry
				if ($post_password != '' && !$_ctx->preview) {
					# Get passwords cookie
					if (isset($_COOKIE['dc_passwd'])) {
						$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
					} else {
						$pwd_cookie = array();
					}

					# Check for match
					if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
                        || (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password)) {
						$pwd_cookie[$post_id] = $post_password;
						setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
					} else {
						self::serveDocument('password-form.html','text/html',false);
						return;
					}
				}

				if ($is_ical) {
					self::serveIcalDocument($_ctx->posts,$args);
				} elseif ($is_hcal) {
					self::serveHcalDocument($_ctx->posts,$args);
				} elseif ($is_gmap) {
					self::serveGmapDocument($_ctx->posts,$args);
				} else {
					self::serveDocument('eventhandler-single.html');
				}
			}
		}
		return;
	}

	# Preview single event from admin side
	public static function eventPreview($args) {
		global $core, $_ctx;

		if (!preg_match('#^(.+?)/([0-9a-z]{40})/(.+?)$#',$args,$m)) {
			# The specified Preview URL is malformed.
			self::p404();
		} else {
			$user_id = $m[1];
			$user_key = $m[2];
			$post_url = $m[3];
			if (!$core->auth->checkUser($user_id,null,$user_key)) {
				# The user has no access to the entry.
				self::p404();
			} else {
				$_ctx->preview = true;
				self::eventSingle($post_url);
			}
		}
	}

	# Multiple events page
	public static function eventList($args) {
		global $core, $_ctx;

		$n = self::getPageNumber($args);
		$is_ical = self::isIcalDocument($args);
		$is_hcal = self::isHcalDocument($args);
		$is_gmap = self::isGmapDocument($args);

		$_ctx->event_params = self::getEventsParams($args);

		if ($n) {
			$GLOBALS['_page_number'] = $n;
		}

		# If it is ical do all job here
		if ($is_hcal || $is_ical || $is_gmap) {
			$params = array();
			// force limit on gmap
			if ($is_gmap) {
				$params['limit'] = array(0,30);
			} else {
				$pn = $n ? $n : 1;
				$nbppf = $core->blog->settings->system->nb_post_per_feed;
				$params['limit'] = array((($pn-1)*$nbppf),$nbppf);
			}
			if ($_ctx->exists("categories")) {
				$params['cat_id'] = $_ctx->categories->cat_id;
			}
			$params = array_merge($params,$_ctx->event_params);

			$eventHandler = new eventHandler($core);
			$rs = $eventHandler->getEvents($params);

			if ($is_ical) {
				self::serveIcalDocument($rs,$args);
			} elseif ($is_hcal) {
				self::serveHcalDocument($rs,$args);
			} elseif ($is_gmap) {
				self::serveGmapDocument($rs,$args);
			}
		} else { # Else serve normal document
			self::serveDocument('eventhandler-list.html');
		}
		return;
	}

	# Classic feed
	public static function eventFeed($args) {
		$type = null;
		$cat_url = false;
		$params = array();
		$subtitle = '';

		$mime = 'application/xml';

		global $core, $_ctx;

		if (preg_match('!^([a-z]{2}(-[a-z]{2})?)/(.*)$!',$args,$m)) {
			$params['lang'] = $m[1];
			$args = $m[3];

			$_ctx->langs = $core->blog->getLangs($params);

			if ($_ctx->langs->isEmpty()) {
				# The specified language does not exist.
				self::p404();
				return;
			} else {
				$_ctx->cur_lang = $m[1];
			}
		}

		if (preg_match('#^rss2/xslt$#',$args,$m)) {
			# RSS XSLT stylesheet
			self::serveDocument('rss2.xsl','text/xml');
			return;
		} elseif (preg_match('#^(?:category/(.+)/)?(atom|rss2)$#',$args,$m)) {
			# All posts or comments feed
			$type = $m[2];
			if (!empty($m[1])) {
				$cat_url = $m[1];
			}
		} else {
			# The specified Feed URL is malformed.
			self::p404();
			return;
		}

		if ($cat_url) {
			$params['cat_url'] = $cat_url;
			$params['post_type'] = 'eventhandler';
			$_ctx->categories = $core->blog->getCategories($params);

			if ($_ctx->categories->isEmpty()) {
				# The specified category does no exist.
				self::p404();
				return;
			}

			$subtitle = ' - '.$_ctx->categories->cat_title;
		}

		$tpl = 'eventhandler-'.$type.'.xml';

		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}

		$_ctx->nb_entry_per_page = $core->blog->settings->system->nb_post_per_feed;
		$_ctx->short_feed_items = $core->blog->settings->system->short_feed_items;
		$_ctx->feed_subtitle = $subtitle;

		header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy,''));
		self::serveDocument($tpl,$mime);
		if (!$cat_url) {
			$core->blog->publishScheduledEntries();
		}
	}

	# Parse URI for multiple events page
	public static function getEventsParams($args) {
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
		if (!preg_match(
            '#^'.
			'((/category/([^/]+))|)'.
			'('.
			 '(/('.implode('|',$default_period_list).'))|'. // period
			 '(/(on|in|of)/([0-9]{4})(/([0-9]{1,2})|)(/([0-9]{1,2})|)(/([0-9]{4})(/([0-9]{1,2})|)(/([0-9]{1,2})|)|))|'. // interval
			')'.
			'(/('.implode('|',array_keys($default_order_list)).')(/(asc|desc)|)|)'. // order
			'(/ical.ics|/hcal.html|/gmap|/|)$#i',$args,$m)) {
			self::p404();
			return;
		}

		# Get category
		if (!empty($m[3])) {
			$cat_params['cat_url'] = $m[3];
			$cat_params['post_type'] = 'eventhandler';
			$_ctx->categories = $core->blog->getCategories($cat_params);

			if ($_ctx->categories->isEmpty()) {
				# The specified category does no exist.
				self::p404();
				return;
			}
		}

		# Get period
		if (!empty($m[6])) {
			$params['event_period'] = $m[6];
		} elseif (!empty($m[8])) { # Get interval
			$params['event_interval'] = $m[8];

			# Make start date
			if (!empty($m[13])) {
				$start =  date('Y-m-d 00:00:00',mktime(0,0,0,$m[11],$m[13],$m[9]));
				$end =  date('Y-m-d 00:00:00',mktime(0,0,0,$m[11],($m[13]+1),$m[9]));
			} elseif (!empty($m[11])) {
				$start = date('Y-m-d 00:00:00',mktime(0,0,0,$m[11],1,$m[9]));
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,($m[11]+1),1,$m[9]));
			} elseif (!empty($m[9])) {
				$start = date('Y-m-d 00:00:00',mktime(0,0,0,1,1,$m[9]));
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,1,1,($m[9]+1)));
			}
			# Make end date
			if (!empty($m[19])) {
				$end =  date('Y-m-d 00:00:00',mktime(0,0,0,$m[17],$m[19],$m[15]));
			} elseif (!empty($m[17])) {
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,$m[17],1,$m[15]));
			} elseif (!empty($m[15])) {
				$end = date('Y-m-d 00:00:00',mktime(0,0,0,1,1,$m[15]));
			}
			# Make interval
			if ($m[8] == 'on') {
				$params['event_period'] = 'ongoing';
				$params['event_startdt'] = $end;
				$params['event_enddt'] = $start;
			} elseif($m[8] == 'in') {
				$params['event_period'] = 'ongoing';
				$params['event_startdt'] = $start;
				$params['event_enddt'] = $end;
			} else {
				if (!empty($m[9])) {
					$params['event_start_year'] = $m[9];
				}
				if (!empty($m[11])) {
					$params['event_start_month'] = $m[11];
				}
				if (!empty($m[13])) {
					$params['event_start_day'] = $m[13];
				}
			}
		} else {
			$params['event_period'] = 'scheduled'; // default
		}
		# Get order
		$params['order'] = 'event_startdt ASC'; // default
		if (!empty($m[21])) {
			$sortorder = 'ASC';
			if (!empty($m[23])) {
				$sortorder = strtoupper($m[23]);
			}
			$params['order'] = $default_order_list[$m[21]].' '.$sortorder;
		}

		return $params;
	}

	# Test if request url is a ical
	protected static function isIcalDocument(&$args) {
		if (preg_match('#/ical\.ics$#',$args,$m)) {
			$args = preg_replace('#/ical\.ics$#','',$args);
			return true;
		}
		return false;
	}

	# Test if request url is a hcal
	protected static function isHcalDocument(&$args) {
		if (preg_match('#/hcal\.html$#',$args,$m)) {
			$args = preg_replace('#/hcal\.html$#','',$args);
			return true;
		}
		return false;
	}

	# Test if request url is a gmap
	protected static function isGmapDocument(&$args) {
		if (preg_match('#/gmap$#',$args,$m)) {
			$args = preg_replace('#/gmap$#','',$args);
			return true;
		}
		return false;
	}

	# Serve special ical document
	public static function serveIcalDocument($rs, $x_dc_folder='') {
		global $core;

		if ($rs->isEmpty()) {
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

		if ($x_dc_folder) {
			$res .=
			implode("\r\n ",str_split(trim("X-DC-FOLDER:".$x_dc_folder),70))."\r\n";
		}

		while($rs->fetch()) {
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
	public static function serveHcalDocument($rs,$x_dc_folder='') {
		global $core;

		if ($rs->isEmpty()) {
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
		'pf=eventHandler/css/event-hcalendar.css);'."\n".
		'</style>'."\n".
		'</head>'."\n".
		'<body>'."\n".
		'<div id="page">'."\n".
		'<div id="top">'."\n".
		'<h1><a href="'.$core->blog->url.'">'.html::escapeHTML($core->blog->name).
		' - '.__('Events').'</a></h1>'."\n";

		if ($x_dc_folder) {
			$res .= '<p>'.__('Directory:').' <a href="'.
			$core->blog->url.$core->url->getBase('eventhandler_list').$x_dc_folder.'">'.
			$x_dc_folder.'</a></p>'."\n";
		}

		$res .=
		'</div>';

		while($rs->fetch()) {
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
	public static function serveGmapDocument($rs,$x_dc_folder='') {
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
		'pf=eventHandler/js/googlepmaps/event-public-map.js"></script>'."\n".
		'<style type="text/css">'.
		'html { height: 100%; } body { height: 100%; margin: 0px; padding: 0px; } '.
		'.event-gmap, .event-gmap-place { height: 100%; } h2 { margin: 2em;}</style>'."\n".
		'</head>'.
		'<body>';

		if ($rs->count()) {
			$total_lat = $total_lng = 0;
			$markers = '';
			while($rs->fetch()) {
				$total_lat += (float) $rs->event_latitude;
				$total_lng += (float) $rs->event_longitude;
				$markers .= $rs->getMapVEvent();
			}
			$lat = round($total_lat / $rs->count(), 7);
			$lng = round($total_lng / $rs->count(), 7);

			$res .= eventHandler::getMapContent(
				'',
				'',
				$core->blog->settings->eventHandler->public_map_type,
				2,
				1,
				$lat,
				$lng,
				$markers
			);
		} else {
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
