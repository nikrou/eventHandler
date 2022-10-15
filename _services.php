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

class eventHandlerRestMethods
{
    public static function unbindEventOfPost($get)
    {
        dcCore::app()->blog->settings->addNamespace('eventHandler');

        $post_id = isset($get['postId']) ? $get['postId'] : null;
        $event_id = isset($get['eventId']) ? $get['eventId'] : null;

        if (is_null($post_id)) {
            throw new Exception(__('No such post ID'));
        }
        if (is_null($event_id)) {
            throw new Exception(__('No such event ID'));
        }

        try {
            dcCore::app()->meta->delPostMeta($post_id, 'eventhandler', $event_id);
        } catch (Exception $e) {
            throw new Exception(__('An error occured when trying de unbind event'));
        }

        $rsp = new xmlTag();
        $rsp->value(__('Event removed from post'));
        return $rsp;
    }
}
