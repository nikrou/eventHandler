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

use Dotclear\App;
use Dotclear\Core\Auth;

$this->registerModule(
    "EventHandler", // Name
    "Manage events on your blog", // Description
    "JC Denis, Nicolas Roudaire", // Author
    '2023.12.22', // Version
    // Properties
    [
        'permissions' => App::auth()->makePermissions([Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_USAGE]),
        'dc_min' => '2.28',
        'requires' => [['core', '2.28']],
        'type' => 'plugin',
        'repository' => 'https://github.com/nikrou/eventHandler',
        'support' => 'http://forum.dotclear.org/viewtopic.php?id=43296',
        'details' => 'http://plugins.dotaddict.org/dc2/details/eventHandler',
    ]
);
