<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
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

# List of entries
class adminEventHandlertPostsList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No entry').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';

			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';

			if ($enclose_block)
			{
				$html_block = sprintf($enclose_block,$html_block);
			}

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';

			$blocks = explode('%s',$html_block);

			echo $blocks[0];

			while ($this->rs->fetch())
			{
				echo $this->postLine();
			}

			echo $blocks[1];

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function postLine()
	{
		if ($this->core->auth->check('categories',$this->core->blog->id))
		{
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		}
		else
		{
			$cat_link = '%2$s';
		}

		if ($this->rs->cat_title)
		{
			$cat_title = sprintf($cat_link,$this->rs->cat_id,html::escapeHTML($this->rs->cat_title));
		}
		else
		{
			$cat_title = __('None');
		}

		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status)
		{
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('scheduled'),'scheduled.png');
				break;
			case -2:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
		}

		$protected = '';
		if ($this->rs->post_password)
		{
			$protected = sprintf($img,__('protected'),'locker.png');
		}

		$selected = '';
		if ($this->rs->post_selected)
		{
			$selected = sprintf($img,__('selected'),'selected.png');
		}

		return
		'<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">'.
		'<td class="nowrap">'.form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.'</td>'.
		'</tr>';
	}
}

# Post part
$post_id = '';
$cat_id = '';
$post_dt = '';
$post_format = $core->auth->getOption('post_format');
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

$page_title = __('New event');

$can_view_page = true;
$can_edit_post = $core->auth->check('usage,contentadmin',$core->blog->id);
$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);
$can_delete = false;

$post_headlink = '<link rel="%s" title="%s" href="'.$p_url.'&amp;part=event&amp;id=%s" />';
$post_link = '<a href="'.$p_url.'&amp;part=event&amp;id=%s" title="%s">%s</a>';

$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# If user can't publish
if (!$can_publish)
{
	$post_status = -2;
}

# Getting categories
$categories_combo = array('&nbsp;' => '');
try
{
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
	while ($categories->fetch())
	{
		$categories_combo[] = new formSelectOption(
			str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
			$categories->cat_id
		);
	}
}
catch (Exception $e) { }

# Status combo
foreach ($core->blog->getAllPostStatus() as $k => $v)
{
	$status_combo[$v] = (string) $k;
}

# Formaters combo
foreach ($core->getFormaters() as $v)
{
	$formaters_combo[$v] = $v;
}

# Languages combo
$rs = $core->blog->getLangs(array('order'=>'asc'));
$all_langs = l10n::getISOcodes(0,1);
$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
while ($rs->fetch())
{
	if (isset($all_langs[$rs->post_lang]))
	{
		$lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
		unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
	}
	else
	{
		$lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
	}
}
unset($all_langs);
unset($rs);

# Change a post to an event
$change = false;
if (!empty($_REQUEST['from_id']))
{
	$post = $core->blog->getPosts(array('post_id'=> (integer) $_REQUEST['from_id'],'post_type'=>''));

	if ($post->isEmpty())
	{
		$core->error->add(__('This entry does not exist.'));
		unset($post);
		$can_view_page = false;
	}
	else
	{
		$change = true;
	}
}

# Get entry informations
if (!empty($_REQUEST['id']))
{
	$post = $eventHandler->getEvents(array('post_id' => (integer) $_REQUEST['id']));

	if ($post->isEmpty())
	{
		$core->error->add(__('This event does not exist.'));
		unset($post);
		$can_view_page = false;
	}
}

