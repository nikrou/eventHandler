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

if (!defined('DC_RC_PATH')) {
    return;
}

$core->addBehavior('initWidgets', array('eventHandlerAdminWidgets', 'events'));
$core->addBehavior('initWidgets', array('eventHandlerAdminWidgets', 'eventsOfPost'));
$core->addBehavior('initWidgets', array('eventHandlerAdminWidgets', 'postsOfEvent'));
$core->addBehavior('initWidgets', array('eventHandlerAdminWidgets', 'categories'));
$core->addBehavior('initWidgets', array('eventHandlerAdminWidgets', 'calendar'));
$core->addBehavior('initWidgets', array('eventHandlerAdminWidgets', 'map'));
