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

dcPage::check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_USAGE, dcAuth::PERMISSION_CONTENT_ADMIN]));

// Init some values
$settings = dcCore::app()->blog->settings->eventHandler;
$eventHandler = new eventHandler();

// Clean REQUESTs
$is_super_admin = dcCore::app()->auth->isSuperAdmin();
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$start_part = $settings->active ? 'events' : 'settings';
$default_part = isset($_REQUEST['part']) ? $_REQUEST['part'] : $start_part;
$default_tab = $default_part;
$section = isset($_REQUEST['section']) ? $_REQUEST['section'] : '';
$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'settings';
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 'eventhandler';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Common page header
$header = adminEventHandler::adminCss();

// Common page footer
$footer = '<hr class="clear"/><p class="right">';

if (dcCore::app()->auth->check('admin', dcCore::app()->blog->id)) {
    $footer .= '<a class="button" href="' . dcCore::app()->admin->getPageURL() . '&amp;part=settings">' . __('Settings') . '</a> - ';
}
$footer .= '
eventHandler - ' . dcCore::app()->plugins->moduleInfo('eventHandler', 'version') . '&nbsp;
<img alt="' . __('Event handler') . '" src="index.php?pf=eventHandler/icon.svg" width="16px" />
</p>';

// succes_codes
$succes = [
    'save_settings' => __('Configuration saved'),
    'del_records' => __('Records deleted')
];

// errors_codes
$errors = [
    'save_settings' => __('Failed to save configuration: %s'),
    'del_records' => __('Failed to delete records: %s')
];

// Messages
if (isset($succes[$msg])) {
    $message = dcPage::message($succes[$msg]);
}

// Pages
if (!file_exists(__DIR__ . '/inc/index.' . $default_part . '.php')) {
    $default_part = 'settings';
}

if (!empty($_SESSION['eh_tab'])) {
    $default_tab = $_SESSION['eh_tab'];
    unset($_SESSION['eh_tab']);
}

define('DC_CONTEXT_EVENTHANDLER', $default_part);
include __DIR__ . '/inc/index.' . $default_part . '.php';
