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
use Dotclear\Helper\Html\Html;

class PublicBehaviors
{
    // Add some css and js to page
    public static function publicHeadContent()
    {
        if (!dcCore::app()->blog->settings->eventHandler->active) {
            return;
        }

        $public_map_zoom = abs((integer) dcCore::app()->blog->settings->eventHandler->public_map_zoom);
        if (!$public_map_zoom) {
            $public_map_zoom = 9;
        }

        if (dcCore::app()->blog->settings->eventHandler->map_provider == 'osm') {
            echo '<link rel="stylesheet" type="text/css" href="' . dcCore::app()->blog->getQmarkURL() . 'pf=eventHandler/css/leaflet.css"/>',"\n";
            echo '<script src="' . dcCore::app()->blog->getQmarkURL() . 'pf=eventHandler/js/osm/leaflet-src.js"></script=>',"\n";
        }

        echo '<script src="' . dcCore::app()->blog->getQmarkURL() . 'pf=eventHandler/js/' . dcCore::app()->blog->settings->eventHandler->map_provider . '/event-public-map.js"></script>',"\n";
        echo '<script src="' . dcCore::app()->blog->getQmarkURL() . 'pf=eventHandler/js/event-public-cal.js"></script>',"\n";
        echo '<script>' . "\n" .
			"//<![CDATA[\n" .
			" \$(function(){ \n" .
			"  \$.fn.eventHandlerCalendar.defaults.service_url = '" .
			Html::escapeJS(dcCore::app()->blog->url . dcCore::app()->url->getBase('eventhandler_pubrest') . '/') . "'; \n" .
			"  \$.fn.eventHandlerCalendar.defaults.service_func = '" .
			Html::escapeJS('eventHandlerCalendar') . "'; \n" .
			"  \$.fn.eventHandlerCalendar.defaults.blog_uid = '" .
			Html::escapeJS(dcCore::app()->blog->uid) . "'; \n" .
			"  \$.fn.eventHandlerCalendar.defaults.msg_wait = '" .
			Html::escapeJS(__('Please wait...')) . "'; \n" .
			"  \$('.calendar-array').eventHandlerCalendar(); \n" .
			" })\n" .
			"//]]>\n" .
			"</script>\n";

        $extra_css = dcCore::app()->blog->settings->eventHandler->public_extra_css;
        if ($extra_css) {
            echo '<style type="text/css">' . "\n" . Html::escapeHTML($extra_css) . "\n" . "\n</style>\n";
        }
    }

    public static function publicBeforeDocument()
    {
        $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme, 'tplset');
        if (!empty($tplset) && is_dir(__DIR__ . '/../default-templates/' . $tplset)) {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/../default-templates/' . $tplset);
        } else {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), __DIR__ . '/../default-templates/' . DC_DEFAULT_TPLSET);
        }
    }

    // Before entry content
    public static function publicEntryBeforeContent()
    {
        return self::publicEntryContent('before');
    }

    // After entry content
    public static function publicEntryAfterContent()
    {
        return self::publicEntryContent('after');
    }

    // Add list of events / posts on entries
    protected static function publicEntryContent($place)
    {
        $s = dcCore::app()->blog->settings->eventHandler;

        if (!$s->active || $s->public_posts_of_event_place == '') {
            return;
        }

        $default_url_type = [
            'eventhandler_list',
            'eventhandler_single',
            'eventhandler_preview'
        ];

        // List of posts related to a event
        if (in_array(dcCore::app()->url->type, $default_url_type)) {
            if ($s->public_posts_of_event_place != $place) {
                return;
            }

            echo dcCore::app()->tpl->getData('eventhandler-postsofevent.html');
        } else { // List of events related to a post
            if ($s->public_events_of_post_place != $place) {
                return;
            }

            echo dcCore::app()->tpl->getData('eventhandler-eventsofpost.html');
        }
    }
}
