<html>
  <head>
    <title><?php echo __('Event handler').' - '.__('Settings');?></title>
    <?php echo dcPage::jsPageTabs($default_tab).dcPage::jsLoad("index.php?pf=eventHandler/js/settings.js");?>
    <?php echo $header;?>
  </head>
  <body>
    <?php
    echo dcPage::breadcrumb(array(html::escapeHTML($core->blog->name) => '',
    '<a href="plugin.php?p=eventHandler&amp;part=events">'.__('Events').'</a>
    &rsaquo; <span class="page-title">'.__('Settings').'</span>' => ''
    ));
    ?>
    <?php echo dcPage::notices();?>
    <form id="setting-form" method="post" action="plugin.php">
      <div class="multi-part" id="settings" title="<?php echo  __('Activation');?>">
	<?php if ($is_super_admin):?>
	<div class="fieldset">
	  <h3><?php echo __('Activation');?></h3>
	  <p>
	    <label class="classic">
	      <?php echo form::checkbox(array('active'),'1',$active).' '.__('Enable plugin');?>
	    </label>
	  </p>
	</div>
	<?php endif;?>
      </div>
      <?php if ($active):?>
      <div class="multi-part" id="configuration" title="<?php echo __('Configuration');?>">
	<div class="fieldset">
	  <h3><?php echo __('Additionnal style sheet:');?></h3>
	  <p>
	    <label class="classic">
	      <?php echo form::textarea(array('public_extra_css'),164,10,$public_extra_css,'maximal');?>
	    </label>
	  </p>
	</div>

	<div class="fieldset" id="setting-event">
	  <h3><?php echo  __('Events');?></h3>
	  <p>
	    <label class="classic">
	      <?php
		 echo __('Show related entries on event:').'<br />'.
		 form::combo(array('public_posts_of_event_place'),$combo_place,$public_posts_of_event_place);
		 ?>
	    </label>
	  </p>

	  <h3><?php echo  __('Entries');?></h3>
	  <p>
	    <label class="classic">
	      <?php echo __('Show related events on entry:').'<br />'.
		    form::combo(array('public_events_of_post_place'),$combo_place,$public_events_of_post_place);?>
	    </label>
	  </p>
	</div>

	<div class="fieldset">
	  <h3><?php echo  __('Maps');?></h3>
	  <p>
	    <label class="classic">
	      <?php echo __('Default zoom on map:').'<br />'.
		    form::combo(array('public_map_zoom'),$combo_map_zoom,$public_map_zoom);?>
	    </label>
	  </p>
	  <p>
	    <label class="classic">
	      <?php echo __('Default type of map:').'<br />'.
		    form::combo(array('public_map_type'),$combo_map_type,$public_map_type);?>
	    </label>
	  </p>
	</div>
      </div>
      <?php endif;?>

      <?php if ($active):?>
      <div class="multi-part" id="categories" title="<?php echo  __('Categories');?>">
	<?php if (count($combo_categories) > 1):?>
	<h3><?php echo  __('Categories');?></h3>
	<p class="info"><?php echo __('When an event has an hidden category, it will only display on its category page.');?></p>
	<table class="clear">
	<tr>
	<th><?php echo __('Hide');?></th>
	<th><?php echo __('Category');?></th>
	<th><?php echo __('Level');?></th>
	<th><?php echo __('Entries');?></th>
	<th><?php echo __('Events');?></th>
	</tr>
	<?php while ($categories->fetch()):?>
	<?php
	   $hidden = in_array($categories->cat_id,$public_hidden_categories) || in_array($categories->cat_title,$public_hidden_categories);
	$nb_events = $core->blog->getPosts(array('cat_id'=>$categories->cat_id,'post_type'=>'eventhandler'),true)->f(0);
	?>
	<?php
	   if ($nb_events) {
	   $nb_events = '<a href="'.$p_url.'&amp;part=events&amp;cat_id='.$categories->cat_id.'" '.
			    'title="'.__('List of events related to this category').'">'.$nb_events.'</a>';
	   }
	   $nb_posts = $categories->nb_post;
	if ($nb_posts) {
	$nb_posts = '<a href="posts.php?cat_id='.$categories->cat_id.'" title="'.__('List of entries related to this category').'">'.$nb_posts.'</a>';
	}
	?>
	<tr class="line">
	  <td class="nowrap"><?php echo form::checkbox(array('public_hidden_categories[]'),$categories->cat_id,$hidden);?></td>
	  <td class="nowrap">
	    <a href="category.php?id=<?php echo $categories->cat_id;?>" title="<?php echo __('Edit this category');?>"><?php echo html::escapeHTML($categories->cat_title);?></a>
	  </td>
	  <td class="nowrap"><?php echo $categories->level;?></td>
	  <td class="nowrap"><?php echo $nb_posts;?></td>
	  <td class="nowrap"><?php echo $nb_events;?></td>
	</tr>
	<?php endwhile;?>
	</table>
	<?php endif;?>
      </div>
      <?php endif;?>

      <?php
	 /*Add a adminEventHandlerSettings behavior handler to add a custom tab to the eventhander settings page
	 and add a adminEventHandlerSettingsSave behavior handler to add save your custom settings.*/
	 $core->callBehavior("adminEventHandlerSettings");  ?>

      <p>
	<?php
	echo $core->formNonce().
	form::hidden(array('p'),'eventHandler').
	form::hidden(array('part'),'settings').
	form::hidden(array('action'),'savesettings')
	?>
	<input type="submit" name="save" value="<?php echo __('Save');?>"/>
      </p>
    </form>

    <?php if ($active && $core->plugins->moduleExists('eventdata')):?>
    <form id="setting-form" method="post" action="plugin.php">
      <div>
	<?php if (isset($eventdata_import) && $eventdata_import === true):?>
	<p class="message"><?php echo __('Events from eventdata successfully imported');?></p>
	<?php endif;?>

	<?php if ($s->eventdata_import):?>
	<p><?php echo __('Records of eventdata have been imported for this blog.');?></p>
	<?php endif;?>

	<p>
	  <input type="submit" name="save" value="<?php echo __('Import eventdata records');?>"/>
	  <?php
	     echo
	  $core->formNonce().
	  form::hidden(array('p'),'eventHandler').
	  form::hidden(array('part'),'settings').
	  form::hidden(array('section'),$section).
	  form::hidden(array('action'),'importeventdata');
	  ?>
	</p>
      </div>
    </form>
    <?php endif;?>

    <?php dcPage::helpBlock('eventHandler');?>
  </body>
</html>
