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
use dcCore;

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

        dcCore::app()->addBehavior('publicPrependV2', function (): void {
            // Localisation
            __('scheduled');
            __('ongoing');
            __('finished');
        });

        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'events']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'eventsOfPost']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'postsOfEvent']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'categories']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'calendar']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'map']);

        // Public behaviors
        dcCore::app()->addBehavior('publicHeadContent', [PublicBehaviors::class, 'publicHeadContent']);
        dcCore::app()->addBehavior('publicBeforeDocument', [PublicBehaviors::class, 'publicBeforeDocument']);
        dcCore::app()->addBehavior('publicEntryBeforeContent', [PublicBehaviors::class, 'publicEntryBeforeContent']);
        dcCore::app()->addBehavior('publicEntryAfterContent', [PublicBehaviors::class, 'publicEntryAfterContent']);

        // Missing values
        dcCore::app()->tpl->addValue('BlogTimezone', [Template::class, 'BlogTimezone']);

        // Page of events
        dcCore::app()->tpl->addBlock('EventsIf', [Template::class, 'EventsIf']);
        dcCore::app()->tpl->addValue('EventsMenuPeriod', [Template::class, 'EventsMenuPeriod']);
        dcCore::app()->tpl->addValue('EventsMenuSortOrder', [Template::class, 'EventsMenuSortOrder']);
        dcCore::app()->tpl->addValue('EventsFeedURL', [Template::class, 'EventsFeedURL']);
        dcCore::app()->tpl->addValue('EventsURL', [Template::class, 'EventsURL']);
        dcCore::app()->tpl->addValue('EventsPeriod', [Template::class, 'EventsPeriod']);
        dcCore::app()->tpl->addValue('EventsInterval', [Template::class, 'EventsInterval']);

        dcCore::app()->tpl->addBlock('EventsCount', [Template::class, 'EventsCount']);
        dcCore::app()->tpl->addBlock('EventsEntries', [Template::class, 'EventsEntries']);
        dcCore::app()->tpl->addBlock('EventsPagination', [Template::class, 'EventsPagination']);
        dcCore::app()->tpl->addBlock('EventsEntryIf', [Template::class, 'EventsEntryIf']);
        dcCore::app()->tpl->addBlock('EventsDateHeader', [Template::class, 'EventsDateHeader']);
        dcCore::app()->tpl->addBlock('EventsDateFooter', [Template::class, 'EventsDateFooter']);
        dcCore::app()->tpl->addValue('EventsEntryDate', [Template::class, 'EventsEntryDate']);
        dcCore::app()->tpl->addValue('EventsEntryTime', [Template::class, 'EventsEntryTime']);
        dcCore::app()->tpl->addValue('EventsEntryCategoryURL', [Template::class, 'EventsEntryCategoryURL']);
        dcCore::app()->tpl->addValue('EventsEntryAddress', [Template::class, 'EventsEntryAddress']);
        dcCore::app()->tpl->addValue('EventsEntryLatitude', [Template::class, 'EventsEntryLatitude']);
        dcCore::app()->tpl->addValue('EventsEntryLongitude', [Template::class, 'EventsEntryLongitude']);
        dcCore::app()->tpl->addValue('EventsEntryZoom', [Template::class, 'EventsEntryZoom']);
        dcCore::app()->tpl->addValue('EventsEntryDuration', [Template::class, 'EventsEntryDuration']);
        dcCore::app()->tpl->addValue('EventsEntryPeriod', [Template::class, 'EventsEntryPeriod']);
        dcCore::app()->tpl->addValue('EventsEntryMap', [Template::class, 'EventsEntryMap']);

        // Events of a post
        dcCore::app()->tpl->addBlock('EventsOfPost', [Template::class, 'EventsOfPost']);
        dcCore::app()->tpl->addBlock('EventsOfPostHeader', [Template::class, 'EventsOfPostHeader']);
        dcCore::app()->tpl->addBlock('EventsOfPostFooter', [Template::class, 'EventsOfPostFooter']);
        dcCore::app()->tpl->addBlock('EventOfPostIf', [Template::class, 'EventOfPostIf']);
        dcCore::app()->tpl->addValue('EventOfPostURL', [Template::class, 'EventOfPostURL']);
        dcCore::app()->tpl->addValue('EventOfPostTitle', [Template::class, 'EventOfPostTitle']);
        dcCore::app()->tpl->addValue('EventOfPostDate', [Template::class, 'EventOfPostDate']);
        dcCore::app()->tpl->addValue('EventOfPostTime', [Template::class, 'EventOfPostTime']);
        dcCore::app()->tpl->addValue('EventOfPostAuthorCommonName', [Template::class, 'EventOfPostAuthorCommonName']);
        dcCore::app()->tpl->addValue('EventOfPostAuthorLink', [Template::class, 'EventOfPostAuthorLink']);
        dcCore::app()->tpl->addValue('EventOfPostCategory', [Template::class, 'EventOfPostCategory']);
        dcCore::app()->tpl->addValue('EventOfPostCategoryURL', [Template::class, 'EventOfPostCategoryURL']);
        dcCore::app()->tpl->addValue('EventOfPostAddress', [Template::class, 'EventOfPostAddress']);
        dcCore::app()->tpl->addValue('EventOfPostDuration', [Template::class, 'EventOfPostDuration']);
        dcCore::app()->tpl->addValue('EventOfPostPeriod', [Template::class, 'EventOfPostPeriod']);

        // Posts of an event
        dcCore::app()->tpl->addBlock('PostsOfEvent', [Template::class, 'PostsOfEvent']);
        dcCore::app()->tpl->addBlock('PostsOfEventHeader', [Template::class, 'PostsOfEventHeader']);
        dcCore::app()->tpl->addBlock('PostsOfEventFooter', [Template::class, 'PostsOfEventFooter']);
        dcCore::app()->tpl->addBlock('PostOfEventIf', [Template::class, 'PostOfEventIf']);
        dcCore::app()->tpl->addValue('PostOfEventURL', [Template::class, 'PostOfEventURL']);
        dcCore::app()->tpl->addValue('PostOfEventTitle', [Template::class, 'PostOfEventTitle']);
        dcCore::app()->tpl->addValue('PostOfEventDate', [Template::class, 'PostOfEventDate']);
        dcCore::app()->tpl->addValue('PostOfEventTime', [Template::class, 'PostOfEventTime']);
        dcCore::app()->tpl->addValue('PostOfEventAuthorCommonName', [Template::class, 'PostOfEventAuthorCommonName']);
        dcCore::app()->tpl->addValue('PostOfEventAuthorLink', [Template::class, 'PostOfEventAuthorLink']);
        dcCore::app()->tpl->addValue('PostOfEventCategory', [Template::class, 'PostOfEventCategory']);
        dcCore::app()->tpl->addValue('PostOfEventCategoryURL', [Template::class, 'PostOfEventCategoryURL']);

        return true;
    }
}
