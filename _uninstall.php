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

$this->addUserAction('settings','delete_all','eventHandler',
	__('delete all settings'));
$this->addUserAction('tables','delete','eventhandler',
	__('delete table'));
$this->addUserAction('plugins','delete','eventHandler',
	__('delete plugin files'));
$this->addUserAction('versions','delete','eventHandler',
	__('delete the version number'));

$this->addDirectAction('settings','delete_all','eventHandler',
	sprintf(__('delete all %s settings'),'eventHandler'));
/*
$this->addDirectAction('tables','delete','eventhandler',
	sprintf(__('delete %s table'),'eventHandler'));
//*/
$this->addDirectAction('plugins','delete','eventHandler',
	sprintf(__('delete %s plugin files'),'eventHandler'));
$this->addDirectAction('versions','delete','eventHandler',
	sprintf(__('delete %s version number'),'eventHandler'));
