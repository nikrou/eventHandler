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

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

dcPage::check('usage,contentadmin');

# Init some values
$s = $core->blog->settings->eventHandler;
$p_url 	= 'plugin.php?p=eventHandler';
$eventHandler = new eventHandler($core);

# Clean REQUESTs
$is_super_admin = $core->auth->isSuperAdmin();
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$start_part = $s->active ? 'events' : 'settings';
$default_part = isset($_REQUEST['part']) ? $_REQUEST['part'] : $start_part;
$default_tab = $default_part;
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'settings';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'eventhandler';
$action = isset($_POST['action']) ? $_POST['action'] : '';

# Common page header
$header = adminEventHandler::adminCss();

# Common page footer
$footer = '<hr class="clear"/><p class="right">';

if ($core->auth->check('admin',$core->blog->id)) {
	$footer .= '<a class="button" href="'.$p_url.'&amp;part=settings">'.__('Settings').'</a> - ';
}
$footer .= '
eventHandler - '.$core->plugins->moduleInfo('eventHandler','version').'&nbsp;
<img alt="'.__('Event handler').'" src="index.php?pf=eventHandler/icon.png" />
</p>';

# succes_codes
$succes = array(
	'save_settings' => __('Configuration saved'),
	'del_records' => __('Records deleted')
);

# errors_codes
$errors = array(
	'save_settings' => __('Failed to save configuration: %s'),
	'del_records' => __('Failed to delete records: %s')
);

# Messages
if (isset($succes[$msg])) {
	$message = dcPage::message($succes[$msg]);
}

# Pages
if (!file_exists(dirname(__FILE__).'/inc/index.'.$default_part.'.php')) {
	$default_part = 'settings';
}

if (!empty($_SESSION['eh_tab'])) {
    $default_tab = $_SESSION['eh_tab'];
    unset($_SESSION['eh_tab']);
}

define('DC_CONTEXT_EVENTHANDLER', $default_part);
include dirname(__FILE__).'/inc/index.'.$default_part.'.php';
