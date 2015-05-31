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

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'events'){return;}

# From post
$from_id = $from_post = null;
if (!empty($_REQUEST['from_id'])) {
	try {
		$from_id = abs((integer) $_REQUEST['from_id']);
		$from_post = $core->blog->getPosts(array('post_id' => $from_id,'post_type' => ''));
		if ($from_post->isEmpty()) {
			$from_id = $from_post = null;
			throw new Exception(__('No such post ID'));
		}
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}


/* Actions
-------------------------------------------------------- */

if ($action == 'eventhandler_bind_event' && $from_id) {
	$redir = $core->getPostAdminURL($from_post->post_type,$from_post->post_id);
	if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false) {
		$redir = $_POST['redir'];
	} elseif (!$redir) {
		$redir = $p_url.'&part=events';
	}

	try {
		$entries = $_POST['entries'];

		foreach ($entries as $k => $v) {
			$entries[$k] = (integer) $v;
		}
		$entries_params = array();
		$entries_params['no_content'] = true;
		$entries_params['sql'] = 'AND P.post_id IN('.implode(',',$entries).') ';
		$events = $eventHandler->getEvents($entries_params);

		while($events->fetch()) {
			$core->meta->delPostMeta($from_id,'eventhandler',$events->post_id);
			$core->meta->setPostMeta($from_id,'eventhandler',$events->post_id);
		}

		http::redirect($redir);
	}  catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

if (!empty($_POST['entries']) && $action == 'author' && !empty($_POST['new_auth_id'])
    && $core->auth->check('admin', $core->blog->id)) {
    if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false) {
        $redir = $_POST['redir'];
    } else {
        $redir = $p_url.'&part=events';
    }
    try {
		$entries = $_POST['entries'];
        if ($core->getUser($_POST['new_auth_id'])->isEmpty()) {
            throw new Exception(__('This user does not exist'));
        }
        $cur = $core->con->openCursor($core->prefix.'post');
        $cur->user_id = $_POST['new_auth_id'];
        $cur->update('WHERE post_id '.$core->con->in($entries));

        dcPage::addSuccessNotice(sprintf(
            __(
                '%d entry has been set to user "%s"',
                '%d entries have been set to user "%s"',
                count($entries)
            ),
            count($entries),
            html::escapeHTML($_POST['new_auth_id']))
        );

		http::redirect($redir);
    }  catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
} elseif ($action == 'category' &&  (!empty($_POST['new_cat_id']) || !empty($_POST['new_cat_title']))
          && !empty($_POST['entries']) && $core->auth->check('categories', $core->blog->id)) {
    if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false) {
        $redir = $_POST['redir'];
    } else {
        $redir = $p_url.'&part=events';
    }
    try {
		$entries = $_POST['entries'];
        if (!empty($_POST['new_cat_title'])) {
            $cur_cat = $core->con->openCursor($core->prefix.'category');
            $cur_cat->cat_title = $_POST['new_cat_title'];
            $cur_cat->cat_url = '';
            $title = $cur_cat->cat_title;
            $parent_cat = !empty($_POST['new_cat_parent']) ? $_POST['new_cat_parent'] : '';
            # --BEHAVIOR-- adminBeforeCategoryCreate
            $core->callBehavior('adminBeforeCategoryCreate', $cur_cat);

            $new_cat_id = $core->blog->addCategory($cur_cat, (integer) $parent_cat);

            # --BEHAVIOR-- adminAfterCategoryCreate
            $core->callBehavior('adminAfterCategoryCreate', $cur_cat, $new_cat_id);
        } else {
            $new_cat_id = $_POST['new_cat_id'];
        }

        $core->blog->updPostsCategory($entries, $new_cat_id);
        $title = $core->blog->getCategory($new_cat_id);
        dcPage::addSuccessNotice(sprintf(
            __(
                '%d entry has been moved to category "%s"',
                '%d entries have been moved to category "%s"',
                count($entries)
            ),
            count($entries),
            html::escapeHTML($title->cat_title))
        );

		http::redirect($redir);
    }  catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

#--BEHAVIOR-- adminEventHandlerActionsManage
$core->callBehavior('adminEventHandlerActionsManage',$eventHandler,$action);

