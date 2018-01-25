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

$core->tpl->addBlock('EventsCount',array('tplEventHandler','EventsCount'));
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
$core->tpl->addValue('EventsEntryZoom',array('tplEventHandler','EventsEntryZoom'));
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