if (isset($post))
{
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

	if ($change)
	{
		$post_type = 'eventhandler';

		$page_title = __('Change entry into event');
	}
	else
	{
		$event_startdt = date('Y-m-d H:i',strtotime($post->event_startdt));
		$event_enddt = date('Y-m-d H:i',strtotime($post->event_enddt));
		$event_address = $post->event_address;
		$event_latitude = $post->event_latitude;
		$event_longitude = $post->event_longitude;

		$page_title = __('Edit event');

		$next_rs = $core->blog->getNextPost($post,1);
		$prev_rs = $core->blog->getNextPost($post,-1);

		if ($next_rs !== null)
		{
			$next_link = sprintf($post_link,$next_rs->post_id,
				html::escapeHTML($next_rs->post_title),__('next event').'&nbsp;&#187;');
			$next_headlink = sprintf($post_headlink,'next',
				html::escapeHTML($next_rs->post_title),$next_rs->post_id);
		}

		if ($prev_rs !== null)
		{
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
if (!empty($_POST) && $can_edit_post)
{
	$post_format = $_POST['post_format'];
	$post_excerpt = $_POST['post_excerpt'];
	$post_content = $_POST['post_content'];

	$post_title = $_POST['post_title'];

	$cat_id = (integer) $_POST['cat_id'];

	if (isset($_POST['post_status']))
	{
		$post_status = (integer) $_POST['post_status'];
	}

	if (empty($_POST['post_dt']))
	{
		$post_dt = '';
	}
	else
	{
		$post_dt = strtotime($_POST['post_dt']);
		$post_dt = date('Y-m-d H:i',$post_dt);
	}

	$post_open_comment = false;
	$post_open_tb = false;
	$post_selected = !empty($_POST['post_selected']);
	$post_lang = $_POST['post_lang'];
	$post_password = !empty($_POST['post_password']) ? $_POST['post_password'] : null;

	$post_notes = $_POST['post_notes'];

	if (isset($_POST['post_url']))
	{
		$post_url = $_POST['post_url'];
	}

	$core->blog->setPostContent(
		$post_id,$post_format,$post_lang,
		$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
	);


	if (empty($_POST['event_startdt']))
	{
		$event_startdt = '';
	}
	else
	{
		$event_startdt = strtotime($_POST['event_startdt']);
		$event_startdt = date('Y-m-d H:i',$event_startdt);
	}

	if (empty($_POST['event_enddt']))
	{
		$event_enddt = '';
	}
	else
	{
		$event_enddt = strtotime($_POST['event_enddt']);
		$event_enddt = date('Y-m-d H:i',$event_enddt);
	}
	$event_address = $_POST['event_address'];
	$event_latitude = $_POST['event_latitude'];
	$event_longitude = $_POST['event_longitude'];
}

# Create or update post
if (!empty($_POST) && !empty($_POST['save']) && $can_edit_post)
{
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

	if (isset($_POST['post_url']))
	{
		$cur_post->post_url = $post_url;
	}

	$cur_event = $core->con->openCursor($core->prefix.'eventhandler');

	$cur_event->event_startdt = $event_startdt ? date('Y-m-d H:i:00',strtotime($event_startdt)) : '';
	$cur_event->event_enddt = $event_enddt ? date('Y-m-d H:i:00',strtotime($event_enddt)) : '';
	$cur_event->event_address = $event_address;
	$cur_event->event_latitude = $event_latitude;
	$cur_event->event_longitude = $event_longitude;

	# Update post
	if ($post_id)
	{
		try
		{
			# --BEHAVIOR-- adminBeforeEventHandlerUpdate
			$core->callBehavior('adminBeforeEventHandlerUpdate',$cur_post,$cur_event,$post_id);

			$eventHandler->updEvent($post_id,$cur_post,$cur_event);

			# --BEHAVIOR-- adminAfterEventHandlerUpdate
			$core->callBehavior('adminAfterEventHandlerUpdate',$cur_post,$cur_event,$post_id);

			http::redirect($p_url.'&part=event&id='.$post_id.'&upd=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	else
	{
		$cur_post->user_id = $core->auth->userID();

		try
		{
			# --BEHAVIOR-- adminBeforeEventHandlerCreate
			$core->callBehavior('adminBeforeEventHandlerCreate',$cur_post,$cur_event);

			$return_id = $eventHandler->addEvent($cur_post,$cur_event);

			# --BEHAVIOR-- adminAfterEventHandlerCreate
			$core->callBehavior('adminAfterEventHandlerCreate',$cur_post,$cur_event,$return_id);

			http::redirect($p_url.'&part=event&id='.$return_id.'&crea=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

if (!empty($_POST['delete']) && $can_delete)
{
	try
	{
		# --BEHAVIOR-- adminBeforeEventHandlerDelete
		$core->callBehavior('adminBeforeEventHandlerDelete',$post_id);

		$eventHandler->delEvent($post_id);
		http::redirect($p_url.'&part=events');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Get bind entries
if ($post_id && !$change)
{
	$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
	$nb_per_page =  30;

	if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0)
	{
		$nb_per_page = (integer) $_GET['nb'];
	}

	$params = array();
	$params['event_id'] = $post_id;
	$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
	$params['no_content'] = true;

	try
	{
		$posts = $eventHandler->getPostsByEvent($params);
		$counter = $eventHandler->getPostsByEvent($params,true);
		$posts_list = new adminEventHandlertPostsList($core,$posts,$counter->f(0));
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}

}

/* DISPLAY
-------------------------------------------------------- */
$default_tab = 'edit-entry';
if (!$can_edit_post)
{
	$default_tab = '';
}
if (!empty($_GET['tab']))
{
	$default_tab = $_GET['tab'];
}

echo '
<html>
<head><title>'.__('Event handler').' - '.$page_title.'</title>'.
dcPage::jsDatePicker().
dcPage::jsToolBar().
dcPage::jsModal().
dcPage::jsMetaEditor().
dcPage::jsLoad('js/_post.js').
dcPage::jsLoad('index.php?pf=eventHandler/js/event.js').
dcPage::jsLoad('index.php?pf=eventHandler/js/event-admin-map.js').
dcPage::jsConfirmClose('entry-form','comment-form').
# --BEHAVIOR-- adminEventHandlerHeaders
$core->callBehavior('adminEventHandlerHeaders').
dcPage::jsPageTabs($default_tab).
'<link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/style.css"/>'.
$next_headlink."\n".$prev_headlink.'
</head>
<body>';

if (!empty($_GET['upd'])) {
	dcPage::message(__('Entry has been updated.'));
}
elseif (!empty($_GET['crea'])) {
	dcPage::message(__('Entry has been created.'));
}
elseif (!empty($_GET['attached'])) {
	dcPage::message(__('File has been attached.'));
}
elseif (!empty($_GET['rmattach'])) {
	dcPage::message(__('Attachment has been removed.'));
}
if (!empty($_GET['creaco'])) {
	dcPage::message(__('Comment has been created.'));
}

# XHTML conversion
if (!empty($_GET['xconv']))
{
	$post_excerpt = $post_excerpt_xhtml;
	$post_content = $post_content_xhtml;
	$post_format = 'xhtml';

	echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your post.').'</p>';
}

echo '
<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=events">'.__('Events').'</a>'.
' &rsaquo; <span class="page-title">'.$page_title.'</span>';

if ($post_id && $post->post_status == 1)
{
	echo ' - <a id="post-preview" href="'.$post->getURL().'" class="button">'.__('View event').'</a>';
}
elseif ($post_id)
{
	$preview_url =
	$core->blog->url.$core->url->getBase('preview').'/'.
	$core->auth->userID().'/'.
	http::browserUID(DC_MASTER_KEY.$core->auth->userID().$core->auth->getInfo('user_pwd')).
	'/'.$post->post_url;
	echo ' - <a id="post-preview" href="'.$preview_url.'" class="button">'.__('Preview event').'</a>';
}

if ($post_id)
{
	echo ' - <a class="button" href="'.$p_url.'&amp;part=event">'.__('New event').'</a>';
}

echo '</h2>';

if ($post_id)
{
	echo '<p>';
	if ($prev_link) { echo $prev_link; }
	if ($next_link && $prev_link) { echo ' - '; }
	if ($next_link) { echo $next_link; }

	# --BEHAVIOR-- adminEventHandlerNavLinks
	$core->callBehavior('adminEventHandlerNavLinks',isset($post) ? $post : null);

	echo '</p>';
}

# If we can view page

/* Post form if we can edit post
-------------------------------------------------------- */
if ($can_view_page && $can_edit_post)
{
	echo
	'<div class="multi-part" title="'.__('Edit event').'" id="edit-entry">'.
	'<form action="'.$p_url.'&part=event" method="post" id="entry-form">'.

	'<div id="entry-sidebar">'.

    '<div>'.
    '<h5 id="label_cat_id">'.__('Category').'</h5>'.
	'<p><label for="cat_id">'.__('Category:').'</label>'.
	form::combo('cat_id',$categories_combo,$cat_id,'maximal').
	'</p></div>'.

    '<p class="entry-status"><label for="post_status">'.__('Entry status').'</label>'.
    form::combo('post_status',$status_combo,$post_status,'maximal','',!$can_publish).
    '</p>'.

    '<p><label for="post_dt">'.__('Published on').'</label>'.
    form::field('post_dt',16,16,$post_dt,'',3).
    '</p>'.

    '<div>'.
    '<h5 id="label_format"><label for="post_format" class="classic">'.__('Text formatting').'</label></h5>'.
    '<p>'.form::combo('post_format',$formaters_combo,$post_format,'maximal').
    '</p>'.
    '<p class="format_control control_no_xhtml">'.
    '<a id="convert-xhtml" class="button'.($post_id && $post_format != 'wiki' ? ' hide' : '').'" href="post.php?id='.$post_id.'&amp;xconv=1">'.
    __('Convert to XHTML').'</a></p></div>'.


	'<p><label class="classic">'.form::checkbox('post_selected',1,$post_selected,'',3).' '.
	__('Selected entry').'</label></p>'.

	'<p><label>'.__('Entry language').
	form::combo('post_lang',$lang_combo,$post_lang,'',5).
	'</label></p>'.
/*
	'<p><label>'.__('Entry password:').
	form::field('post_password',10,32,html::escapeHTML($post_password),'maximal',3).
	'</label></p>'.
//*/
	'<div class="lockable">'.
	'<p><label>'.__('Edit basename').
	form::field('post_url',10,255,html::escapeHTML($post_url),'maximal',3).
	'</label></p>'.
	'<p class="form-note warn">'.
	__('Warning: If you set the URL manually, it may conflict with another entry.').
	'</p>'.
	'</div>';

	# --BEHAVIOR-- adminEventHandlerFormSidebar
	$core->callBehavior('adminEventHandlerFormSidebar',isset($post) ? $post : null);

	echo
	'</div>'.		// End #entry-sidebar

	'<div id="entry-content"><fieldset class="constrained">'.		// #entry-content

	'<div class="two-cols"><div class="col">'.

	'<p class="datepicker"><label class="required">'.__('Start date:').
	form::field('event_startdt',16,16,$event_startdt,'datepicker',2).
	'</label></p>'.

	'</div><div class="col">'.

	'<p class="datepicker"><label class="required">'.__('End date:').
	form::field('event_enddt',16,16,$event_enddt,'datepicker',2).
	'</label></p>'.

	'</div></div>'.

	'<p id="event-area-title">'.__('Localization:').'</p>'.
	'<div id="event-area-content">'.

	'<p><label>'.__('Address:').
	form::field('event_address',10,255,html::escapeHTML($event_address),'maximal',6).
	'</label></p>'.

	'<fieldset><legend>'.__('Maps').'</legend>'.
	'<p class="form-note">'.__('If you want to use maps, you must enter an address as precise as possible (number, street, city, country)').'</p>'.
	'<p id="event-map-link"><a href="http://maps.google.fr/maps">'.__('Find coordinates on googleMap').'</a></p>'.
	'<div class="two-cols"><div class="col">'.

	'<p><label>'.__('Latitude:').
	form::field('event_latitude',16,16,$event_latitude,'',6).
	'</label></p>'.

	'</div><div class="col">'.

	'<p><label>'.__('Longitude:').
	form::field('event_longitude',16,16,$event_longitude,'',6).
	'</label></p>'.

	'</div></div>'.
	'</fieldset>'.

	'</div>'.

	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
	form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
	'</label></p>'.

	'<p class="area" id="excerpt-area"><label for="post_excerpt">'.__('Excerpt:').'</label> '.
	form::textarea('post_excerpt',50,5,html::escapeHTML($post_excerpt),'',2).
	'</p>'.

	'<p class="area"><label class="required" title="'.__('Required field').'" '.
	'for="post_content">'.__('Content:').'</label> '.
	form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'',2).
	'</p>'.

	'<p class="area" id="notes-area"><label>'.__('Notes:').'</label>'.
	form::textarea('post_notes',50,5,html::escapeHTML($post_notes),'',2).
	'</p>';

	# --BEHAVIOR-- adminEventHandlerForm
	$core->callBehavior('adminEventHandlerForm',isset($post) ? $post : null);

	echo
	'<p>'.
	($post_id ? form::hidden('id',$post_id) : '').
	'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
	'accesskey="s" name="save" /> '.
	($can_delete ? '<input type="submit" value="'.__('delete').'" name="delete" />' : '').
	$core->formNonce().
	'</p>'.

	'</fieldset></div>'.		// End #entry-content
	'</form>'.
	'</div>'.

	# Related posts
	'<div class="multi-part" title="'.__('Related entries').'" id="bind-entries">';

	if (!$post_id || $change)
	{
		echo
		'<p>'.__('You must save event before adding entries').'</p>';
	}
	else
	{
		$posts_list->display($page,$nb_per_page,'%s');
	}

	echo
	'</div>';
}

dcPage::helpBlock('eventHandler');
echo $footer.'</body></html>';
