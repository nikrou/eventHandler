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

use Dotclear\Core\Process;
use Dotclear\App;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('publicPrependV2', function (): void {
            // Localisation
            __('scheduled');
            __('ongoing');
            __('finished');
        });

        App::behavior()->addBehavior('initWidgets', Widgets::events(...));
        App::behavior()->addBehavior('initWidgets', Widgets::eventsOfPost(...));
        App::behavior()->addBehavior('initWidgets', Widgets::postsOfEvent(...));
        App::behavior()->addBehavior('initWidgets', Widgets::categories(...));
        App::behavior()->addBehavior('initWidgets', Widgets::calendar(...));
        App::behavior()->addBehavior('initWidgets', Widgets::map(...));

        // Public behaviors
        App::behavior()->addBehavior('publicHeadContent', PublicBehaviors::publicHeadContent(...));
        App::behavior()->addBehavior('publicBeforeDocument', PublicBehaviors::publicBeforeDocument(...));
        App::behavior()->addBehavior('publicEntryBeforeContent', PublicBehaviors::publicEntryBeforeContent(...));
        App::behavior()->addBehavior('publicEntryAfterContent', PublicBehaviors::publicEntryAfterContent(...));

        // Missing values
        App::frontend()->template()->addValue('BlogTimezone', Template::BlogTimezone(...));

        // Page of events
        App::frontend()->template()->addBlock('EventsIf', Template::EventsIf(...));
        App::frontend()->template()->addValue('EventsMenuPeriod', Template::EventsMenuPeriod(...));
        App::frontend()->template()->addValue('EventsMenuSortOrder', Template::EventsMenuSortOrder(...));
        App::frontend()->template()->addValue('EventsFeedURL', Template::EventsFeedURL(...));
        App::frontend()->template()->addValue('EventsURL', Template::EventsURL(...));
        App::frontend()->template()->addValue('EventsPeriod', Template::EventsPeriod(...));
        App::frontend()->template()->addValue('EventsInterval', Template::EventsInterval(...));

        App::frontend()->template()->addBlock('EventsCount', Template::EventsCount(...));
        App::frontend()->template()->addBlock('EventsEntries', Template::EventsEntries(...));
        App::frontend()->template()->addBlock('EventsPagination', Template::EventsPagination(...));
        App::frontend()->template()->addBlock('EventsEntryIf', Template::EventsEntryIf(...));
        App::frontend()->template()->addBlock('EventsDateHeader', Template::EventsDateHeader(...));
        App::frontend()->template()->addBlock('EventsDateFooter', Template::EventsDateFooter(...));
        App::frontend()->template()->addValue('EventsEntryDate', Template::EventsEntryDate(...));
        App::frontend()->template()->addValue('EventsEntryTime', Template::EventsEntryTime(...));
        App::frontend()->template()->addValue('EventsEntryCategoryURL', Template::EventsEntryCategoryURL(...));
        App::frontend()->template()->addValue('EventsEntryAddress', Template::EventsEntryAddress(...));
        App::frontend()->template()->addValue('EventsEntryLatitude', Template::EventsEntryLatitude(...));
        App::frontend()->template()->addValue('EventsEntryLongitude', Template::EventsEntryLongitude(...));
        App::frontend()->template()->addValue('EventsEntryZoom', Template::EventsEntryZoom(...));
        App::frontend()->template()->addValue('EventsEntryDuration', Template::EventsEntryDuration(...));
        App::frontend()->template()->addValue('EventsEntryPeriod', Template::EventsEntryPeriod(...));
        App::frontend()->template()->addValue('EventsEntryMap', Template::EventsEntryMap(...));

        // Events of a post
        App::frontend()->template()->addBlock('EventsOfPost', Template::EventsOfPost(...));
        App::frontend()->template()->addBlock('EventsOfPostHeader', Template::EventsOfPostHeader(...));
        App::frontend()->template()->addBlock('EventsOfPostFooter', Template::EventsOfPostFooter(...));
        App::frontend()->template()->addBlock('EventOfPostIf', Template::EventOfPostIf(...));
        App::frontend()->template()->addValue('EventOfPostURL', Template::EventOfPostURL(...));
        App::frontend()->template()->addValue('EventOfPostTitle', Template::EventOfPostTitle(...));
        App::frontend()->template()->addValue('EventOfPostDate', Template::EventOfPostDate(...));
        App::frontend()->template()->addValue('EventOfPostTime', Template::EventOfPostTime(...));
        App::frontend()->template()->addValue('EventOfPostAuthorCommonName', Template::EventOfPostAuthorCommonName(...));
        App::frontend()->template()->addValue('EventOfPostAuthorLink', Template::EventOfPostAuthorLink(...));
        App::frontend()->template()->addValue('EventOfPostCategory', Template::EventOfPostCategory(...));
        App::frontend()->template()->addValue('EventOfPostCategoryURL', Template::EventOfPostCategoryURL(...));
        App::frontend()->template()->addValue('EventOfPostAddress', Template::EventOfPostAddress(...));
        App::frontend()->template()->addValue('EventOfPostDuration', Template::EventOfPostDuration(...));
        App::frontend()->template()->addValue('EventOfPostPeriod', Template::EventOfPostPeriod(...));

        // Posts of an event
        App::frontend()->template()->addBlock('PostsOfEvent', Template::PostsOfEvent(...));
        App::frontend()->template()->addBlock('PostsOfEventHeader', Template::PostsOfEventHeader(...));
        App::frontend()->template()->addBlock('PostsOfEventFooter', Template::PostsOfEventFooter(...));
        App::frontend()->template()->addBlock('PostOfEventIf', Template::PostOfEventIf(...));
        App::frontend()->template()->addValue('PostOfEventURL', Template::PostOfEventURL(...));
        App::frontend()->template()->addValue('PostOfEventTitle', Template::PostOfEventTitle(...));
        App::frontend()->template()->addValue('PostOfEventDate', Template::PostOfEventDate(...));
        App::frontend()->template()->addValue('PostOfEventTime', Template::PostOfEventTime(...));
        App::frontend()->template()->addValue('PostOfEventAuthorCommonName', Template::PostOfEventAuthorCommonName(...));
        App::frontend()->template()->addValue('PostOfEventAuthorLink', Template::PostOfEventAuthorLink(...));
        App::frontend()->template()->addValue('PostOfEventCategory', Template::PostOfEventCategory(...));
        App::frontend()->template()->addValue('PostOfEventCategoryURL', Template::PostOfEventCategoryURL(...));

        return true;
    }
}
