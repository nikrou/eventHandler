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

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'event'){return;}

# Post part
$post_id = '';
$cat_id = '';
$post_dt = '';
$post_format = $core->auth->getOption('post_format');
$post_editor = $core->auth->getOption('editor');
$post_password = '';
$post_url = '';
$post_lang = $core->auth->getInfo('user_lang');
$post_title = '';
$post_excerpt = '';
$post_excerpt_xhtml = '';
$post_content = '';
$post_content_xhtml = '';
$post_notes = '';
$post_status = $core->auth->getInfo('user_post_status');
$post_selected = false;

# This 3 options are disabled
$post_open_comment = 0;
$post_open_tb = 0;
$post_media = array();

# Event part
$event_startdt = '';
$event_enddt = '';
$event_address = '';
$event_latitude = '';
$event_longitude = '';
$event_zoom = 0;

$page_title = __('New event');

$can_view_page = true;
$can_edit_post = $core->auth->check('usage,contentadmin',$core->blog->id);
$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);
$can_delete = false;

$post_headlink = '<link rel="%s" title="%s" href="'.$p_url.'&amp;part=event&amp;id=%s" />';
$post_link = '<a href="'.$p_url.'&amp;part=event&amp;id=%s" title="%s">%s</a>';
$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# settings
$events_default_zoom = $core->blog->settings->eventHandler->public_map_zoom;
$map_provider = $core->blog->settings->eventHandler->map_provider?$core->blog->settings->eventHandler->map_provider:'googlemaps';
$map_api_key = $core->blog->settings->eventHandler->map_api_key;

$preview_url = '';

# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

# Getting categories
$categories_combo = array('&nbsp;' => '');
try {
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
	while ($categories->fetch()) {
		$categories_combo[] = new formSelectOption(
			str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
			$categories->cat_id
		);
	}
} catch (Exception $e) {
}

# Status combo
foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# Formaters combo
if (version_compare(DC_VERSION, '2.7', '>=')) {
	$core_formaters = $core->getFormaters();
	$available_formats = array('' => '');
	foreach ($core_formaters as $editor => $formats) {
		foreach ($formats as $format) {
			$available_formats[$format] = $format;
		}
	}
} else {
	foreach ($core->getFormaters() as $v) {
		$available_formats[$v] = $v;
	}
}

# Languages combo
$rs = $core->blog->getLangs(array('order' => 'asc'));
$all_langs = l10n::getISOcodes(0,1);
$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
while ($rs->fetch()) {
	if (isset($all_langs[$rs->post_lang])) {
		$lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
		unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
	} else {
		$lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
	}
}
unset($all_langs);
unset($rs);

# Change a post to an event
$change = false;
if (!empty($_REQUEST['from_id'])) {
	$post = $core->blog->getPosts(array('post_id' => (integer) $_REQUEST['from_id'], 'post_type' => ''));

	if ($post->isEmpty()) {
		$core->error->add(__('This entry does not exist.'));
		unset($post);
		$can_view_page = false;
	} else {
		$change = true;
	}
}

# Get entry informations
if (!empty($_REQUEST['id'])) {
	$post = $eventHandler->getEvents(array('post_id' => (integer) $_REQUEST['id']));

	if ($post->isEmpty()) {
		$core->error->add(__('This event does not exist.'));
		unset($post);
		$can_view_page = false;
	}
}

