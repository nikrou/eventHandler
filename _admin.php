<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2016 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
#
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

# set ns
$core->blog->settings->addNamespace('eventHandler');

# Load _wigdets.php
if ($core->blog->settings->eventHandler->active) {
    include_once(__DIR__.'/_widgets.php');
}

# Admin menu
$_menu['Blog']->addItem(
	__('Event handler'),
	'plugin.php?p=eventHandler','index.php?pf=eventHandler/icon.png',
	preg_match('/plugin.php\?p=eventHandler(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id)
);

# Admin Dashboard
$core->addBehavior('adminDashboardIcons', array('adminEventHandler', 'adminDashboardIcons'));
$core->addBehavior('adminDashboardFavs', array('adminEventHandler', 'adminDashboardFavs'));

# Admin behaviors
if ($core->blog->settings->eventHandler->active) {
    $core->addBehavior('adminPageHTTPHeaderCSP', array('adminEventHandler', 'adminPageHTTPHeaderCSP'));
	$core->addBehavior('adminPostHeaders', array('adminEventHandler', 'adminPostHeaders'));
	$core->addBehavior('adminPostsActionsCombo', array('adminEventHandler', 'adminPostsActionsCombo'));
	$core->addBehavior('adminPostsActionsPage', array('adminEventHandler', 'adminPostsActionsPage'));
	$core->addBehavior('adminPostFormItems', array('adminEventHandler', 'adminPostFormItems'));
	$core->addBehavior('adminAfterPostCreate', array('adminEventHandler', 'adminAfterPostSave'));
	$core->addBehavior('adminAfterPostUpdate', array('adminEventHandler', 'adminAfterPostSave'));
	$core->addBehavior('adminBeforePostDelete', array('adminEventHandler', 'adminBeforePostDelete'));
}
