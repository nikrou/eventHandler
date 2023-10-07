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

use dcCore;
use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

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

        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'events']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'eventsOfPost']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'postsOfEvent']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'categories']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'calendar']);
        dcCore::app()->addBehavior('initWidgets', [Widgets::class, 'map']);

        // Admin Dashboard
        dcCore::app()->addBehavior('adminDashboardIconsV2', [AdminBehaviors::class, 'adminDashboardIcons']);
        dcCore::app()->addBehavior('adminDashboardFavoritesV2', [AdminBehaviors::class, 'adminDashboardFavs']);

        // Admin behaviors
        if (dcCore::app()->blog->settings->eventHandler->active) {
            dcCore::app()->addBehavior('adminPageHTTPHeaderCSP', [AdminBehaviors::class, 'adminPageHTTPHeaderCSP']);
            dcCore::app()->addBehavior('adminPostHeaders', [AdminBehaviors::class, 'adminPostHeaders']);
            dcCore::app()->addBehavior('adminPostsActions', [AdminBehaviors::class, 'adminPostsActions']);
            dcCore::app()->addBehavior('adminPostsActionsPage', [AdminBehaviors::class, 'adminPostsActions']);
            dcCore::app()->addBehavior('adminPostFormItems', [AdminBehaviors::class, 'adminPostFormItems']);
            dcCore::app()->addBehavior('adminAfterPostCreate', [AdminBehaviors::class, 'adminAfterPostSave']);
            dcCore::app()->addBehavior('adminAfterPostUpdate', [AdminBehaviors::class, 'adminAfterPostSave']);
            dcCore::app()->addBehavior('adminBeforePostDelete', [AdminBehaviors::class, 'adminBeforePostDelete']);
        }

        return true;
    }
}
