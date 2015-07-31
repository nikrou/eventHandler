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

global $__autoload, $core;

# Main class
$__autoload['eventHandler'] = dirname(__FILE__).'/inc/class.eventhandler.php';

$__autoload['eventHandlerAdminWidgets'] = dirname(__FILE__).'/inc/class.eventhandler.admin.widgets.php';
$__autoload['eventHandlerPublicWidgets'] = dirname(__FILE__).'/inc/class.eventhandler.public.widgets.php';
$__autoload['adminEventHandlerMiniList'] = dirname(__FILE__).'/inc/class.admin.eventhandler.minilist.php';
$__autoload['adminEventHandlertList'] = dirname(__FILE__).'/inc/class.admin.eventhandler.list.php';
$__autoload['adminEventHandlertPostsList'] = dirname(__FILE__).'/inc/class.admin.eventhandler.posts.list.php';

$__autoload['adminEventHandler'] = dirname(__FILE__).'/inc/class.admin.eventhandler.php';
$__autoload['publicEventHandler'] = dirname(__FILE__).'/inc/class.public.eventhandler.php';
$__autoload['tplEventHandler'] = dirname(__FILE__).'/inc/class.tpl.eventhandler.php';
$__autoload['urlEventHandler'] = dirname(__FILE__).'/inc/class.url.eventhandler.php';
$__autoload['rsExtEventHandlerPublic'] = dirname(__FILE__).'/inc/lib.eventhandler.rs.extension.php';
$__autoload['eventHandlerCalendar'] = dirname(__FILE__).'/inc/lib.eventhandler.calendar.php';
$__autoload['eventHandlerRestMethods'] = dirname(__FILE__).'/_services.php';
$__autoload['eventHandlerPublicRest'] = dirname(__FILE__).'/inc/lib.eventhandler.pubrest.php';


# Public page for an event
$core->url->register('eventhandler_single','day','^day/(.+)$',array('urlEventHandler','eventSingle'));
# Preview page
$core->url->register('eventhandler_preview','daypreview','^daypreview/(.+)$',array('urlEventHandler','eventPreview'));
# Public page for list of events
$core->url->register('eventhandler_list','days','^days(|/.+)$',array('urlEventHandler','eventList'));
# Feed of events
$core->url->register('eventhandler_feed','daysfeed','^daysfeed/(.+)$',array('urlEventHandler','eventFeed'));
# Public rest service
$core->url->register('eventhandler_pubrest','daysservice','^daysservice/$',array('urlEventHandler','eventService'));

# Add new post type for event
$core->setPostType('eventhandler','plugin.php?p=eventHandler&part=event&id=%d',$core->url->getBase('eventhandler_single').'/%s');
# Add sort ability on template
$core->addBehavior('templateCustomSortByAlias','eventHandlerCustomSortByAlias');

function eventHandlerCustomSortByAlias($alias) {
	$alias['eventhandler'] = array(
		'startdt' => 'event_startdt',
		'enddt' => 'event_enddt'
    );
}

# Admin rest method
$core->rest->addFunction('unbindEventOfPost',array('eventHandlerRestMethods','unbindEventOfPost'));
