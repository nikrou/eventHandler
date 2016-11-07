<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2016 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
#
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

define('EH_DC_MIN_VERSION','2.6');
if (!defined('DC_CONTEXT_ADMIN')){return;}

if (version_compare(DC_VERSION,EH_DC_MIN_VERSION,'<')) {
	$core->error->add(sprintf(__('Dotclear version %s minimum is required. "%s" is deactivated',EH_DC_MIN_VERSION,'eventHandler')));
	$core->plugins->deactivateModule('eventHandler');
	return false;
}
# Get new version
$new_version = $core->plugins->moduleInfo('eventHandler','version');
$old_version = $core->getVersion('eventHandler');
# Compare versions
if (version_compare($old_version,$new_version,'>=')) return;
# Install
try {
	# Database schema
	$t = new dbStruct($core->con,$core->prefix);
	$t->eventhandler
		->post_id('bigint',0,false)
		->event_startdt('timestamp',0,false,'now()')
		->event_enddt('timestamp',0,false,'now()')
		->event_address('text','',true,null)
		->event_latitude('varchar',25,true,null)
		->event_longitude('varchar',25,true,null)
		->event_zoom('integer',0,true,0)

		->index('idx_event_post_id','btree','post_id')
		->index('idx_event_event_start','btree','event_startdt')
		->index('idx_event_event_end','btree','event_enddt')
		->reference('fk_event_post','post_id','post','post_id','cascade','cascade');

	# Schema installation
	$ti = new dbStruct($core->con,$core->prefix);
	$changes = $ti->synchronize($t);

	# Settings options
	$core->blog->settings->addNamespace('eventHandler');
	$s = $core->blog->settings->eventHandler;

	$extra_css = file_get_contents(__DIR__.'/css/default-eventhandler.css');

	$s->put('active',false,'boolean','Enabled eventHandler extension',false,true);
	$s->put('public_events_of_post_place','after','string','Display related events on entries',false,true);
	$s->put('public_posts_of_event_place','after','string','Display related posts on events',false,true);
	$s->put('public_events_list_sortby','','string','Default field for ordering events list',false,true);
	$s->put('public_events_list_order','','string','Default order (asc or desc) for events list',false,true);
	$s->put('public_hidden_categories','','string','List of categories to hide from post content and widgets',false,true);
	$s->put('public_map_zoom',9,'integer','Default zoom of map',false,true);
	$s->put('public_map_type','ROADMAP','string','Default type of map',false,true);
	$s->put('public_extra_css',$extra_css,'string','Custom CSS',false,true);
	$s->put('map_provider','googlemaps','string','Map provider',false,true);
	$s->put('map_tile_layer','http://{s}.tile.osm.org/{z}/{x}/{y}.png','string','Tile Layer for OSM',false,true);
	$s->put('map_api_key','','string','Map API Key',false,true);

	# Set version
	$core->setVersion('eventHandler',$new_version);

	return true;
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}
return false;
