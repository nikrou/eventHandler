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

dcCore::app()->addBehavior('initWidgets', [eventHandlerAdminWidgets::class, 'events']);
dcCore::app()->addBehavior('initWidgets', [eventHandlerAdminWidgets::class, 'eventsOfPost']);
dcCore::app()->addBehavior('initWidgets', [eventHandlerAdminWidgets::class, 'postsOfEvent']);
dcCore::app()->addBehavior('initWidgets', [eventHandlerAdminWidgets::class, 'categories']);
dcCore::app()->addBehavior('initWidgets', [eventHandlerAdminWidgets::class, 'calendar']);
dcCore::app()->addBehavior('initWidgets', [eventHandlerAdminWidgets::class, 'map']);
