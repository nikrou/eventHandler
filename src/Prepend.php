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
use Dotclear\Core\Process;

class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Public page for an event
        dcCore::app()->url->register('eventhandler_single', 'day', '^day/(.+)$', [UrlHandler::class, 'eventSingle']);
        // Preview page
        dcCore::app()->url->register('eventhandler_preview', 'daypreview', '^daypreview/(.+)$', [UrlHandler::class, 'eventPreview']);
        // Public page for list of events
        dcCore::app()->url->register('eventhandler_list', 'days', '^days(|/.+)$', [UrlHandler::class, 'eventList']);
        // Feed of events
        dcCore::app()->url->register('eventhandler_feed', 'daysfeed', '^daysfeed/(.+)$', [UrlHandler::class, 'eventFeed']);
        // Public rest service
        dcCore::app()->url->register('eventhandler_pubrest', 'daysservice', '^daysservice/$', [UrlHandler::class, 'eventService']);

        // Add new post type for event
        dcCore::app()->setPostType('eventhandler', 'plugin.php?p=eventHandler&part=event&id=%d', dcCore::app()->url->getBase('eventhandler_single') . '/%s');
        // Add sort ability on template
        dcCore::app()->addBehavior('templateCustomSortByAlias', [self::class, 'eventHandlerCustomSortByAlias']);

        // Admin rest method
        dcCore::app()->rest->addFunction('unbindEventOfPost', [RestMethods::class, 'unbindEventOfPost']);

        return true;
    }

    public function eventHandlerCustomSortByAlias($alias)
    {
        $alias['eventhandler'] = [
            'startdt' => 'event_startdt',
            'enddt' => 'event_enddt'
        ];
    }
}
