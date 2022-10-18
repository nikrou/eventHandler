<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
	/* Name */			"EventHandler",
	/* Description*/	"Manage events on your blog",
	/* Author */		"JC Denis, Nicolas Roudaire",
	/* Version */		'2022.10.18',
	/* Properties */
	array(
		'permissions' => 'usage,contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.21',
		'support' => 'http://forum.dotclear.org/viewtopic.php?id=43296',
		'details' => 'http://plugins.dotaddict.org/dc2/details/eventHandler'
		)
);