if (isset($post)) {
	$post_id = $post->post_id;
	$cat_id = $post->cat_id;
	$post_dt = date('Y-m-d H:i',strtotime($post->post_dt));
	$post_format = $post->post_format;
	$post_password = $post->post_password;
	$post_url = $post->post_url;
	$post_lang = $post->post_lang;
	$post_title = $post->post_title;
	$post_excerpt = $post->post_excerpt;
	$post_excerpt_xhtml = $post->post_excerpt_xhtml;
	$post_content = $post->post_content;
	$post_content_xhtml = $post->post_content_xhtml;
	$post_notes = $post->post_notes;
	$post_status = $post->post_status;
	$post_selected = (boolean) $post->post_selected;
	$post_open_comment = false;
	$post_open_tb = false;

	if ($change) {
		$post_type = 'eventhandler';
		$page_title = __('Change entry into event');
	} else {
		$event_startdt = date('Y-m-d H:i',strtotime($post->event_startdt));
		$event_enddt = date('Y-m-d H:i',strtotime($post->event_enddt));
		$event_address = $post->event_address;
		$event_latitude = $post->event_latitude;
		$event_longitude = $post->event_longitude;
		$event_zoom = $post->event_zoom;

		$page_title = __('Edit event');

		$next_rs = $core->blog->getNextPost($post,1);
		$prev_rs = $core->blog->getNextPost($post,-1);

		if ($next_rs !== null) {
			$next_link = sprintf($post_link,$next_rs->post_id,
				html::escapeHTML($next_rs->post_title),__('next event').'&nbsp;&#187;');
			$next_headlink = sprintf($post_headlink,'next',
				html::escapeHTML($next_rs->post_title),$next_rs->post_id);
		}

		if ($prev_rs !== null) {
			$prev_link = sprintf($post_link,$prev_rs->post_id,
				html::escapeHTML($prev_rs->post_title),'&#171;&nbsp;'.__('previous event'));
			$prev_headlink = sprintf($post_headlink,'previous',
				html::escapeHTML($prev_rs->post_title),$prev_rs->post_id);
		}
	}

	$can_edit_post = $post->isEditable();
	$can_delete= $post->isDeletable();

	$post_media = array();
}

# Format excerpt and content
if (!empty($_POST) && $can_edit_post) {
	$post_format = $_POST['post_format'];
	$post_excerpt = $_POST['post_excerpt'];
	$post_content = $_POST['post_content'];

	$post_title = $_POST['post_title'];

	$cat_id = (integer) $_POST['cat_id'];

	if (isset($_POST['post_status'])) {
		$post_status = (integer) $_POST['post_status'];
	}

	if (empty($_POST['post_dt'])) {
		$post_dt = '';
	} else {
		$post_dt = strtotime($_POST['post_dt']);
		$post_dt = date('Y-m-d H:i',$post_dt);
	}

	$post_open_comment = false;
	$post_open_tb = false;
	$post_selected = !empty($_POST['post_selected']);
	$post_lang = $_POST['post_lang'];
	$post_password = !empty($_POST['post_password']) ? $_POST['post_password'] : null;

	$post_notes = $_POST['post_notes'];

	if (isset($_POST['post_url'])) {
		$post_url = $_POST['post_url'];
	}

	$core->blog->setPostContent(
		$post_id,$post_format,$post_lang,
		$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
	);


	if (empty($_POST['event_startdt'])) {
		$event_startdt = '';
	} else {
		$event_startdt = strtotime($_POST['event_startdt']);
		$event_startdt = date('Y-m-d H:i',$event_startdt);
	}

	if (empty($_POST['event_enddt'])) {
		$event_enddt = '';
	} else {
		$event_enddt = strtotime($_POST['event_enddt']);
		$event_enddt = date('Y-m-d H:i',$event_enddt);
	}
	$event_address = $_POST['event_address'];
	$event_latitude = $_POST['event_latitude'];
	$event_longitude = $_POST['event_longitude'];
	$event_zoom = (int) $_POST['event_zoom'];
}

