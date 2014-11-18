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

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'events'){return;}

# List of events
class adminEventHandlertList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No event').'</strong></p>';
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
			'<th>'.__('Start date').'</th>'.
			'<th>'.__('End date').'</th>'.
			'<th>'.__('Entries').'</th>'.
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

		$now = time();
		if (strtotime($this->rs->event_startdt) > $now)
		{
			$style = ' eventhandler-scheduled';
		}
		elseif (strtotime($this->rs->event_enddt) < $now)
		{
			$style = ' eventhandler-finished';
		}
		else
		{
			$style = ' eventhandler-ongoing';
		}

		$entries = $this->rs->eventHandler->getPostsByEvent(array('event_id'=>$this->rs->post_id),true);
		if ($entries->isEmpty())
		{
			$entries = 0;
		}
		else
		{
			$entries = '<a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'&amp;tab=bind-entries">'.$entries->f(0).'</a>';
		}

		return
		'<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').$style.'"'.
		' id="p'.$this->rs->post_id.'">'.
		'<td class="nowrap">'.form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap'.$style.'">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->event_startdt).'</td>'.
		'<td class="nowrap'.$style.'">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->event_enddt).'</td>'.
		'<td class="nowrap">'.$entries.'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.'</td>'.
		'</tr>';
	}
}

