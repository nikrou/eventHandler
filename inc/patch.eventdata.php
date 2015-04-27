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

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'settings'){return;}

$eventdata_import = null;
if ($core->plugins->moduleExists('eventdata')) {

	$eventdata = new dcEventdata($core);

	# Get events of eventdata
	$events = $eventdata->getEventdata();

	while($events->fetch())
	{
		# get related post
		$post = $core->blog->getPosts(array('post_id'=>$events->post_id,'psot_type'=>''));
		if (!$post->isEmpty())
		{
			# post part of new event
			$cur_post = $core->con->openCursor($core->prefix.'post');

			$cur_post->post_title = $post->post_title;
			$cur_post->cat_id = $post->cat_id;
			$cur_post->post_dt = $post->post_dt;
			$cur_post->post_format = $post->post_format;
			$cur_post->post_password = $post->post_password;
			$cur_post->post_lang = $post->post_lang;
			$cur_post->post_title = $post->post_title;
			$cur_post->post_excerpt = $post->post_excerpt;
			$cur_post->post_excerpt_xhtml = $post->post_excerpt_xhtml;
			$cur_post->post_content = $post->post_content;
			$cur_post->post_content_xhtml = $post->post_content_xhtml;
			$cur_post->post_notes = $post->post_notes;
			$cur_post->post_status = $post->post_status;
			$cur_post->post_selected = (integer) $post->post_selected;
			$cur_post->post_open_comment = 0;
			$cur_post->post_open_tb = 0;
			$cur_post->user_id = $post->user_id;

			# event part of new event
			$cur_event = $core->con->openCursor($core->prefix.'eventhandler');

			$cur_event->event_startdt = $events->eventdata_start;
			$cur_event->event_enddt = $events->eventdata_end;
			$cur_event->event_address = $events->eventdata_location;
			$cur_event->event_latitude = '';
			$cur_event->event_longitude = '';

			try
			{
				# --BEHAVIOR-- adminBeforeEventHandlerCreate
				$core->callBehavior('adminBeforeEventHandlerCreate',$cur_post,$cur_event);

				$return_id = $eventHandler->addEvent($cur_post,$cur_event);

				# --BEHAVIOR-- adminAfterEventHandlerCreate
				$core->callBehavior('adminAfterEventHandlerCreate',$cur_post,$cur_event,$return_id);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			$eventdata_import = true;
			$core->blog->settings->eventHandler->put('eventdata_import_done',true,'boolean');
		}
	}
}
