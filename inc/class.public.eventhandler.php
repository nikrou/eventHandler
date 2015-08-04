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

# Public behaviors
class publicEventHandler
{
	# Add some css and js to page
	public static function publicHeadContent($core) {
		if (!$core->blog->settings->eventHandler->active) {
			return;
		}

		$public_map_zoom = abs((integer) $core->blog->settings->eventHandler->public_map_zoom);
		if (!$public_map_zoom) $public_map_zoom = 9;

		if ($core->blog->settings->eventHandler->map_provider=='osm') {
			echo '<link rel="stylesheet" type="text/css" href="'.$core->blog->getQmarkURL().'pf=eventHandler/css/leaflet.css"/>',"\n";
			echo '<script type="text/javascript" src="'.$core->blog->getQmarkURL().'pf=eventHandler/js/osm/leaflet-src.js"></script>',"\n";
		}

		echo '<script type="text/javascript" src="'.$core->blog->getQmarkURL().'pf=eventHandler/js/'.$core->blog->settings->eventHandler->map_provider.'/event-public-map.js"></script>',"\n";
        echo '<script type="text/javascript" src="'.$core->blog->getQmarkURL().'pf=eventHandler/js/event-public-cal.js"></script>',"\n";
        echo '<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			" \$(function(){ \n".
			"  \$.fn.eventHandlerCalendar.defaults.service_url = '".
			html::escapeJS($core->blog->url.$core->url->getBase('eventhandler_pubrest').'/')."'; \n".
			"  \$.fn.eventHandlerCalendar.defaults.service_func = '".
			html::escapeJS('eventHandlerCalendar')."'; \n".
			"  \$.fn.eventHandlerCalendar.defaults.blog_uid = '".
			html::escapeJS($core->blog->uid)."'; \n".
			"  \$.fn.eventHandlerCalendar.defaults.msg_wait = '".
			html::escapeJS(__('Please wait...'))."'; \n".
			"  \$('.calendar-array').eventHandlerCalendar(); \n".
			" })\n".
			"//]]>\n".
			"</script>\n";

		$extra_css = $core->blog->settings->eventHandler->public_extra_css;
		if ($extra_css) {
			echo '<style type="text/css">'."\n".html::escapeHTML($extra_css)."\n"."\n</style>\n";
		}
	}

	public static function publicBeforeDocument($core) {
		$tplset = $core->themes->moduleInfo($core->blog->settings->system->theme, 'tplset');
		if (!empty($tplset) && is_dir(__DIR__.'/../default-templates/'.$tplset)) {
			$core->tpl->setPath($core->tpl->getPath(), __DIR__.'/../default-templates/'.$tplset);
		} else {
			$core->tpl->setPath($core->tpl->getPath(), __DIR__.'/../default-templates/'.DC_DEFAULT_TPLSET);
		}
	}

	# Before entry content
	public static function publicEntryBeforeContent($core, $_ctx) {
		return self::publicEntryContent($core, $_ctx, 'before');
	}

	# After entry content
	public static function publicEntryAfterContent($core, $_ctx) {
		return self::publicEntryContent($core, $_ctx, 'after');
	}

	# Add list of events / posts on entries
	protected static function publicEntryContent($core, $_ctx, $place) {
		$s = $core->blog->settings->eventHandler;

		if (!$s->active || $s->public_posts_of_event_place == '') {
			return;
		}

		$default_url_type = array(
			'eventhandler_list',
			'eventhandler_single',
			'eventhandler_preview'
		);

		# List of posts related to a event
		if (in_array($core->url->type,$default_url_type)) {
			if ($s->public_posts_of_event_place != $place) {
				return;
			}

			echo $core->tpl->getData('eventhandler-postsofevent.html');
		} else { # List of events related to a post
			if ($s->public_events_of_post_place != $place) {
				return;
			}

			echo $core->tpl->getData('eventhandler-eventsofpost.html');
		}
	}
}
