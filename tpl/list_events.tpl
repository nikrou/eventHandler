<html>
  <head>
    <title><?php echo __('Events');?></title>
    <?php echo $header;?>
    <?php echo dcPage::jsLoad('js/_posts_list.js');?>
    <?php echo dcPage::jsLoad('js/filter-controls.js');?>
    <script type="text/javascript">
      //<![CDATA[
      <?php echo dcPage::jsVar('dotclear.msg.show_filters', $show_filters ? 'true':'false');?>
      <?php echo dcPage::jsVar('dotclear.msg.filter_posts_list',$form_filter_title);?>
      <?php echo dcPage::jsVar('dotclear.msg.cancel_the_filter',__('Cancel filters and display options'));?>
      //]]>
    </script>
  </head>
  <body>
    <?php echo dcPage::breadcrumb(array(html::escapeHTML($core->blog->name) => '',__('Events') => '')).dcPage::notices();?>
    <?php if (!empty($message)):?>
    <?php echo $message;?>
    <?php endif;?>
    <p class="top-add"><a class="button add" href="<?php echo $p_url;?>&amp;part=event"><?php echo __('New event');?></a></p>
    <?php if ($from_id):?>
    <p class="info"><?php echo sprintf(__('Attach events to "%s" post.'), $from_post->post_title);?></p>
    <?php endif;?>

    <form action="<?php echo $p_url;?>" method="get" id="filters-form">
      <h3 class="out-of-screen-if-js"><?php echo $form_filter_title;?></h3>
      <div class="table">
	<div class="cell">
	  <h4><?php echo __('Filters');?></h4>
	  <p><label for="user_id" class="ib"><?php echo __('Author:');?></label>
	    <?php echo form::combo('user_id',$users_combo,$user_id);?>
	  </p>
	  <p><label for="cat_id" class="ib"><?php echo __('Category:');?></label>
	    <?php echo form::combo('cat_id',$categories_combo,$cat_id);?>
	  </p>
	  <p><label for="period" class="ib"><?php echo __('Period:');?></label>
	    <?php echo form::combo('period',$period_combo,$period);?>
	  </p>
	  <p><label for="status" class="ib"><?php echo __('Status:');?></label>
	    <?php echo form::combo('status',$status_combo,$status);?>
	  </p>
	</div>

	<div class="cell filters-sibling-cell">
	  <p><label for="selected" class="ib"><?php echo __('Selected:');?></label>
	    <?php echo form::combo('selected',$selected_combo,$selected);?>
	  </p>
	  <p><label for="month" class="ib"><?php echo __('Month:');?></label>
	    <?php echo form::combo('month',$dt_m_combo,$month);?>
	  </p>
	  <p><label for="lang" class="ib"><?php echo __('Lang:');?></label>
	    <?php echo form::combo('lang',$lang_combo,$lang);?>
	  </p>
	  <?php $core->callBehavior('adminEventHandlerEventsCustomFilterDisplay');?>
	</div>

	<div class="cell filters-options">
	  <h4><?php echo __('Display options');?></h4>
	  <p><label for="sortby" class="ib"><?php echo __('Order by:');?></label>
	    <?php echo form::combo('sortby',$sortby_combo,$sortby);?></p>
	  <p><label for="order" class="ib"><?php echo __('Sort:');?></label>
	    <?php echo form::combo('order',$order_combo,$order);?></p>
	  <p><span class="label ib"><?php echo __('Show');?></span>
	    <label for="nb" class="classic">
	      <?php echo form::field('nb',3,3,$nb_per_page).' '.__('Events per page');?></label>
	  </p>
	</div>

      </div>
      <p>
	<input type="submit" value="<?php echo __('Apply filters and display options');?>"/><br class="clear" />
	<?php echo form::hidden(array('p'),'eventHandler').form::hidden(array('part'),'events');?>
      </p>
    </form>

    <?php if ($from_id){?>
    <?php $post_list->display($page,$nb_per_page,
    '<form action="'.$p_url.'" method="post" id="form-entries">'.
      '%s'.
      '<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.
	  '<input type="submit" value="'.__('Attach selected events').'" />'.
	  form::hidden('action','eventhandler_bind_event').
	  form::hidden(array('from_id'),$from_id).
	  $hidden_fields.
	  '</p>'.
	'</div>'.
      '</form>'
    );
    } else {
    $post_list->display($page,$nb_per_page,
    '<form action="posts_actions.php" method="post" id="form-entries">'.
      '%s'.
      '<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.__('Selected entries action:').' '.
	  form::combo('action',$combo_action).
	  '<input type="submit" value="'.__('ok').'" />'.
	  $hidden_fields.
	  form::hidden(array('post_type'),'eventhandler').
	  form::hidden(array('redir'),$redir).
	  '</p>'.
	'</div>'.
      '</form>'
    );
    }
    ?>
    <?php echo $footer;?>
    <?php echo dcPage::helpBlock('eventHandler');?>
  </body>
</html>
