<html>
  <head>
    <title><?php echo __('Event handler'), ' - ', $page_title;?></title>
    <script type=""text/javascript" src = "http://maps.google.com/maps/api/js?sensor=false"></script>
    <?php
       echo
       dcPage::jsDatePicker().
       dcPage::jsToolBar().
       dcPage::jsModal().
       dcPage::jsMetaEditor().
       $admin_post_behavior.
       dcPage::jsLoad('js/_post.js').
       dcPage::jsLoad('index.php?pf=eventHandler/js/event.js').
       dcPage::jsLoad('index.php?pf=eventHandler/js/event-admin-map.js').
       dcPage::jsConfirmClose('entry-form','comment-form').
       # --BEHAVIOR-- adminEventHandlerHeaders
       $core->callBehavior('adminEventHandlerHeaders').
       dcPage::jsPageTabs($default_tab);
    ?>
    <link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/style.css"/>
    <?php echo $next_headlink."\n".$prev_headlink;?>
  </head>
  <body>
    <h2><?php echo html::escapeHTML($core->blog->name);?>
      &rsaquo; <a href="<?php echo $p_url;?>&amp;part=events"><?php echo __('Events');?></a>
      &rsaquo; <span class="page-title"><?php echo $page_title;?></span>
    </h2>

    <?php if (!empty($message)):?>
    <?php echo $message;?>
    <?php endif;?>

    <?php if ($post_id && $post->post_status==1):?>
    <p>
      <a class="onblog_link outgoing" href="<?php echo $post->getURL();?>" title="<?php echo $post_title;?>">
	<?php echo __('Go to this event on the site');?> <img src="images/outgoing-blue.png" alt=""/>
      </a>
    </p>
    <p>
      <?php
	 if ($prev_link) { echo $prev_link; }
	 if ($next_link && $prev_link) { echo ' - '; }
	 if ($next_link) { echo $next_link; }

	 # --BEHAVIOR-- adminEventHandlerNavLinks
	 $core->callBehavior('adminEventHandlerNavLinks',isset($post) ? $post : null);
      ?>
    </p>
    <?php endif;?>
    <?php if ($can_view_page && $can_edit_post):?>
    <div class="multi-part" title="<?php echo __('Edit event');?>" id="edit-entry">
      <form action="<?php echo $p_url;?>&part=event" method="post" id="entry-form">

      <div id="entry-sidebar">
	<div class="sb-box">
	  <h4><?php echo __('Status');?></h4>
	  <p class="entry-status">
	    <label for="post_status"><?php echo __('Event status');?></label>
	    <?php echo form::combo('post_status',$status_combo,$post_status,'maximal','',!$can_publish);?>
	  </p>
	  <p><label for="post_dt"><?php echo __('Published on');?></label>
	    <?php echo form::field('post_dt',16,16,$post_dt,'',3);?>
	  </p>
	  <div>
	    <h5 id="label_format"><label for="post_format" class="classic"><?php echo __('Text formatting');?></label></h5>
	    <p><?php echo form::combo('post_format',$available_formats,$post_format,'maximal');?></p>
	    <p class="format_control control_no_xhtml">
	      <a id="convert-xhtml" class="button<?php echo ($post_id && $post_format != 'wiki' ? ' hide' : '');?>" href="post.php?id=<?php echo $post_id;?>&amp;xconv=1"><?php echo __('Convert to XHTML');?></a></p>
	  </div>
	  <p>
	    <label>
	      <?php echo __('Entry language').form::combo('post_lang',$lang_combo,$post_lang,'',5);?>
	    </label>
	  </p>
	</div>
	<div class="sb-box">
	  <h4><?php echo __('Filing');?></h4>
	  <p>
	    <label class="classic">
	      <?php echo form::checkbox('post_selected',1,$post_selected,'',3).' '.__('Selected event');?>
	    </label>
	  </p>
	  <div>
	    <h5 id="label_cat_id"><?php echo __('Category');?></h5>
	    <p><label for="cat_id"><?php echo __('Category:');?></label>
	      <?php echo form::combo('cat_id',$categories_combo,$cat_id,'maximal');?>
	    </p>
	  </div>
	</div>
	<div class="sb-box">
	  <h4><?php echo __('Options');?></h4>
	  <div class="lockable">
	    <p>
	      <label><?php echo __('Edit basename').form::field('post_url',10,255,html::escapeHTML($post_url),'maximal',3);?></label>
	    </p>
	    <p class="form-note warn">
	      <?php echo __('Warning: If you set the URL manually, it may conflict with another entry.');?>
	      </p>
	  </div>
	</div>
	<?php
	   # --BEHAVIOR-- adminEventHandlerFormSidebar
	   $core->callBehavior('adminEventHandlerFormSidebar',isset($post) ? $post : null);
	?>
      </div>

	<div id="entry-content">
	  <div class="constrained">
	    <h3 class="out-of-screen-if-js"><?php echo __('Edit event');?></h3>

	    <div class="two-cols">
	      <div class="col">
		<p class="datepicker"><label class="required"><?php echo __('Start date:');?>
		    <?php echo form::field('event_startdt',16,16,$event_startdt,'datepicker',2);?>
		  </label>
		</p>
	      </div>
	      <div class="col">
		<p class="datepicker"><label class="required"><?php echo __('End date:');?>
		    <?php echo form::field('event_enddt',16,16,$event_enddt,'datepicker',2);?>
		  </label>
		</p>
	      </div>
	    </div>
	    <p id="event-area-title"><?php echo __('Localization:');?></p>
	    <div id="event-area-content">
	      <p><label><?php echo __('Address:');?>
		  <?php echo form::field('event_address',10,255,html::escapeHTML($event_address),'maximal',6);?>
		</label>
	      </p>

	      <div class="fieldset">
		<h3><?php echo __('Maps');?></h3>
		<p class="form-note"><?php echo __('If you want to use maps, you must enter an address as precise as possible (number, street, city, country)');?></p>
		<p><a id="event-map-link" href="#"><?php echo __('Find coordinates on googleMap');?></a></p>
		<div class="two-cols clearfix">
		  <div class="col">
		    <p><label><?php echo __('Latitude:');?>
			<?php echo form::field('event_latitude',16,16,$event_latitude,'',6);?>
		      </label>
		    </p>
		  </div>
		  <div class="col">
		    <p><label><?php echo __('Longitude:');?>
			<?php echo form::field('event_longitude',16,16,$event_longitude,'',6);?>
		      </label>
		    </p>
		  </div>
		</div>
	      </div>
	    </div>
		<?php
	       # --BEHAVIOR-- adminEventHandlerForm 
	       $core->callBehavior('adminEventHandlerForm',isset($post) ? $post : null);
		?>

	    <p class="col"><label class="required" title="<?php echo __('Required field');?>"><?php echo __('Title:');?>
		<?php echo form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2);?>
	      </label>
	    </p>
	    <p class="area" id="excerpt-area"><label for="post_excerpt"><?php echo __('Excerpt:');?></label>
	      <?php echo form::textarea('post_excerpt',50,5,html::escapeHTML($post_excerpt),'',2);?>
	    </p>
	    <p class="area">
	      <label class="required" title="<?php echo __('Required field');?>" for="post_content">
		<?php echo __('Content:');?>
	      </label>
	      <?php echo form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'',2);?>
	    </p>

	    <p class="area" id="notes-area"><label><?php echo __('Notes:');?></label>
	      <?php echo form::textarea('post_notes',50,5,html::escapeHTML($post_notes),'',2);?>
	    </p>
	    <p>
	      <input type="submit" value="<?php echo __('Save');?> (s)" tabindex="4" accesskey="s" name="save" />
	      <?php if ($post_id):?>
	      <a id="post-preview" href="<?php echo $preview_url;?>" class="button modal" accesskey="p">
		<?php echo __('Preview event');?>&nbsp;(p)
	      </a>
	      <?php else:?>
	      <a id="post-cancel" href="<?php echo $p_url;?>" class="button" accesskey="c"><?php echo __('Cancel');?> (c)</a>
	      <?php endif;?>
	      <?php
		 echo
		 ($post_id ? form::hidden('id',$post_id) : '').
		 ($can_delete ? '<input type="submit" value="'.__('Delete').'" class="delete" name="delete" />' : '').
		 $core->formNonce();
	      ?>
	    </p>
	  </div>
	</div>
      </form>
    </div>

    <div class="multi-part" title="<?php echo __('Related entries');?>" id="bind-entries">
      <?php if (!$post_id || $change):?>
      <p><?php echo __('You must save event before adding entries');?></p>
      <?php else:?>
      <?php $posts_list->display($page,$nb_per_page,'%s');?>
      <?php endif;?>
    </div>
	<?php
	       # --BEHAVIOR-- adminEventHandlerTab 
	       $core->callBehavior('adminEventHandlerTab',isset($post) ? $post : null);	
	?>
    <?php endif;?>
    <?php echo $footer;?>
    <?php dcPage::helpBlock('eventHandler');?>
  </body>
</html>
