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

if (!defined('DC_CONTEXT_EVENTHANDLER') || DC_CONTEXT_EVENTHANDLER != 'settings'){return;}

# Read settings
$active = (boolean) $s->active;
$public_posts_of_event_place = (string) $s->public_posts_of_event_place;
$public_events_of_post_place = (string) $s->public_events_of_post_place;
$public_hidden_categories = @unserialize($s->public_hidden_categories);
if (!is_array($public_hidden_categories)) $public_hidden_categories = array();
$public_map_zoom = abs((integer) $s->public_map_zoom);
if (!$public_map_zoom) $public_map_zoom = 9;
$public_map_type = (string) $s->public_map_type;
$public_extra_css = (string) $s->public_extra_css;

# Action
if ($action == 'savesettings') {
	try  {
		$active = !empty($_POST['active']);
		$public_posts_of_event_place = $_POST['public_posts_of_event_place'];
		$public_events_of_post_place = $_POST['public_events_of_post_place'];
        if (isset($_POST['public_hidden_categories'])) {
            $public_hidden_categories = $_POST['public_hidden_categories'];
        } else {
            $public_hidden_categories = array();
        }
		if (!is_array($public_hidden_categories)) $public_hidden_categories = array();
		$public_map_zoom = abs((integer) $_POST['public_map_zoom']);
		if (!$public_map_zoom) $public_map_zoom = 9;
		$public_map_type = $_POST['public_map_type'];
		$public_extra_css = $_POST['public_extra_css'];

		$s->put('active',$active,'boolean');
		$s->put('public_posts_of_event_place',$public_posts_of_event_place,'string');
		$s->put('public_events_of_post_place',$public_events_of_post_place,'string');
		$s->put('public_hidden_categories',serialize($public_hidden_categories),'string');
		$s->put('public_map_zoom',$public_map_zoom,'integer');
		$s->put('public_map_type',$public_map_type,'string');
		$s->put('public_extra_css',$public_extra_css,'string');

		$core->blog->triggerBlog();

		http::redirect($p_url.'&part=settings&msg=save_settings&section='.$section);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
if ($action == 'importeventdata') {
	include dirname(__FILE__).'/patch.eventdata.php';
}

# Combos
$combo_place = array(
	__('hide') => '',
	__('before content') => 'before',
	__('after content') => 'after'
);
for($i=3;$i<21;$i++)
{
	$combo_map_zoom[$i] = $i;
}
$combo_map_type = array(
	__('road map') => 'ROADMAP',
	__('satellite') => 'SATELLITE',
	__('hybrid') => 'HYBRID',
	__('terrain') => 'TERRAIN'
);


# Categories combo
$combo_categories = array('-'=>'');
try
{
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
while ($categories->fetch())
{
	$combo_categories[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
		html::escapeHTML($categories->cat_title)] = $categories->cat_id;
}

# Display
echo '
<html>
<head><title>'.__('Event handler').' - '.__('Settings').'</title>'.$header.'</head>
<body>
<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'&amp;part=events">'.__('Events').'</a>'.
' &rsaquo; <span class="page-title">'.__('Settings').'</span>'.
' - <a class="button" href="'.$p_url.'&amp;part=event">'.__('New event').'</a>'.
'</h2>'.$msg.'
<form id="setting-form" method="post" action="plugin.php">

<fieldset id="setting-plugin"><legend>'. __('Activation').'</legend>
<p><label class="classic">'.
form::checkbox(array('active'),'1',$active).' '.
__('Enable extension').'</label></p>

<p><label class="classic">'.__('Additionnal style sheet:').' '.
form::textarea(array('public_extra_css'),164,10,$public_extra_css,'maximal').'</label></p>
</fieldset>

<fieldset id="setting-event"><legend>'. __('Events').'</legend>
<p><label class="classic">'.
__('Show related entries on event:').'<br />'.
form::combo(array('public_posts_of_event_place'),$combo_place,$public_posts_of_event_place).'
</label></p>
</fieldset>

<fieldset id="setting-enrty"><legend>'. __('Entries').'</legend>
<p><label class="classic">'.
__('Show related events on entry:').'<br />'.
form::combo(array('public_events_of_post_place'),$combo_place,$public_events_of_post_place).'
</label></p>
</fieldset>
';

if (count($combo_categories) > 1)
{
	echo '
	<fieldset id="setting-cat"><legend>'. __('Categories').'</legend>
	<p>'.__('When an event has an hidden category, it will only display on its category page.').'</p>
	<table class="clear">
	<tr>
	<th>'.__('Hide').'</th>
	<th colspan="2">'.__('Category').'</th>
	<th>'.__('Level').'</th>
	<th>'.__('Entries').'</th>
	<th>'.__('Events').'</th>
	</tr>';

	while ($categories->fetch())
	{
		$hidden = in_array($categories->cat_id,$public_hidden_categories) || in_array($categories->cat_title,$public_hidden_categories);
		$nb_events = $core->blog->getPosts(array('cat_id'=>$categories->cat_id,'post_type'=>'eventhandler'),true)->f(0);
		if ($nb_events)
		{
			$nb_events =
			'<a href="'.$p_url.'&amp;part=events&amp;cat_id='.$categories->cat_id.'" '.
			'title="'.__('List of events related to this category').'">'.$nb_events.'</a>';
		}
		$nb_posts = $categories->nb_post;
		if ($nb_posts)
		{
			$nb_posts =
			'<a href="posts.php?cat_id='.$categories->cat_id.'" '.
			'title="'.__('List of entries related to this category').'">'.$nb_posts.'</a>';
		}

		echo '
		<tr class="line">
		<td class="nowrap">'.form::checkbox(array('public_hidden_categories[]'),$categories->cat_id,$hidden).'</td>
		<td class="nowrap">'.$categories->cat_id.'</td>
		<td class="nowrap"><a href="category.php?id='.$categories->cat_id.'" '.
		'title="'.__('Edit this category').'">'.html::escapeHTML($categories->cat_title).'</a></td>
		<td class="nowrap">'.$categories->level.'</td>
		<td class="nowrap">'.$nb_posts.'</td>
		<td class="nowrap">'.$nb_events.'</td>
		</tr>';
	}
	echo '
	</table>
	</fieldset>';
}

echo '
<fieldset id="setting-map"><legend>'. __('Maps').'</legend>
<p><label class="classic">'.
__('Default zoom on map:').'<br />'.
form::combo(array('public_map_zoom'),$combo_map_zoom,$public_map_zoom).'
</label></p>
<p><label class="classic">'.
__('Default type of map:').'<br />'.
form::combo(array('public_map_type'),$combo_map_type,$public_map_type).'
</label></p>
</fieldset>';

echo '
<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'eventHandler').
form::hidden(array('part'),'settings').
form::hidden(array('section'),$section).
form::hidden(array('action'),'savesettings').'
</p></div>
</form>';

if ($core->plugins->moduleExists('eventdata')) {

	echo '
	<form id="setting-form" method="post" action="plugin.php">
	<div><hr />';

	if (isset($eventdata_import) && $eventdata_import === true) {
		echo '<p class="message">'.__('Events from eventdata successfully imported').'</p>';
	}
	if ($s->eventdata_import){
		echo '<p>'.__('Records of eventdata have been imported for this blog.').'</p>';
	}
	echo '
	<p><input type="submit" name="save" value="'.__('Import eventdata records').'" />'.
	$core->formNonce().
	form::hidden(array('p'),'eventHandler').
	form::hidden(array('part'),'settings').
	form::hidden(array('section'),$section).
	form::hidden(array('action'),'importeventdata').'
	</p></div>
	</form>';
}

dcPage::helpBlock('eventHandler');
echo $footer.'</body></html>';
