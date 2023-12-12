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

use ArrayObject;
use Dotclear\Core\Process;
use Dotclear\App;

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

        App::url()->register('eventhandler_single', 'day', '^day/(.+)$', UrlHandler::eventSingle(...));
        App::url()->register('eventhandler_preview', 'daypreview', '^daypreview/(.+)$', UrlHandler::eventPreview(...));
        App::url()->register('eventhandler_list', 'days', '^days(|/.+)$', UrlHandler::eventList(...));
        App::url()->register('eventhandler_feed', 'daysfeed', '^daysfeed/(.+)$', UrlHandler::eventFeed(...));
        App::url()->register('eventhandler_pubrest', 'daysservice', '^daysservice/$', UrlHandler::eventService(...));

        App::postTypes()->setPostType('eventhandler', 'plugin.php?p=eventHandler&part=event&id=%d', App::url()->getBase('eventhandler_single') . '/%s');
        App::behavior()->addBehavior('templateCustomSortByAlias', self::eventHandlerCustomSortByAlias(...));

        App::rest()->addFunction('unbindEventOfPost', RestMethods::unbindEventOfPost(...));

        return true;
    }

    /**
    * @param ArrayObject<string, mixed> $alias
    */
    public static function eventHandlerCustomSortByAlias(ArrayObject $alias): void
    {
        $alias['eventhandler'] = [
            'startdt' => 'event_startdt',
            'enddt' => 'event_enddt',
        ];
    }
}
