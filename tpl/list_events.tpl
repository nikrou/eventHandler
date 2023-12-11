<?php

use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\eventHandler\AdminBehaviors;
use Dotclear\Plugin\eventHandler\My;

?>
<html>
  <head>
    <title><?php echo __('Events');?></title>
    <?php echo $header;?>
    <?php echo Page::jsLoad('js/_posts_list.js');?>
		<?php echo dcCore::app()->admin->events_filter->js(My::manageUrl());?>
  </head>
  <body>
    <?php echo Page::breadcrumb([Html::escapeHTML(dcCore::app()->blog->name) => '', __('Events') => '']) . Notices::getNotices();?>
    <?php if (!empty($message)):?>
    <?php echo $message;?>
    <?php endif;?>
    <p class="top-add"><a class="button add" href="<?php echo dcCore::app()->admin->getPageURL();?>&amp;part=event"><?php echo __('New event');?></a></p>
    <?php if ($from_id):?>
    <p class="info"><?php echo sprintf(__('Attach events to "%s" post.'), $from_post->post_title);?></p>
    <?php endif;?>

		<?php dcCore::app()->admin->events_filter->display('admin.plugin.' . My::id());?>

    <?php if ($from_id) {?>
    <?php $post_list->display(
    $page,
    $nb_per_page,
    '<form action="' . dcCore::app()->admin->getPageURL() . '" method="post" id="form-entries">' .
                                                                     '%s' .
                                                                     '<div class="two-cols">' .
	'<p class="col checkboxes-helpers"></p>' .
	'<p class="col right">' .
	  '<input type="submit" value="' . __('Attach selected events') . '" />' .
	  form::hidden('action', AdminBehaviors::BIND_EVENT_ACTION) .
	  form::hidden(['from_id'], $from_id) .
	  $hidden_fields .
	  '</p>' .
	'</div>' .
                                                                     '</form>'
);
    } else {
        $post_list->display(
            $page,
            $nb_per_page,
            '<form action="posts_actions.php" method="post" id="form-entries">' .
              '%s' .
              '<div class="two-cols">' .
	'<p class="col checkboxes-helpers"></p>' .
	'<p class="col right">' . __('Selected entries action:') . ' ' .
	  form::combo('action', $combo_action) .
	  '<input type="submit" value="' . __('ok') . '" />' .
	  $hidden_fields .
	  form::hidden(['post_type'], 'eventhandler') .
	  form::hidden(['redir'], $redir) .
	  '</p>' .
	'</div>' .
              '</form>'
        );
    }

?>
    <?php echo $footer;?>
    <?php echo Page::helpBlock('eventHandler');?>
  </body>
</html>
