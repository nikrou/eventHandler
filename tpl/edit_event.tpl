<?php

use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\eventHandler\AdminBehaviors;
use Dotclear\Plugin\eventHandler\My;
?>
<html>
    <head>
	<title><?php echo __('Event handler'), ' - ', $page_title;?></title>
	<?php if ($map_provider == 'googlemaps'):?>
	<script async defer src="//maps.google.com/maps/api/js?key=<?php echo $map_api_key;?>"></script>
	<?php endif;?>
	<?php
	echo
	Page::jsModal() .
	Page::jsMetaEditor() .
	$admin_post_behavior .
	Page::jsLoad('js/_post.js') .
	Page::jsLoad('index.php?pf=eventHandler/js/event.js') .
	Page::jsLoad('index.php?pf=eventHandler/js/' . $map_provider . '/event-admin-map.js') .
	Page::jsConfirmClose('entry-form', 'comment-form') .
	// --BEHAVIOR-- adminEventHandlerHeaders
	dcCore::app()->callBehavior('adminEventHandlerHeaders') .
	Page::jsPageTabs($default_tab) .
	AdminBehaviors::adminCss();
	?>
	<?php echo $next_headlink . "\n" . $prev_headlink;?>
    </head>
    <body>
	<?php
echo Page::breadcrumb([Html::escapeHTML(dcCore::app()->blog->name) => '',
    '<a href="' . dcCore::app()->admin->url->get('admin.plugin.eventHandler', ['part' => 'events']) . '">' . __('Events') . '</a>
	&rsaquo; <span class="page-title">' . $page_title . '</span>' => ''
]);
?>

	<?php echo Notices::getNotices();?>

	<?php if ($post_id && $post->post_status == 1):?>
	<p>
	    <a class="onblog_link outgoing" href="<?php echo $post->getURL();?>" title="<?php echo $post_title;?>">
		<?php echo __('Go to this event on the site');?> <img src="images/outgoing-link.svg" alt=""/>
	    </a>
	</p>
	<p>
	    <?php
     if ($prev_link) {
         echo $prev_link;
     }
     if ($next_link && $prev_link) {
         echo ' - ';
     }
     if ($next_link) {
         echo $next_link;
     }

     // --BEHAVIOR-- adminEventHandlerNavLinks
     dcCore::app()->callBehavior('adminEventHandlerNavLinks', isset($post) ? $post : null);
	    ?>
	</p>
	<?php endif;?>
	<?php if ($can_view_page && $can_edit_post):?>
	<div class="multi-part" title="<?php echo __('Edit event');?>" id="edit-entry">
	    <form action="<?php echo dcCore::app()->admin->getPageURL();?>&amp;part=event" method="post" id="entry-form">
		<div id="entry-wrapper">
		    <div id="entry-content">
			<div class="constrained">
			    <h3 class="out-of-screen-if-js"><?php echo __('Edit event');?></h3>

			    <div class="two-cols">
				<div class="col">
				    <p class="datepicker"><label class="required"><?php echo __('Start date:');?>
					<?php echo form::datetime('event_startdt', 16, 16, $event_startdt, 'datepicker', 2);?>
				    </label>
				    </p>
				</div>
				<div class="col">
				    <p class="datepicker">
					<label class="required"><?php echo __('End date:');?>
					    <?php echo form::datetime('event_enddt', 16, 16, $event_enddt, 'datepicker', 2);?>
					</label>
				    </p>
				</div>
			    </div>
			    <p id="event-area-title"><?php echo __('Localization');?></p>
			    <div id="event-area-content">
				<p>
				    <label><?php echo __('Address:');?>
					<?php echo form::field('event_address', 10, 255, Html::escapeHTML($event_address), 'maximal', 6);?>
				    </label>
				</p>

				<div class="fieldset">
				    <h3><?php echo __('Maps');?></h3>
				    <p class="info"><?php echo __('If you want to use maps, you must enter an address as precise as possible (number, street, city, country)');?></p>
				    <p><a id="event-map-link" href="#"><?php echo __('Find coordinates from address');?></a></p>
				    <p>
					<label><?php echo __('Latitude:');?>
					    <?php echo form::field('event_latitude', 30, 16, $event_latitude, '', 6);?>
					</label>
				    </p>
				    <p>
					<label><?php echo __('Longitude:');?>
					    <?php echo form::field('event_longitude', 30, 16, $event_longitude, '', 6);?>
					</label>
				    </p>
				    <p>
					<label><?php echo __('Zoom for that map:');?>
					    <?php echo form::field('event_zoom', 30, 16, $event_zoom, '', 6);?>
					</label>
				    </p>
				    <p class="form-note"><?php echo sprintf(__('If empty, defaut zoom (%d) will be used'), $events_default_zoom);?></p>
				</div>
			    </div>
			    <?php
			    // --BEHAVIOR-- adminEventHandlerForm
			    dcCore::app()->callBehavior('adminEventHandlerForm', isset($post) ? $post : null);
	    ?>

			    <p class="col"><label class="required" title="<?php echo __('Required field');?>"><?php echo __('Title:');?>
				<?php echo form::field('post_title', 20, 255, Html::escapeHTML($post_title), 'maximal', 2);?>
			    </label>
			    </p>
			    <p class="area" id="excerpt-area"><label for="post_excerpt"><?php echo __('Excerpt:');?></label>
				<?php echo form::textarea('post_excerpt', 50, 5, Html::escapeHTML($post_excerpt), '', 2);?>
			    </p>
			    <p class="area">
				<label class="required" title="<?php echo __('Required field');?>" for="post_content">
				    <?php echo __('Content:');?>
				</label>
				<?php echo form::textarea('post_content', 50, dcCore::app()->auth->getOption('edit_size'), Html::escapeHTML($post_content), '', 2);?>
			    </p>

			    <p class="area" id="notes-area"><label><?php echo __('Notes:');?></label>
				<?php echo form::textarea('post_notes', 50, 5, Html::escapeHTML($post_notes), '', 2);?>
			    </p>
			    <p>
				<input type="submit" value="<?php echo __('Save');?> (s)" tabindex="4" accesskey="s" name="save" />
				<?php if ($post_id):?>
				<a id="post-preview" href="<?php echo $preview_url;?>" class="button modal" accesskey="p">
				    <?php echo __('Preview event');?>&nbsp;(p)
				</a>
				<?php else:?>
				<a id="post-cancel" href="<?php echo dcCore::app()->admin->getPageURL();?>" class="button" accesskey="c"><?php echo __('Cancel');?> (c)</a>
				<?php endif;?>
				<?php
				echo
				($post_id ? form::hidden('id', $post_id) : '') .
				($can_delete ? '<input type="submit" value="' . __('Delete') . '" class="delete" name="delete" />' : '') .
				dcCore::app()->formNonce();
				?>
			    </p>
			</div>
		    </div>
		</div>
		<div id="entry-sidebar">
		    <div class="sb-box">
			<h4><?php echo __('Status');?></h4>
			<p class="entry-status">
			    <label for="post_status"><?php echo __('Event status');?></label>
			    <?php echo form::combo('post_status', $status_combo, $post_status, 'maximal', '', !$can_publish);?>
			</p>
			<p><label for="post_dt"><?php echo __('Published on');?></label>
			    <?php echo form::datetime('post_dt', 16, 16, $post_dt, '', 3);?>
			</p>
			<div>
			    <h5 id="label_format"><label for="post_format" class="classic"><?php echo __('Text formatting');?></label></h5>
			    <p><?php echo form::combo('post_format', $available_formats, $post_format, 'maximal');?></p>
			    <p class="format_control control_no_xhtml">
				<a id="convert-xhtml" class="button<?php echo ($post_id && $post_format != 'wiki' ? ' hide' : '');?>" href="<?php echo dcCore::app()->admin->getPageURL();?>&amp;part=event&amp;id=<?php echo $post_id;?>&amp;xconv=1"><?php echo __('Convert to XHTML');?></a></p>
			</div>
			<p>
			    <label>
				<?php echo __('Entry language') . form::combo('post_lang', $lang_combo, $post_lang, '', 5);?>
			    </label>
			</p>
		    </div>
		    <div class="sb-box">
			<h4><?php echo __('Filing');?></h4>
			<p>
			    <label class="classic">
				<?php echo form::checkbox('post_selected', 1, $post_selected, '', 3) . ' ' . __('Selected event');?>
			    </label>
			</p>
			<div>
			    <h5 id="label_cat_id"><?php echo __('Category');?></h5>
			    <p><label for="cat_id"><?php echo __('Category:');?></label>
				<?php echo form::combo('cat_id', $categories_combo, $cat_id, 'maximal');?>
			    </p>
			</div>
		    </div>
		    <div class="sb-box">
			<h4><?php echo __('Options');?></h4>
			<div class="lockable">
			    <p>
				<label><?php echo __('Edit basename') . form::field('post_url', 10, 255, Html::escapeHTML($post_url), 'maximal', 3);?></label>
			    </p>
			    <p class="form-note warn">
				<?php echo __('Warning: If you set the URL manually, it may conflict with another entry.');?>
			    </p>
			</div>
		    </div>
		    <?php
		    // --BEHAVIOR-- adminEventHandlerFormSidebar
		    dcCore::app()->callBehavior('adminEventHandlerFormSidebar', isset($post) ? $post : null);
		    ?>
		</div>
	    </form>
	</div>

	<div class="multi-part" title="<?php echo __('Related entries');?>" id="bind-entries">
	    <?php if (!$post_id || $change):?>
	    <p><?php echo __('You must save event before adding entries');?></p>
	    <?php else:?>
	    <?php $posts_list->display($page, $nb_per_page, '%s');?>
	    <?php endif;?>
	</div>
	<?php
	// --BEHAVIOR-- adminEventHandlerTab
	dcCore::app()->callBehavior('adminEventHandlerTab', isset($post) ? $post : null);
	 ?>
	<?php endif;?>
	<?php Page::helpBlock('eventHandler');?>
    </body>
</html>
