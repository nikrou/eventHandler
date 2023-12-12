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

use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;
use Dotclear\App;

class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem(Menus::MENU_BLOG);

        App::behavior()->addBehavior('initWidgets', Widgets::events(...));
        App::behavior()->addBehavior('initWidgets', Widgets::eventsOfPost(...));
        App::behavior()->addBehavior('initWidgets', Widgets::postsOfEvent(...));
        App::behavior()->addBehavior('initWidgets', Widgets::categories(...));
        App::behavior()->addBehavior('initWidgets', Widgets::calendar(...));
        App::behavior()->addBehavior('initWidgets', Widgets::map(...));

        // Admin Dashboard
        App::behavior()->addBehavior('adminDashboardFavoritesV2', AdminBehaviors::adminDashboardFavs(...));
        App::behavior()->addBehavior('pluginsToolsHeadersV2', AdminBehaviors::pluginsToolsHeadersV2(...));

        // Admin behaviors
        if (My::settings()->active) {
            App::behavior()->addBehavior('adminPageHTTPHeaderCSP', AdminBehaviors::adminPageHTTPHeaderCSP(...));
            App::behavior()->addBehavior('adminPostHeaders', AdminBehaviors::adminPostHeaders(...));
            App::behavior()->addBehavior('adminPostsActions', AdminBehaviors::adminPostsActions(...));
            App::behavior()->addBehavior('adminPostFormItems', AdminBehaviors::adminPostFormItems(...));
            App::behavior()->addBehavior('adminAfterPostCreate', AdminBehaviors::adminAfterPostSave(...));
            App::behavior()->addBehavior('adminAfterPostUpdate', AdminBehaviors::adminAfterPostSave(...));
            App::behavior()->addBehavior('adminBeforePostDelete', AdminBehaviors::adminBeforePostDelete(...));
        }

        return true;
    }
}