# From post
$from_id = $from_post = null;
if (!empty($_REQUEST['from_id']))
{
	try
	{
		$from_id = abs((integer) $_REQUEST['from_id']);
		$from_post = $core->blog->getPosts(array('post_id'=>$from_id,'post_type'=>''));
		if ($from_post->isEmpty())
		{
			$from_id = $from_post = null;
			throw new Exception(__('No such post ID'));
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}


/* Actions
-------------------------------------------------------- */

if ($action == 'eventhandler_bind_event' && $from_id)
{
	$redir = $core->getPostAdminURL($from_post->post_type,$from_post->post_id);
	if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
	{
		$redir = $_POST['redir'];
	}
	elseif (!$redir)
	{
		$redir = $p_url.'&part=events';
	}

	try
	{
		$entries = $_POST['entries'];

		foreach ($entries as $k => $v)
		{
			$entries[$k] = (integer) $v;
		}
		$entries_params = array();
		$entries_params['no_content'] = true;
		$entries_params['sql'] = 'AND P.post_id IN('.implode(',',$entries).') ';
		$events = $eventHandler->getEvents($entries_params);

		while($events->fetch())
		{
			$core->meta->delPostMeta($from_id,'eventhandler',$events->post_id);
			$core->meta->setPostMeta($from_id,'eventhandler',$events->post_id);
		}

		http::redirect($redir);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if (!$core->error->flag())
{
	try
	{
		# Getting categories
		$categories = $core->blog->getCategories(array('post_type'=>'post'));
		# Getting authors
		$users = $core->blog->getPostsUsers();
		# Getting dates
		$dates = $core->blog->getDates(array('type'=>'month'));
		# Getting langs
		$langs = $core->blog->getLangs();
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

# Creating filter combo boxes
if (!$core->error->flag())
{
	# Filter form we'll put in html_block
	$users_combo = $categories_combo = array();
	$users_combo['-'] = $categories_combo['-'] = '';
	while ($users->fetch())
	{
		$user_cn = dcUtils::getUserCN($users->user_id,$users->user_name,
		$users->user_firstname,$users->user_displayname);

		if ($user_cn != $users->user_id)
		{
			$user_cn .= ' ('.$users->user_id.')';
		}

		$users_combo[$user_cn] = $users->user_id;
	}

	$categories_combo[__('None')] = 'NULL';
	while ($categories->fetch())
	{
		$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
			html::escapeHTML($categories->cat_title).
			' ('.$categories->nb_post.')'] = $categories->cat_id;
	}

	$status_combo = array(
		'-' => ''
	);
	foreach ($core->blog->getAllPostStatus() as $k => $v)
	{
		$status_combo[$v] = (string) $k;
	}

	$selected_combo = array(
		'-' => '',
		__('selected') => '1',
		__('not selected') => '0'
	);

	# Months array
	$dt_m_combo['-'] = '';
	while ($dates->fetch())
	{
		$dt_m_combo[dt::str('%B %Y',$dates->ts())] = $dates->year().$dates->month();
	}

	$lang_combo['-'] = '';
	while ($langs->fetch())
	{
		$lang_combo[$langs->post_lang] = $langs->post_lang;
	}

	$sortby_combo = array(
		__('Date') => 'post_dt',
		__('Title') => 'post_title',
		__('Category') => 'cat_title',
		__('Author') => 'user_id',
		__('Status') => 'post_status',
		__('Selected') => 'post_selected',
		__('Start date') => 'event_startdt',
		__('End date') => 'event_enddt',
		__('Localization') => 'event_address',
	);

	$order_combo = array(
		__('Descending') => 'desc',
		__('Ascending') => 'asc'
	);

	# Period combo
	$period_combo = array(
		'-' => '',
		__('Not started') => 'scheduled',
		__('Started') => 'started',
		__('Finished') => 'finished',
		__('Not finished') => 'notfinished',
		__('Ongoing') => 'ongoing',
		__('Outgoing') => 'outgoing'
	);
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('Status')] = array(
		__('Publish') => 'publish',
		__('Unpublish') => 'unpublish',
		__('Schedule') => 'schedule',
		__('Mark as pending') => 'pending'
	);
}
$combo_action[__('Mark')] = array(
	__('Mark as selected') => 'selected',
	__('Mark as unselected') => 'unselected'
);
$combo_action[__('Change')] = array(__('Change category') => 'category');
if ($core->auth->check('admin',$core->blog->id))
{
	$combo_action[__('Change')] = array_merge($combo_action[__('Change')],
		array(__('Change author') => 'author'));
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('Delete')] = array(__('Delete') => 'delete');
	$combo_action[__('Entries')] = array(__('Unbind related entries') => 'eventhandler_unbind_post');
}

# --BEHAVIOR-- adminEventHandlerActionsCombo
$core->callBehavior('adminEventHandlerActionsCombo',array(&$combo_action));

/* Get events
-------------------------------------------------------- */
$user_id = !empty($_GET['user_id']) ?	$_GET['user_id'] : '';
$cat_id = !empty($_GET['cat_id']) ?	$_GET['cat_id'] : '';
$status = isset($_GET['status']) ?	$_GET['status'] : '';
$selected = isset($_GET['selected']) ?	$_GET['selected'] : '';
$month = !empty($_GET['month']) ?		$_GET['month'] : '';
$lang = !empty($_GET['lang']) ?		$_GET['lang'] : '';
$sortby = !empty($_GET['sortby']) ?	$_GET['sortby'] : 'post_dt';
$order = !empty($_GET['order']) ?		$_GET['order'] : 'desc';
$period = !empty($_GET['period']) ? $_GET['period'] : '';

$show_filters = false;

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0)
{
	if ($nb_per_page != $_GET['nb'])
	{
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

# - User filter
if ($user_id !== '' && in_array($user_id,$users_combo))
{
	$params['user_id'] = $user_id;
	$show_filters = true;
}

# - Categories filter
if ($cat_id !== '' && in_array($cat_id,$categories_combo))
{
	$params['cat_id'] = $cat_id;
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo))
{
	$params['post_status'] = $status;
	$show_filters = true;
}

# - Selected filter
if ($selected !== '' && in_array($selected,$selected_combo))
{
	$params['post_selected'] = $selected;
	$show_filters = true;
}

# - Month filter
if ($month !== '' && in_array($month,$dt_m_combo))
{
	$params['post_month'] = substr($month,4,2);
	$params['post_year'] = substr($month,0,4);
	$show_filters = true;
}

# - Lang filter
if ($lang !== '' && in_array($lang,$lang_combo))
{
	$params['post_lang'] = $lang;
	$show_filters = true;
}

# Period filter
if ($period !== '' && in_array($period,$period_combo))
{
	$params['event_period'] = $period;
	$show_filters = true;
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo))
{
	if ($order !== '' && in_array($order,$order_combo))
	{
		$params['order'] = $sortby.' '.$order;
	}

	if ($sortby != 'post_dt' || $order != 'desc')
	{
		$show_filters = true;
	}
}

$hidden_fields =
form::hidden(array('p'),'eventHandler').
form::hidden(array('part'),'events').
form::hidden(array('user_id'),$user_id).
form::hidden(array('cat_id'),$cat_id).
form::hidden(array('status'),$status).
form::hidden(array('selected'),$selected).
form::hidden(array('month'),$month).
form::hidden(array('lang'),$lang).
form::hidden(array('period'),$period).
form::hidden(array('sortby'),$sortby).
form::hidden(array('order'),$order).
form::hidden(array('page'),$page).
form::hidden(array('nb'),$nb_per_page).
$core->formNonce();

$redir = $p_url.
'&amp;part=events'.
'&amp;user_id='.$user_id.
'&amp;cat_id='.$cat_id.
'&amp;status='.$status.
'&amp;selected='.$selected.
'&amp;month='.$month.
'&amp;lang='.$lang.
'&amp;period='.$period.
'&amp;sortby='.$sortby.
'&amp;order='.$order.
'&amp;page='.$page.
'&amp;nb='.$nb_per_page;

# Get events
try
{
	$posts = $eventHandler->getEvents($params);
	$counter = $eventHandler->getEvents($params,true);
	$post_list = new adminEventHandlertList($core,$posts,$counter->f(0));
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if ($core->error->flag())
{
	echo '
	<html>
	<head><title>'.__('Event handler').' - '.__('Events').'</title></head>
	<body>
	<h2>'.html::escapeHTML($core->blog->name).
	' &rsaquo; <a href="'.$p_url.'&amp;part=events">'.__('Events').'</a>'.
	' - <a class="button" href="'.$p_url.'&amp;part=event">'.__('New event').'</a>'.
	'</h2>';
}
else
{
	echo '
	<html>
	<head><title>'.__('Event handler').' - '.__('Events').'</title>'.
	$header.
	dcPage::jsLoad('js/_posts_list.js').
	(!$show_filters ? dcPage::jsLoad('js/filter-controls.js') : '').

	'</head>
	<body>
	<h2>'.html::escapeHTML($core->blog->name).
	' &rsaquo; <span class="page-title">'.($from_id ? __('Bind an event') : __('Events')).'</span>'.
	' - <a class="button" href="'.$p_url.'&amp;part=event">'.__('New event').'</a>'.
	'</h2>'.$msg;

	if (!$show_filters)
	{
		echo '<p><a id="filter-control" class="form-control" href="#">'.
		__('Filters').'</a></p>';
	}

	echo
	'<form action="'.$p_url.'" method="get" id="filters-form">'.
	'<fieldset><legend>'.__('Filters').'</legend>'.
	'<div class="three-cols">'.
	'<div class="col">'.
	'<label>'.__('Author:').
	form::combo('user_id',$users_combo,$user_id).'</label> '.
	'<label>'.__('Category:').
	form::combo('cat_id',$categories_combo,$cat_id).'</label> '.
	'<label>'.__('Period:').
	form::combo('period',$period_combo,$period).'</label> '.
	'</div>'.

	'<div class="col">'.
	'<label>'.__('Status:').
	form::combo('status',$status_combo,$status).'</label> '.
	'<label>'.__('Selected:').
	form::combo('selected',$selected_combo,$selected).'</label> '.
	'<label>'.__('Month:').
	form::combo('month',$dt_m_combo,$month).'</label> '.
	'<label>'.__('Lang:').
	form::combo('lang',$lang_combo,$lang).'</label> '.
	'</div>'.

	'<div class="col">'.
	'<p><label>'.__('Order by:').
	form::combo('sortby',$sortby_combo,$sortby).'</label> '.
	'<label>'.__('Sort:').
	form::combo('order',$order_combo,$order).'</label></p>'.
	'<p><label class="classic">'.	form::field('nb',3,3,$nb_per_page).' '.
	__('Entries per page').'</label> '.
	form::hidden(array('p'),'eventHandler').
	form::hidden(array('part'),'events').
	($from_id ? form::hidden(array('from_id'),$from_id) : '').
	'<input type="submit" value="'.__('Apply filters').'" /></p>'.
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>'.
	'</form>';

	# Show posts

	if ($from_id)
	{
		$post_list->display($page,$nb_per_page,
			'<form action="'.$p_url.'" method="post" id="form-entries">'.

			'%s'.

			'<div class="two-cols">'.
			'<p class="col checkboxes-helpers"></p>'.

			'<p class="col right">'.
			'<input type="submit" value="'.__('Attach selected events').'" />'.
			form::hidden('action','eventhandler_bind_event').
			form::hidden(array('from_id'),$from_id).
			$hidden_fields.
			'</p>'.
			'</div>'.
			'</form>'
		);
	}
	else
	{
		$post_list->display($page,$nb_per_page,
			'<form action="posts_actions.php" method="post" id="form-entries">'.

			'%s'.

			'<div class="two-cols">'.
			'<p class="col checkboxes-helpers"></p>'.

			'<p class="col right">'.__('Selected entries action:').' '.
			form::combo('action',$combo_action).
			'<input type="submit" value="'.__('ok').'" />'.
			$hidden_fields.
			form::hidden(array('post_type'),'eventhandler').
			form::hidden(array('redir'),$redir).
			'</p>'.
			'</div>'.
			'</form>'
		);
	}
}

dcPage::helpBlock('eventHandler');
echo $footer.'</body></html>';
