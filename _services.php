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

if (!defined('DC_CONTEXT_ADMIN')){return;}

class eventHandlerRestMethods
{
	public static function unbindEventOfPost($core,$get)
	{
		$core->blog->settings->addNamespace('eventHandler');

		$post_id = isset($get['postId']) ? $get['postId'] : null;
		$event_id = isset($get['eventId']) ? $get['eventId'] : null;

		if ($post_id === null)
		{
			throw new Exception(__('No such post ID'));
		}
		if ($event_id === null)
		{
			throw new Exception(__('No such event ID'));
		}

		try
		{
			$core->meta->delPostMeta($post_id,'eventhandler',$event_id);
		}
		catch (Exception $e)
		{
			throw new Exception(__('An error occured when trying de unbind event'));
		}

		$rsp = new xmlTag();
		$rsp->value(__('Event removed from post'));
 		return $rsp;
	}
}
