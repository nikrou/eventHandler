<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2015 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# List of events
class adminEventHandlertList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='') {
		if ($this->rs->isEmpty()) {
			echo '<p><strong>'.__('No event').'</strong></p>';
		} else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';

			$columns = array(
                '<th colspan="2">'.__('Title').'</th>',
                '<th>'.__('Start date').'</th>',
                '<th>'.__('End date').'</th>',
                '<th>'.__('Entries').'</th>',
                '<th>'.__('Date').'</th>',
                '<th>'.__('Category').'</th>',
                '<th>'.__('Author').'</th>',
                '<th>'.__('Status').'</th>'
            );

			# --BEHAVIOR-- adminEventHandlerEventsListHeaders
			$this->core->callBehavior('adminEventHandlerEventsListHeaders',array('columns' => &$columns));
			$html_block = '<table class="clear"><tr>'.
                join('',$columns).
                '</tr>%s</table>';

			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';

			$blocks = explode('%s',$html_block);

			echo $blocks[0];

			while ($this->rs->fetch()) {
				echo $this->postLine();
			}

			echo $blocks[1];

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function postLine() {
		if ($this->core->auth->check('categories',$this->core->blog->id)) {
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		} else {
			$cat_link = '%2$s';
		}

		if ($this->rs->cat_title) {
			$cat_title = sprintf($cat_link,$this->rs->cat_id,html::escapeHTML($this->rs->cat_title));
		} else {
			$cat_title = __('None');
		}

		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status)
		{
        case 1:
            $img_status = sprintf($img,__('published'),'check-on.png');
            break;
        case 0:
            $img_status = sprintf($img,__('unpublished'),'check-off.png');
            break;
        case -1:
            $img_status = sprintf($img,__('scheduled'),'scheduled.png');
            break;
        case -2:
            $img_status = sprintf($img,__('pending'),'check-wrn.png');
            break;
		}

		$protected = '';
		if ($this->rs->post_password) {
			$protected = sprintf($img,__('protected'),'locker.png');
		}

		$selected = '';
		if ($this->rs->post_selected) {
			$selected = sprintf($img,__('selected'),'selected.png');
		}

		$now = time();
		if (strtotime($this->rs->event_startdt) > $now) {
			$event_class = 'eventhandler scheduled';
		} elseif (strtotime($this->rs->event_enddt) < $now) {
			$event_class = 'eventhandler finished';
		} else {
			$event_class = 'eventhandler ongoing';
		}

		$entries = $this->rs->eventHandler->getPostsByEvent(array('event_id'=>$this->rs->post_id),true);
        $nb_entries = '';
		if ($entries->isEmpty()) {
			$nb_entries = 0;
		} else {
			$nb_entries = '<a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id);
            $nb_entries .= '&amp;tab=bind-entries">'.$entries->f(0).'</a>';
		}

		$columns = array(
            '<td class="nowrap">'.form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
            '<td class="maximal"><a href="'.$this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id).'">'.
            html::escapeHTML($this->rs->post_title).'</a></td>',
			'<td class="nowrap'.' '.$event_class.'">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->event_startdt).'</td>',
            '<td class="nowrap'.' '.$event_class.'">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->event_enddt).'</td>',
            '<td class="nowrap">'.$nb_entries.'</td>',
            '<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>',
            '<td class="nowrap">'.$cat_title.'</td>',
            '<td class="nowrap">'.$this->rs->user_id.'</td>',
            '<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.'</td>'
        );

        # --BEHAVIOR-- adminEventHandlerEventsListBody
        $this->core->callBehavior('adminEventHandlerEventsListBody', $this->rs, array('columns' => &$columns));

		return
            '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').' '.$event_class.'"'.
            ' id="p'.$this->rs->post_id.'">'.
            join("",$columns).
            '</tr>';
	}
}
