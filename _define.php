<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of eventHandler, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$this->registerModule(
	/* Name */			"Event handler",
	/* Description*/		"Add period to your posts",
	/* Author */			"JC Denis",
	/* Version */			'1.0-RC1',
	/* Permissions */		'usage,contentadmin',
	/* Priority */			5000 // Before plugin kUtRL
);
	/* date */		#20100903
?>