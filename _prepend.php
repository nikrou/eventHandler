<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of eventHandler, a plugin for Dotclear 2.
 *
 *  Copyright(c) 2014-2022 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
 *
 *  Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 *  contact@jcdenis.fr http://jcd.lv
 *
 *  Licensed under the GPL version 2.0 license.
 *  A copy of this license is available in LICENSE file or at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

// Main class
Clearbricks::lib()->autoload(
    [
        'eventHandler' => __DIR__ . '/inc/class.eventhandler.php',
        'eventHandlerAdminWidgets' => __DIR__ . '/inc/class.eventhandler.admin.widgets.php',
        'eventHandlerPublicWidgets' => __DIR__ . '/inc/class.eventhandler.public.widgets.php',
        'adminEventHandlerMiniList' => __DIR__ . '/inc/class.admin.eventhandler.minilist.php',
        'adminEventHandlerList' => __DIR__ . '/inc/class.admin.eventhandler.list.php',
        'adminEventHandlertPostsList' => __DIR__ . '/inc/class.admin.eventhandler.posts.list.php',

        'adminEventHandler' => __DIR__ . '/inc/class.admin.eventhandler.php',
        'publicEventHandler' => __DIR__ . '/inc/class.public.eventhandler.php',
        'tplEventHandler' => __DIR__ . '/inc/class.tpl.eventhandler.php',
        'urlEventHandler' => __DIR__ . '/inc/class.url.eventhandler.php',
        'rsExtEventHandlerPublic' => __DIR__ . '/inc/lib.eventhandler.rs.extension.php',
        'eventHandlerCalendar' => __DIR__ . '/inc/lib.eventhandler.calendar.php',
        'eventHandlerRestMethods' => __DIR__ . '/_services.php',
        'eventHandlerPublicRest' => __DIR__ . '/inc/lib.eventhandler.pubrest.php',
    ]
);


// Public page for an event
dcCore::app()->url->register('eventhandler_single', 'day', '^day/(.+)$', [urlEventHandler::class, 'eventSingle']);
// Preview page
dcCore::app()->url->register('eventhandler_preview', 'daypreview', '^daypreview/(.+)$', [urlEventHandler::class, 'eventPreview']);
// Public page for list of events
dcCore::app()->url->register('eventhandler_list', 'days', '^days(|/.+)$', [urlEventHandler::class, 'eventList']);
// Feed of events
dcCore::app()->url->register('eventhandler_feed', 'daysfeed', '^daysfeed/(.+)$', [urlEventHandler::class, 'eventFeed']);
// Public rest service
dcCore::app()->url->register('eventhandler_pubrest', 'daysservice', '^daysservice/$', [urlEventHandler::class, 'eventService']);

// Add new post type for event
dcCore::app()->setPostType('eventhandler', 'plugin.php?p=eventHandler&part=event&id=%d', dcCore::app()->url->getBase('eventhandler_single') . '/%s');
// Add sort ability on template
dcCore::app()->addBehavior('templateCustomSortByAlias', 'eventHandlerCustomSortByAlias');

function eventHandlerCustomSortByAlias($alias)
{
    $alias['eventhandler'] = [
        'startdt' => 'event_startdt',
        'enddt' => 'event_enddt'
    ];
}

// Admin rest method
dcCore::app()->rest->addFunction('unbindEventOfPost', [eventHandlerRestMethods::class, 'unbindEventOfPost']);
