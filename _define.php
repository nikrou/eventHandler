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

$this->registerModule(
    "EventHandler", // Name
    "Manage events on your blog", // Description
    "JC Denis, Nicolas Roudaire", // Author
    '2022.10.15', // Version
    // Properties
    [
        'permissions' => dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_CONTENT_ADMIN, dcAuth::PERMISSION_USAGE]),
        'type' => 'plugin',
        'dc_min' => '2.24',
        'support' => 'http://forum.dotclear.org/viewtopic.php?id=43296',
        'details' => 'http://plugins.dotaddict.org/dc2/details/eventHandler'
    ]
);
