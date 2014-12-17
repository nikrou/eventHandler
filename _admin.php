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

if (!defined('DC_CONTEXT_ADMIN')){return;}

# set ns
$core->blog->settings->addNamespace('eventHandler');

# Load _wigdets.php
require dirname(__FILE__).'/_widgets.php';

# Admin menu
$_menu['Blog']->addItem(
	__('Event handler'),
	'plugin.php?p=eventHandler','index.php?pf=eventHandler/icon.png',
	preg_match('/plugin.php\?p=eventHandler(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id)
);

# Admin Dashboard
$core->addBehavior('adminDashboardIcons',array('adminEventHandler','adminDashboardIcons'));
$core->addBehavior('adminDashboardFavs',array('adminEventHandler','adminDashboardFavs'));

# Admin behaviors
if ($core->blog->settings->eventHandler->active)
{
	$core->addBehavior('adminPostHeaders',array('adminEventHandler','adminPostHeaders'));
	$core->addBehavior('adminPostsActionsCombo',array('adminEventHandler','adminPostsActionsCombo'));
	$core->addBehavior('adminPostsActionsHeaders',array('adminEventHandler','adminPostsActionsHeaders'));
	$core->addBehavior('adminPostsActions',array('adminEventHandler','adminPostsActions'));
	$core->addBehavior('adminPostsActionsContent',array('adminEventHandler','adminPostsActionsContent'));
	$core->addBehavior('adminPostFormSidebar',array('adminEventHandler','adminPostFormSidebar'));
	$core->addBehavior('adminAfterPostCreate',array('adminEventHandler','adminAfterPostSave'));
	$core->addBehavior('adminAfterPostUpdate',array('adminEventHandler','adminAfterPostSave'));
	$core->addBehavior('adminBeforePostDelete',array('adminEventHandler','adminBeforePostDelete'));
}

class adminEventHandler
{
	# Dashboard icon
	public static function adminDashboardIcons($core,$icons)
	{
		$icons['eventHandler'] = new ArrayObject(array(
			__('Event handler'),
			'plugin.php?p=eventHandler',
			'index.php?pf=eventHandler/icon.png'
		));
	}

	# Dashboard fav icon
	public static function adminDashboardFavs($core,$favs)
	{
		$favs['eventHandler'] = new ArrayObject(array(
			'eventHandler',
			'Event handler',
			'plugin.php?p=eventHandler',
			'index.php?pf=eventHandler/icon.png',
			'index.php?pf=eventHandler/icon-b.png',
			'usage,contentadmin',null,null
		));
	}

	# post.php
	# Headers, jQuery features to remove events from a post
	public static function adminPostHeaders()
	{
		return
		'<link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/style.css" />'.
		dcPage::jsLoad('index.php?pf=eventHandler/js/post.js');
	}

	# posts.php
	# Combo of actions on multiple posts
	public static function adminPostsActionsCombo($args)
	{
		# usage, contentadmin
		$args[0][__('Events')][__('Bind events')] = 'eventhandler_bind_event';
		$args[0][__('Events')][__('Unbind events')] = 'eventhandler_remove_event';
	}

	# posts_actions.php
	# Headers for table of events
	public static function adminPostsActionsHeaders()
	{
		return
		'<link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/style.css" />';
	}

	# posts_actions.php
	# Action for multiple posts and actions for multiple events
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		# Bind selected events to selected posts
		if ($action == 'eventhandler_bind_event' && !empty($_POST['events']))
		{
			try
			{
				foreach ($_POST['events'] as $k => $v)
				{
					$events_id[$k] = (integer) $v;
				}
				$params['sql'] = 'AND P.post_id IN('.implode(',',$events_id).') ';
				$eventHandler = new eventHandler($core);
				$events = $eventHandler->getEvents($params);

				if ($events->isEmpty())
				{
					throw new Exception(__('No such event'));
				}
				$meta_ids = array();
				while ($events->fetch())
				{
					$meta_ids[] = $events->post_id;
				}

				while ($posts->fetch())
				{
					foreach($meta_ids as $meta_id)
					{
						$core->meta->delPostMeta($posts->post_id,'eventhandler',$meta_id);
						$core->meta->setPostMeta($posts->post_id,'eventhandler',$meta_id);
					}
				}

				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		# Unbind all posts from selected events
		if ($action == 'eventhandler_unbind_post')
		{
			try
			{
				while ($posts->fetch())
				{
					$core->meta->delMeta($posts->post_id,'eventhandler');
				}

				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}

	# posts_actions.php
	# Form for action on multiple posts
	# (action on one post can be found on index.php?part=events&from_id=xxx)
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'eventhandler_bind_event')
		{
			echo '<h3>'.__('Select events to link to entries').'</h3>';

			try
			{
				$eventHandler = new eventHandler($core);

				$params = array();
				$params['no_content'] = true;
				$params['order'] = 'event_startdt DESC';
				$params['period'] = 'notfinished';

				$events = $eventHandler->getEvents($params);
				$counter = $eventHandler->getEvents($params,true);
				$list = new adminEventHandlerMiniList($core,$events,$counter->f(0));

				echo $list->display(1,100,
					'<form action="posts_actions.php" method="post">'.

					'%s'.

					'<p>'.
					$hidden_fields.
					$core->formNonce().
					form::hidden(array('action'),'eventhandler_bind_event').
					'<input type="submit" value="'.__('save').'" /></p>'.
					'</form>'
				);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}

	# post.php
	# Sidebar list of linked events, menu of events actions for this post
	public static function adminPostFormSidebar($post)
	{
		if ($post === null) return;

		global $core;

		# Get linked events
		$events = null;
		$params = array();
		$params['post_id'] = $post->post_id;
		$params['no_content'] = true;

		try
		{
			$eventHandler = new eventHandler($core);
			$events = $eventHandler->getEventsByPost($params);
			if ($events->isEmpty())
			{
				$events = null;
			}
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}

		# Display
		echo
		'<div id="eventhandler-form">'.
		'<h3 id="eventhandler-form-title">'.__('Events:').'</h3>'.
		'<div id="eventhandler-form-content">';

		# Related events
		if ($events)
		{
			echo
			'<ul class="event-list">';

			while($events->fetch())
			{
				echo
				'<li class="event-node event-node-'.$events->getPeriod().'"><label title="'.__('Check to unbind').'" class="classic">'.form::checkbox(array('eventhandler_events[]'),$events->post_id,'','event-node-value').html::escapeHTML($events->post_title).'</label></li>';
			}

			echo
			'</ul>';
		}

		# Bind a event to this post
		echo
		'<p><a href="plugin.php?p=eventHandler'.
		'&amp;part=events&amp;from_id='.$post->post_id.
		'">'.__('Bind events').'</a>';

		# Change post into event publish,contenadmin
		if($core->auth->check('publish,contentadmin',$core->blog->id))
		{
			echo
			'<br /><a href="plugin.php?p=eventHandler'.
			'&amp;part=event&amp;from_id='.$post->post_id.
			'" title="'.__('Change this entry into an event').
			'">'.__('Change into event').'</a>';
		}

		echo
		'</p>'.
		'</div></div>';
	}

	# post.php
	# This delete relation between this post and ckecked related event (without javascript)
	public static function adminAfterPostSave($cur,$post_id)
	{
		if (!$post_id) return;

		if (empty($_POST['eventhandler_events']) || !is_array($_POST['eventhandler_events'])) return;

		global $core;

		try
		{
			foreach($_POST['eventhandler_events'] as $event_id)
			{
				$event_id = abs((integer) $event_id);
				if (!$event_id) continue;

				$core->meta->delPostMeta($post_id,'eventhandler',$event_id);
			}
		}
		catch (Exception $e)
		{
			//$core->error->add($e->getMessage());
		}

	}

	# post.php
	# This delete relation between this post and all there events
	public static function adminBeforePostDelete($post_id)
	{
		if (!$post_id) return;

		global $core;

		try
		{
			$core->meta->delPostMeta($post_id,'eventhandler');
		}
		catch (Exception $e)
		{
			//$core->error->add($e->getMessage());
		}
	}
}

# Table for form for action on multiple posts (posts_actions.php)
class adminEventHandlerMiniList extends adminGenericList
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
			'<th>'.__('Period').'</th>'.
			'<th>'.__('Start date').'</th>'.
			'<th>'.__('End date').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';

			if ($enclose_block) {
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
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
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

		$period = $this->rs->getPeriod();
		$style = ' eventhandler-'.$period;

		return
		'<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').$style.'" id="e'.$this->rs->post_id.'">'.
		'<td class="nowrap">'.form::checkbox(array('events[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'" '.
		'title="'.html::escapeHTML($this->rs->getURL()).'">'.html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap'.$style.'">'.__($period).'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->event_startdt).'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->event_enddt).'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.'</td>'.
		'</tr>';
	}
}
