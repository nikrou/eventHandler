<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of eventHandler, a plugin for Dotclear 2.
 *
 *  Copyright(c) 2014-2023 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
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

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'events') {
    return;
}

// From post
$from_id = $from_post = null;
if (!empty($_REQUEST['from_id'])) {
    try {
        $from_id = (int) $_REQUEST['from_id'];
        $from_post = dcCore::app()->blog->getPosts(['post_id' => $from_id, 'post_type' => '']);
        if ($from_post->isEmpty()) {
            $from_id = $from_post = null;
            throw new Exception(__('No such post ID'));
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

/* Actions
-------------------------------------------------------- */
$action = isset($_POST['action']) ? $_POST['action'] : '';
if ($action === adminEventHandler::BIND_EVENT_ACTION && $from_id) {
    $redir = dcCore::app()->getPostAdminURL($from_post->post_type, $from_post->post_id);
    if (isset($_POST['redir']) && strpos($_POST['redir'], '://') === false) {
        $redir = $_POST['redir'];
    } elseif (!$redir) {
        $redir = dcCore::app()->admin->getPageURL() . '&part=events';
    }

    try {
        $entries = $_POST['entries'];

        foreach ($entries as $k => $v) {
            $entries[$k] = (integer) $v;
        }
        $entries_params = [];
        $entries_params['no_content'] = true;
        $entries_params['sql'] = 'AND P.post_id IN(' . implode(',', $entries) . ') ';
        /** @phpstan-ignore-next-line ; define in index.php */
        $events = $eventHandler->getEvents($entries_params);

        while ($events->fetch()) {
            dcCore::app()->meta->delPostMeta($from_id, 'eventhandler', $events->post_id);
            dcCore::app()->meta->setPostMeta($from_id, 'eventhandler', $events->post_id);
        }

        http::redirect($redir);
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

if (!empty($_POST['entries']) && $action === 'author' && !empty($_POST['new_auth_id'])
    && dcCore::app()->auth->check('admin', dcCore::app()->blog->id)) {
    if (isset($_POST['redir']) && strpos($_POST['redir'], '://') === false) {
        $redir = $_POST['redir'];
    } else {
        $redir = dcCore::app()->admin->getPageURL() . '&part=events';
    }
    try {
        $entries = $_POST['entries'];
        if (dcCore::app()->getUser($_POST['new_auth_id'])->isEmpty()) {
            throw new Exception(__('This user does not exist'));
        }
        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'post');
        $cur->user_id = $_POST['new_auth_id'];
        $cur->update('WHERE post_id ' . dcCore::app()->con->in($entries));

        dcPage::addSuccessNotice(
            sprintf(
                __(
                    '%d entry has been set to user "%s"',
                    '%d entries have been set to user "%s"',
                    count($entries)
                ),
                count($entries),
                html::escapeHTML($_POST['new_auth_id'])
            )
        );

        http::redirect($redir);
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
} elseif ($action === 'category' && (!empty($_POST['new_cat_id']) || !empty($_POST['new_cat_title']))
          && !empty($_POST['entries']) && dcCore::app()->auth->check('categories', dcCore::app()->blog->id)) {
    if (isset($_POST['redir']) && strpos($_POST['redir'], '://') === false) {
        $redir = $_POST['redir'];
    } else {
        $redir = dcCore::app()->admin->getPageURL() . '&part=events';
    }
    try {
        $entries = $_POST['entries'];
        if (!empty($_POST['new_cat_title'])) {
            $cur_cat = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'category');
            $cur_cat->cat_title = $_POST['new_cat_title'];
            $cur_cat->cat_url = '';
            $title = $cur_cat->cat_title;
            $parent_cat = !empty($_POST['new_cat_parent']) ? $_POST['new_cat_parent'] : '';
            // --BEHAVIOR-- adminBeforeCategoryCreate
            dcCore::app()->callBehavior('adminBeforeCategoryCreate', $cur_cat);

            $new_cat_id = dcCore::app()->blog->addCategory($cur_cat, (integer) $parent_cat);

            // --BEHAVIOR-- adminAfterCategoryCreate
            dcCore::app()->callBehavior('adminAfterCategoryCreate', $cur_cat, $new_cat_id);
        } else {
            $new_cat_id = $_POST['new_cat_id'];
        }

        dcCore::app()->blog->updPostsCategory($entries, $new_cat_id);
        $title = dcCore::app()->blog->getCategory($new_cat_id);
        dcPage::addSuccessNotice(
            sprintf(
                __(
                    '%d entry has been moved to category "%s"',
                    '%d entries have been moved to category "%s"',
                    count($entries)
                ),
                count($entries),
                html::escapeHTML($title->cat_title)
            )
        );

        http::redirect($redir);
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

//--BEHAVIOR-- adminEventHandlerActionsManage
/** @phpstan-ignore-next-line ; define in index.php */
dcCore::app()->callBehavior('adminEventHandlerActionsManage', $eventHandler, $action);

$users = null;
$users_combo = [];
$categories = null;
$categories_combo = [];
$dates = null;
$dt_m_combo = [];
$langs = null;
$lang_combo = [];
$status_combo = [];
$selected_combo = [];
$period_combo = [];
$sortby_combo = [];
$order_combo = [];
if (!dcCore::app()->error->flag()) {
    try {
        $categories = dcCore::app()->blog->getCategories(['post_type' => 'post']);
        $users = dcCore::app()->blog->getPostsUsers();
        $dates = dcCore::app()->blog->getDates(['type' => 'month']);
        $langs = dcCore::app()->blog->getLangs();
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

// Creating filter combo boxes
if (!dcCore::app()->error->flag()) {
    // Filter form we'll put in html_block
    $users_combo = $categories_combo = [];
    $users_combo['-'] = $categories_combo['-'] = '';
    while ($users->fetch()) {
        $user_cn = dcUtils::getUserCN(
            $users->user_id,
            $users->user_name,
            $users->user_firstname,
            $users->user_displayname
        );

        if ($user_cn != $users->user_id) {
            $user_cn .= ' (' . $users->user_id . ')';
        }

        $users_combo[$user_cn] = $users->user_id;
    }

    $categories_combo[__('None')] = 'NULL';
    while ($categories->fetch()) {
        $categories_combo[str_repeat('&nbsp;&nbsp;', $categories->level - 1) . '&bull; ' .
        	html::escapeHTML($categories->cat_title) .
        	' (' . $categories->nb_post . ')'] = $categories->cat_id;
    }

    $status_combo = ['-' => ''];
    foreach (dcCore::app()->blog->getAllPostStatus() as $k => $v) {
        $status_combo[$v] = (string) $k;
    }

    $selected_combo = [
        '-' => '',
        __('selected') => '1',
        __('not selected') => '0'
    ];

    // Months array
    $dt_m_combo['-'] = '';
    while ($dates->fetch()) {
        $dt_m_combo[dt::str('%B %Y', $dates->ts())] = $dates->year() . $dates->month();
    }

    $lang_combo['-'] = '';
    while ($langs->fetch()) {
        $lang_combo[$langs->post_lang] = $langs->post_lang;
    }

    $sortby_combo = [
        __('Date') => 'post_dt',
        __('Title') => 'post_title',
        __('Category') => 'cat_title',
        __('Author') => 'user_id',
        __('Status') => 'post_status',
        __('Selected') => 'post_selected',
        __('Start date') => 'event_startdt',
        __('End date') => 'event_enddt',
        __('Localization') => 'event_address',
    ];

    $order_combo = [
        __('Descending') => 'desc',
        __('Ascending') => 'asc'
    ];

    // Period combo
    $period_combo = [
        '-' => '',
        __('Not started') => 'scheduled',
        __('Started') => 'started',
        __('Finished') => 'finished',
        __('Not finished') => 'notfinished',
        __('Ongoing') => 'ongoing',
        __('Outgoing') => 'outgoing'
    ];
}

// Actions combo box
$combo_action = [];
if (dcCore::app()->auth->check('publish,contentadmin', dcCore::app()->blog->id)) {
    $combo_action[__('Status')] = [
        __('Publish') => 'publish',
        __('Unpublish') => 'unpublish',
        __('Schedule') => 'schedule',
        __('Mark as pending') => 'pending'
    ];
}
$combo_action[__('Mark')] = [
    __('Mark as selected') => 'selected',
    __('Mark as unselected') => 'unselected'
];
$combo_action[__('Change')] = [__('Change category') => 'category'];
if (dcCore::app()->auth->check('admin', dcCore::app()->blog->id)) {
    $combo_action[__('Change')] = array_merge(
        $combo_action[__('Change')],
        [__('Change author') => 'author']
    );
}
if (dcCore::app()->auth->check('delete,contentadmin', dcCore::app()->blog->id)) {
    $combo_action[__('Delete')] = [__('Delete') => 'delete'];
    $combo_action[__('Entries')] = [
        __('Unbind related entries') => adminEventHandler::UNBIND_POST_ACTION,
    ];
}

// --BEHAVIOR-- adminEventHandlerActionsCombo
dcCore::app()->callBehavior('adminEventHandlerActionsCombo', [&$combo_action]);

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
$nb_per_page = 30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
    if ($nb_per_page != $_GET['nb']) {
        $show_filters = true;
    }
    $nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = [(($page - 1) * $nb_per_page), $nb_per_page];
$params['no_content'] = true;

// - User filter
if ($user_id !== '' && in_array($user_id, $users_combo)) {
    $params['user_id'] = $user_id;
    $show_filters = true;
}

// - Categories filter
if ($cat_id !== '' && in_array($cat_id, $categories_combo)) {
    $params['cat_id'] = $cat_id;
    $show_filters = true;
}

// - Status filter
if ($status !== '' && in_array($status, $status_combo)) {
    $params['post_status'] = $status;
    $show_filters = true;
}

// - Selected filter
if ($selected !== '' && in_array($selected, $selected_combo)) {
    $params['post_selected'] = $selected;
    $show_filters = true;
}

// - Month filter
if ($month !== '' && in_array($month, $dt_m_combo)) {
    $params['post_month'] = substr($month, 4, 2);
    $params['post_year'] = substr($month, 0, 4);
    $show_filters = true;
}

// - Lang filter
if ($lang !== '' && in_array($lang, $lang_combo)) {
    $params['post_lang'] = $lang;
    $show_filters = true;
}

// Period filter
if ($period !== '' && in_array($period, $period_combo)) {
    $params['event_period'] = $period;
    $show_filters = true;
}

// - Sortby and order filter
if ($sortby !== '' && in_array($sortby, $sortby_combo)) {
    if ($order !== '' && in_array($order, $order_combo)) {
        $params['order'] = $sortby . ' ' . $order;
    }

    if ($sortby != 'post_dt' || $order != 'desc') {
        $show_filters = true;
    }
}

$hidden_fields =
form::hidden(['p'], 'eventHandler') .
form::hidden(['part'], 'events') .
form::hidden(['user_id'], $user_id) .
form::hidden(['cat_id'], $cat_id) .
form::hidden(['status'], $status) .
form::hidden(['selected'], $selected) .
form::hidden(['month'], $month) .
form::hidden(['lang'], $lang) .
form::hidden(['period'], $period) .
form::hidden(['sortby'], $sortby) .
form::hidden(['order'], $order) .
form::hidden(['page'], $page) .
form::hidden(['nb'], $nb_per_page) .
dcCore::app()->formNonce();

$redir = dcCore::app()->admin->getPageURL() .
'&amp;part=events' .
'&amp;user_id=' . $user_id .
'&amp;cat_id=' . $cat_id .
'&amp;status=' . $status .
'&amp;selected=' . $selected .
'&amp;month=' . $month .
'&amp;lang=' . $lang .
'&amp;period=' . $period .
'&amp;sortby=' . $sortby .
'&amp;order=' . $order .
'&amp;page=' . $page .
'&amp;nb=' . $nb_per_page;

//behavior to customize different aspects of the events page :
// params to manage the getEvents (add some db fields...)
// sortby_combo : add some criterias to the sortby_combo filter
// show_filters : boolean to tell if the filters must be displayed or not
// redir : redirection url
// hidden_fields

dcCore::app()->callBehavior(
    'adminEventHandlerEventsPageCustomize',
    ['params' => &$params,
        'sortby_combo' => &$sortby_combo,
        'show_filters' => &$show_filters,
        'redir' => &$redir,
        'hidden_fields' => &$hidden_fields
    ]
);

// Get events
try {
    /** @phpstan-ignore-next-line ; define in index.php */
    $posts = $eventHandler->getEvents($params);
    /** @phpstan-ignore-next-line ; define in index.php */
    $counter = $eventHandler->getEvents($params, true);
    $post_list = new adminEventHandlerList(dcCore::app(), $posts, $counter->f(0));
    dcCore::app()->callBehavior('adminEventHandlerEventsListCustom', [$posts, $counter, $post_list]);
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

// --BEHAVIOR-- adminEventHandlerBeforeEventsTpl - to use a custom tpl e.g.
dcCore::app()->callBehavior('adminEventHandlerBeforeEventsTpl');

include(__DIR__ . '/../tpl/list_events.tpl');