# Create or update post
if (!empty($_POST) && !empty($_POST['save']) && $can_edit_post) {
	$cur_post = $core->con->openCursor($core->prefix.'post');

	$cur_post->cat_id = ($cat_id ? $cat_id : null);
	$cur_post->post_dt = $post_dt ? date('Y-m-d H:i:00',strtotime($post_dt)) : '';
	$cur_post->post_format = $post_format;
	$cur_post->post_password = $post_password;
	$cur_post->post_lang = $post_lang;
	$cur_post->post_title = $post_title;
	$cur_post->post_excerpt = $post_excerpt;
	$cur_post->post_excerpt_xhtml = $post_excerpt_xhtml;
	$cur_post->post_content = $post_content;
	$cur_post->post_content_xhtml = $post_content_xhtml;
	$cur_post->post_notes = $post_notes;
	$cur_post->post_status = $post_status;
	$cur_post->post_selected = (integer) $post_selected;
	$cur_post->post_open_comment = 0;
	$cur_post->post_open_tb = 0;

	if (isset($_POST['post_url'])) {
		$cur_post->post_url = $post_url;
	}

	$cur_event = $core->con->openCursor($core->prefix.'eventhandler');

	$cur_event->event_startdt = $event_startdt ? date('Y-m-d H:i:00',strtotime($event_startdt)) : '';
	$cur_event->event_enddt = $event_enddt ? date('Y-m-d H:i:00',strtotime($event_enddt)) : '';
	$cur_event->event_address = $event_address;
	$cur_event->event_latitude = $event_latitude;
	$cur_event->event_longitude = $event_longitude;
	$cur_event->event_zoom = $event_zoom;

	# Update post
	if ($post_id) {
		try {
			# --BEHAVIOR-- adminBeforeEventHandlerUpdate
			$core->callBehavior('adminBeforeEventHandlerUpdate',$cur_post,$cur_event,$post_id);

			$eventHandler->updEvent($post_id,$cur_post,$cur_event);

			# --BEHAVIOR-- adminAfterEventHandlerUpdate
			$core->callBehavior('adminAfterEventHandlerUpdate',$cur_post,$cur_event,$post_id);

			dcPage::addSuccessNotice(__('Event has been updated.'));
			http::redirect($p_url.'&part=event&id='.$post_id);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	} else {
		$cur_post->user_id = $core->auth->userID();

		try {
			# --BEHAVIOR-- adminBeforeEventHandlerCreate
			$core->callBehavior('adminBeforeEventHandlerCreate',$cur_post,$cur_event);

			$return_id = $eventHandler->addEvent($cur_post,$cur_event);

			# --BEHAVIOR-- adminAfterEventHandlerCreate
			$core->callBehavior('adminAfterEventHandlerCreate',$cur_post,$cur_event,$return_id);

			dcPage::addSuccessNotice(__('Event has been created.'));
			http::redirect($p_url.'&part=event&id='.$return_id);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}
	}
}

if ($post_id) {
	$preview_url = $core->blog->url.
		$core->url->getURLFor(
			'eventhandler_preview',
			$core->auth->userID().'/'.
			http::browserUID(DC_MASTER_KEY.$core->auth->userID().$core->auth->getInfo('user_pwd')).
			'/'.$post->post_url
		);
}

if (!empty($_POST['delete']) && $can_delete) {
	try {
		# --BEHAVIOR-- adminBeforeEventHandlerDelete
		$core->callBehavior('adminBeforeEventHandlerDelete',$post_id);

		$eventHandler->delEvent($post_id);
		http::redirect($p_url.'&part=events');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Get bind entries
if ($post_id && !$change) {
	$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
	$nb_per_page =	30;

	if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
		$nb_per_page = (integer) $_GET['nb'];
	}

	$params = array();
	$params['event_id'] = $post_id;
	$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
	$params['no_content'] = true;

	try {
		$posts = $eventHandler->getPostsByEvent($params);
		$counter = $eventHandler->getPostsByEvent($params,true);
		$posts_list = new adminEventHandlertPostsList($core,$posts,$counter->f(0));
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

/* DISPLAY
-------------------------------------------------------- */
$default_tab = 'edit-entry';
if (!$can_edit_post) {
	$default_tab = '';
}

if (!empty($_GET['tab'])) {
	$default_tab = $_GET['tab'];
}

$admin_post_behavior = '';
if ($post_editor && !empty($post_editor[$post_format])) {
	$admin_post_behavior = $core->callBehavior('adminPostEditor', $post_editor[$post_format],
											   'event', array('#post_content', '#post_excerpt')
	);
}

# XHTML conversion
if (!empty($_GET['xconv'])) {
	$post_excerpt = $post_excerpt_xhtml;
	$post_content = $post_content_xhtml;
	$post_format = 'xhtml';

	$message = __('Don\'t forget to validate your XHTML conversion by saving your post.');
}

# --BEHAVIOR-- adminEventHandlerBeforeEventTpl - to use a custom tpl e.g.
$core->callBehavior('adminEventHandlerCustomEventTpl');

include(dirname(__FILE__).'/../tpl/edit_event.tpl');