if (!$core->error->flag()) {
	try {
		# Getting categories
		$categories = $core->blog->getCategories(array('post_type'=>'post'));
		# Getting authors
		$users = $core->blog->getPostsUsers();
		# Getting dates
		$dates = $core->blog->getDates(array('type'=>'month'));
		# Getting langs
		$langs = $core->blog->getLangs();
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Creating filter combo boxes
if (!$core->error->flag()) {
	# Filter form we'll put in html_block
	$users_combo = $categories_combo = array();
	$users_combo['-'] = $categories_combo['-'] = '';
	while ($users->fetch())	{
		$user_cn = dcUtils::getUserCN($users->user_id,$users->user_name,
		$users->user_firstname,$users->user_displayname);

		if ($user_cn != $users->user_id) {
			$user_cn .= ' ('.$users->user_id.')';
		}

		$users_combo[$user_cn] = $users->user_id;
	}

	$categories_combo[__('None')] = 'NULL';
	while ($categories->fetch()) {
		$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
			html::escapeHTML($categories->cat_title).
			' ('.$categories->nb_post.')'] = $categories->cat_id;
	}

	$status_combo = array('-' => '');
	foreach ($core->blog->getAllPostStatus() as $k => $v) {
		$status_combo[$v] = (string) $k;
	}

	$selected_combo = array(
		'-' => '',
		__('selected') => '1',
		__('not selected') => '0'
	);

	# Months array
	$dt_m_combo['-'] = '';
	while ($dates->fetch()) {
		$dt_m_combo[dt::str('%B %Y',$dates->ts())] = $dates->year().$dates->month();
	}

	$lang_combo['-'] = '';
	while ($langs->fetch()) {
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
if ($core->auth->check('publish,contentadmin',$core->blog->id)) {
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
if ($core->auth->check('admin',$core->blog->id)) {
	$combo_action[__('Change')] = array_merge($combo_action[__('Change')],
		array(__('Change author') => 'author'));
}
if ($core->auth->check('delete,contentadmin',$core->blog->id)) {
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

$form_filter_title = __('Show filters and display options');

$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) {
		$show_filters = true;
	}
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

# - User filter
if ($user_id !== '' && in_array($user_id,$users_combo)) {
	$params['user_id'] = $user_id;
	$show_filters = true;
}

# - Categories filter
if ($cat_id !== '' && in_array($cat_id,$categories_combo)) {
	$params['cat_id'] = $cat_id;
	$show_filters = true;
}

# - Status filter
if ($status !== '' && in_array($status,$status_combo)) {
	$params['post_status'] = $status;
	$show_filters = true;
}

# - Selected filter
if ($selected !== '' && in_array($selected,$selected_combo)) {
	$params['post_selected'] = $selected;
	$show_filters = true;
}

# - Month filter
if ($month !== '' && in_array($month,$dt_m_combo)) {
	$params['post_month'] = substr($month,4,2);
	$params['post_year'] = substr($month,0,4);
	$show_filters = true;
}

# - Lang filter
if ($lang !== '' && in_array($lang,$lang_combo)) {
	$params['post_lang'] = $lang;
	$show_filters = true;
}

# Period filter
if ($period !== '' && in_array($period,$period_combo)) {
	$params['event_period'] = $period;
	$show_filters = true;
}

# - Sortby and order filter
if ($sortby !== '' && in_array($sortby,$sortby_combo)) {
	if ($order !== '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}

	if ($sortby != 'post_dt' || $order != 'desc') {
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

#behavior to customize different aspects of the events page :
# params to manage the getEvents (add some db fields...)
# sortby_combo : add some criterias to the sortby_combo filter
# show_filters : boolean to tell if the filters must be displayed or not
# redir : redirection url
# hidden_fields

$core->callBehavior('adminEventHandlerEventsPageCustomize',
                    array('params' => &$params,
                          'sortby_combo' => &$sortby_combo,
                          'show_filters'=> &$show_filters,
                          'redir' => &$redir,
                          'hidden_fields' => &$hidden_fields
                    )
);

# Get events
try {
	$posts = $eventHandler->getEvents($params);
	$counter = $eventHandler->getEvents($params,true);
	$post_list = new adminEventHandlertList($core,$posts,$counter->f(0));
	$core->callBehavior('adminEventHandlerEventsListCustom',array($posts,$counter,$post_list));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# --BEHAVIOR-- adminEventHandlerBeforeEventsTpl - to use a custom tpl e.g.
$core->callBehavior('adminEventHandlerBeforeEventsTpl');

include(dirname(__FILE__).'/../tpl/list_events.tpl');
