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

// set ns
dcCore::app()->blog->settings->addNamespace('eventHandler');
// Localisation
__('scheduled');
__('ongoing');
__('finished');

// Load _wigdets.php
if (dcCore::app()->blog->settings->eventHandler->active) {
    include_once(__DIR__ . '/_widgets.php');
}

// Public behaviors
dcCore::app()->addBehavior('publicHeadContent', [publicEventHandler::class, 'publicHeadContent']);
dcCore::app()->addBehavior('publicBeforeDocument', [publicEventHandler::class, 'publicBeforeDocument']);
dcCore::app()->addBehavior('publicEntryBeforeContent', [publicEventHandler::class, 'publicEntryBeforeContent']);
dcCore::app()->addBehavior('publicEntryAfterContent', [publicEventHandler::class, 'publicEntryAfterContent']);

// Missing values
dcCore::app()->tpl->addValue('BlogTimezone', [tplEventHandler::class, 'BlogTimezone']);

// Page of events
dcCore::app()->tpl->addBlock('EventsIf', [tplEventHandler::class, 'EventsIf']);
dcCore::app()->tpl->addValue('EventsMenuPeriod', [tplEventHandler::class, 'EventsMenuPeriod']);
dcCore::app()->tpl->addValue('EventsMenuSortOrder', [tplEventHandler::class, 'EventsMenuSortOrder']);
dcCore::app()->tpl->addValue('EventsFeedURL', [tplEventHandler::class, 'EventsFeedURL']);
dcCore::app()->tpl->addValue('EventsURL', [tplEventHandler::class, 'EventsURL']);
dcCore::app()->tpl->addValue('EventsPeriod', [tplEventHandler::class, 'EventsPeriod']);
dcCore::app()->tpl->addValue('EventsInterval', [tplEventHandler::class, 'EventsInterval']);

dcCore::app()->tpl->addBlock('EventsCount', [tplEventHandler::class, 'EventsCount']);
dcCore::app()->tpl->addBlock('EventsEntries', [tplEventHandler::class, 'EventsEntries']);
dcCore::app()->tpl->addBlock('EventsPagination', [tplEventHandler::class, 'EventsPagination']);
dcCore::app()->tpl->addBlock('EventsEntryIf', [tplEventHandler::class, 'EventsEntryIf']);
dcCore::app()->tpl->addBlock('EventsDateHeader', [tplEventHandler::class, 'EventsDateHeader']);
dcCore::app()->tpl->addBlock('EventsDateFooter', [tplEventHandler::class, 'EventsDateFooter']);
dcCore::app()->tpl->addValue('EventsEntryDate', [tplEventHandler::class, 'EventsEntryDate']);
dcCore::app()->tpl->addValue('EventsEntryTime', [tplEventHandler::class, 'EventsEntryTime']);
dcCore::app()->tpl->addValue('EventsEntryCategoryURL', [tplEventHandler::class, 'EventsEntryCategoryURL']);
dcCore::app()->tpl->addValue('EventsEntryAddress', [tplEventHandler::class, 'EventsEntryAddress']);
dcCore::app()->tpl->addValue('EventsEntryLatitude', [tplEventHandler::class, 'EventsEntryLatitude']);
dcCore::app()->tpl->addValue('EventsEntryLongitude', [tplEventHandler::class, 'EventsEntryLongitude']);
dcCore::app()->tpl->addValue('EventsEntryZoom', [tplEventHandler::class, 'EventsEntryZoom']);
dcCore::app()->tpl->addValue('EventsEntryDuration', [tplEventHandler::class, 'EventsEntryDuration']);
dcCore::app()->tpl->addValue('EventsEntryPeriod', [tplEventHandler::class, 'EventsEntryPeriod']);
dcCore::app()->tpl->addValue('EventsEntryMap', [tplEventHandler::class, 'EventsEntryMap']);

// Events of a post
dcCore::app()->tpl->addBlock('EventsOfPost', [tplEventHandler::class, 'EventsOfPost']);
dcCore::app()->tpl->addBlock('EventsOfPostHeader', [tplEventHandler::class, 'EventsOfPostHeader']);
dcCore::app()->tpl->addBlock('EventsOfPostFooter', [tplEventHandler::class, 'EventsOfPostFooter']);
dcCore::app()->tpl->addBlock('EventOfPostIf', [tplEventHandler::class, 'EventOfPostIf']);
dcCore::app()->tpl->addValue('EventOfPostURL', [tplEventHandler::class, 'EventOfPostURL']);
dcCore::app()->tpl->addValue('EventOfPostTitle', [tplEventHandler::class, 'EventOfPostTitle']);
dcCore::app()->tpl->addValue('EventOfPostDate', [tplEventHandler::class, 'EventOfPostDate']);
dcCore::app()->tpl->addValue('EventOfPostTime', [tplEventHandler::class, 'EventOfPostTime']);
dcCore::app()->tpl->addValue('EventOfPostAuthorCommonName', [tplEventHandler::class, 'EventOfPostAuthorCommonName']);
dcCore::app()->tpl->addValue('EventOfPostAuthorLink', [tplEventHandler::class, 'EventOfPostAuthorLink']);
dcCore::app()->tpl->addValue('EventOfPostCategory', [tplEventHandler::class, 'EventOfPostCategory']);
dcCore::app()->tpl->addValue('EventOfPostCategoryURL', [tplEventHandler::class, 'EventOfPostCategoryURL']);
dcCore::app()->tpl->addValue('EventOfPostAddress', [tplEventHandler::class, 'EventOfPostAddress']);
dcCore::app()->tpl->addValue('EventOfPostDuration', [tplEventHandler::class, 'EventOfPostDuration']);
dcCore::app()->tpl->addValue('EventOfPostPeriod', [tplEventHandler::class, 'EventOfPostPeriod']);

// Posts of an event
dcCore::app()->tpl->addBlock('PostsOfEvent', [tplEventHandler::class, 'PostsOfEvent']);
dcCore::app()->tpl->addBlock('PostsOfEventHeader', [tplEventHandler::class, 'PostsOfEventHeader']);
dcCore::app()->tpl->addBlock('PostsOfEventFooter', [tplEventHandler::class, 'PostsOfEventFooter']);
dcCore::app()->tpl->addBlock('PostOfEventIf', [tplEventHandler::class, 'PostOfEventIf']);
dcCore::app()->tpl->addValue('PostOfEventURL', [tplEventHandler::class, 'PostOfEventURL']);
dcCore::app()->tpl->addValue('PostOfEventTitle', [tplEventHandler::class, 'PostOfEventTitle']);
dcCore::app()->tpl->addValue('PostOfEventDate', [tplEventHandler::class, 'PostOfEventDate']);
dcCore::app()->tpl->addValue('PostOfEventTime', [tplEventHandler::class, 'PostOfEventTime']);
dcCore::app()->tpl->addValue('PostOfEventAuthorCommonName', [tplEventHandler::class, 'PostOfEventAuthorCommonName']);
dcCore::app()->tpl->addValue('PostOfEventAuthorLink', [tplEventHandler::class, 'PostOfEventAuthorLink']);
dcCore::app()->tpl->addValue('PostOfEventCategory', [tplEventHandler::class, 'PostOfEventCategory']);
dcCore::app()->tpl->addValue('PostOfEventCategoryURL', [tplEventHandler::class, 'PostOfEventCategoryURL']);
