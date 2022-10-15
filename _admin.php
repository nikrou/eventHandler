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

// Load _wigdets.php
if (dcCore::app()->blog->settings->eventHandler->active) {
    include_once(__DIR__ . '/_widgets.php');
}

// Admin menu
dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
    __('Event handler'),
    'plugin.php?p=eventHandler',
    ['index.php?pf=eventHandler/icon.svg', 'index.php?pf=eventHandler/icon-dark.svg'],
    preg_match('/plugin.php\?p=eventHandler(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_USAGE, dcAuth::PERMISSION_CONTENT_ADMIN]), dcCore::app()->blog->id)
);

// Admin Dashboard
dcCore::app()->addBehavior('adminDashboardIconsV2', [adminEventHandler::class, 'adminDashboardIcons']);
dcCore::app()->addBehavior('adminDashboardFavoritesV2', [adminEventHandler::class, 'adminDashboardFavs']);

// Admin behaviors
if (dcCore::app()->blog->settings->eventHandler->active) {
    dcCore::app()->addBehavior('adminPageHTTPHeaderCSP', [adminEventHandler::class, 'adminPageHTTPHeaderCSP']);
    dcCore::app()->addBehavior('adminPostHeaders', [adminEventHandler::class, 'adminPostHeaders']);
    dcCore::app()->addBehavior('adminPostsActionsCombo', [adminEventHandler::class, 'adminPostsActionsCombo']);
    dcCore::app()->addBehavior('adminPostsActionsPage', [adminEventHandler::class, 'adminPostsActionsPage']);
    dcCore::app()->addBehavior('adminPostFormItems', [adminEventHandler::class, 'adminPostFormItems']);
    dcCore::app()->addBehavior('adminAfterPostCreate', [adminEventHandler::class, 'adminAfterPostSave']);
    dcCore::app()->addBehavior('adminAfterPostUpdate', [adminEventHandler::class, 'adminAfterPostSave']);
    dcCore::app()->addBehavior('adminBeforePostDelete', [adminEventHandler::class, 'adminBeforePostDelete']);
}
