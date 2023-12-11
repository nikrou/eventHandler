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
use Exception;

class RestMethods
{
    public static function unbindEventOfPost(array $unused, array $post): array
    {
        dcCore::app()->blog->settings->addNamespace('eventHandler');

        $post_id = $post['postId'] ?? null;
        $event_id = $post['eventId'] ?? null;

        if (is_null($post_id)) {
            throw new \Exception(__('No such post ID'));
        }

        if (is_null($event_id)) {
            throw new \Exception(__('No such event ID'));
        }

        try {
            dcCore::app()->meta->delPostMeta($post_id, 'eventhandler', $event_id);
        } catch (Exception) {
            throw new Exception(__('An error occured when trying de unbind event'));
        }

        return ['message' => __('Event removed from post')];
    }
}
