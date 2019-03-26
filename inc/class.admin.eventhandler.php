<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2016 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class adminEventHandler
{
	# Dashboard icon
	public static function adminDashboardIcons($core, $icons) {
		$icons['eventHandler'] = new ArrayObject(array(
			__('Event handler'),
			'plugin.php?p=eventHandler',
			'index.php?pf=eventHandler/icon.png'
		));
	}

	# Dashboard fav icon
	public static function adminDashboardFavs($core, $favs) {
		$favs['eventHandler'] = new ArrayObject(array(
			'eventHandler',
			'Event handler',
			'plugin.php?p=eventHandler',
			'index.php?pf=eventHandler/icon.png',
			'index.php?pf=eventHandler/icon-b.png',
			'usage,contentadmin',null,null
		));
	}

    public static function adminPageHTTPHeaderCSP($csp) {
		global $core;

        if ($core->blog->settings->eventHandler->map_provider==='googlemaps') {
            $host_map_provider = 'csi.gstatic.com maps.google.com maps.googleapis.com';
            if (isset($csp['img-src'])) {
                $csp['img-src'] .= ' csi.gstatic.com';
            } else {
                $csp['img-src'] = 'csi.gstatic.com';
            }

            if (isset($csp['img-src'])) {
                $csp['img-src'] .= ' *.google.com *.gstatic.com *.googleapis.com';
            } else {
                $csp['img-src'] = '*.google.com *.gstatic.com *.googleapis.com';
            }

            if (isset($csp['script-src'])) {
                $csp['script-src'] .= ' ' . $host_map_provider;
            } else {
                $csp['script-src'] = $host_map_provider;
            }
        } else { // osm
            $host_map_provider = 'nominatim.openstreetmap.org';

            if (isset($csp['img-src'])) {
                $csp['img-src'] .= ' tile.openstreetmap.org';
            } else {
                $csp['img-src'] = 'tile.openstreetmap.org';
            }
        }

        if (isset($csp['connect-src'])) {
            $csp['connect-src'] .= ' ' . $host_map_provider;
        } else {
            $csp['connect-src'] = $csp['default-src'] . ' ' . $host_map_provider;
        }
    }

	# post.php
	# Headers, jQuery features to remove events from a post
	public static function adminPostHeaders() {
		return
		self::adminCss().
		dcPage::jsLoad('index.php?pf=eventHandler/js/post.js');
	}

	# posts.php
	# Combo of actions on multiple posts
	public static function adminPostsActionsCombo($args) {
		# usage, contentadmin
		$args[0][__('Events')][__('Bind events')] = 'eventhandler_bind_event';
		$args[0][__('Events')][__('Unbind events')] = 'eventhandler_remove_event';
	}

	public static function adminPostsActionsPage($core,dcPostsActionsPage $ap){
		if ($core->auth->check('publish,contentadmin',$core->blog->id)) {
			$ap->addAction(array(__('Events') => array(
					__('Bind events') => 'eventhandler_bind_event',
					__('Unbind events') => 'eventhandler_unbind_post'
				)),
				array('adminEventHandler','doBindUnbind')
			);
		}
	}

	public static function doBindUnBind($core, dcPostsActionsPage $ap, $post){
		$action = $ap->getAction();
		if ($action!='eventhandler_bind_event' && $action!='eventhandler_unbind_post') {
			return;
        }

		$posts_ids = $ap->getIDs();
		if (empty($posts_ids)) {
			throw new Exception(__('No entry selected'));
		}
		$params['sql'] = ' AND P.post_id '.$core->con->in($posts_ids).' ';
		$posts = $core->blog->getPosts($params);

		if ($action == 'eventhandler_bind_event') {
			if (isset($post['events'])) {
				foreach ($post['events'] as $k => $v)	{
					$events_id[$k] = (integer) $v;
				}
				$params['sql'] = 'AND P.post_id '.$core->con->in($events_id).' ';
				$eventHandler = new eventHandler($core);
				$events = $eventHandler->getEvents($params);
				if ($events->isEmpty()) {
					throw new Exception(__('No such event'));
				}
				$meta_ids = array();
				while ($events->fetch()) {
					$meta_ids[] = $events->post_id;
				}

				while ($posts->fetch()) {
					foreach($meta_ids as $meta_id)	{
						$core->meta->delPostMeta($posts->post_id,'eventhandler',$meta_id);
						$core->meta->setPostMeta($posts->post_id,'eventhandler',$meta_id);
					}
				}
				dcPage::addSuccessNotice(sprintf(
					__(
						'%d entry has been bound %s',
						'%d entries have been bound %s',
						count($posts_ids)
					),
					count($posts_ids),__('to the selected event','to the selected events',$events->count()))
				);
				$ap->redirect(true);
			} else {
				$ap->beginPage('',self::adminCss());
				echo '<h3>'.__('Select events to link to entries').'</h3>';
				$eventHandler = new eventHandler($core);

				$params = array();
				$params['no_content'] = true;
				$params['order'] = 'event_startdt DESC';
				$params['period'] = 'notfinished';

				# --BEHAVIOR-- adminEventHandlerMinilistCustomize
				$core->callBehavior('adminEventHandlerMinilistCustomize',array('params' => &$params));

				$events = $eventHandler->getEvents($params);
				$counter = $eventHandler->getEvents($params,true);
				$list = new adminEventHandlerMiniList($core,$events,$counter->f(0));

				echo $list->display(1,100,
					'<form action="posts_actions.php" method="post">'.

					'%s'.

					'<p>'.
					$ap->getHiddenFields().
					$ap->getIDsHidden().
					$core->formNonce().
					form::hidden(array('action'),'eventhandler_bind_event').
					'<input type="submit" value="'.__('Save').'" /></p>'.
					'</form>'
				);
				$ap->endPage();
			}
		}
		# Unbind all posts from selected events
		if ($action == 'eventhandler_unbind_post') {
			if (!$posts->isEmpty()) { //called from posts.php
				while ($posts->fetch()) {
					$core->meta->delPostMeta($posts->post_id,'eventhandler');
				}
			dcPage::addSuccessNotice(sprintf(
				__(
					'%d post has been unbound from its events',
					'%d posts have been unbound from their events',
					count($posts_ids)
				),
				count($posts_ids)));
			} elseif (isset($post['entries'])) {
				$eventHandler = new eventHandler($core);
				foreach ($post['entries'] as $k => $v)	{
					$params = array('event_id'=>$v);
					$posts = $eventHandler->getPostsByEvent($params);
					$event = $eventHandler->getEvents($params);
					if ($posts->isEmpty()) {
						dcPage::addWarningNotice(sprintf(
						__('Event #%d (%s) has no related post to be unbound from.'),
						$v,$event->post_title));
						continue;
					}
					while ($posts->fetch()) {
						$core->meta->delPostMeta($posts->post_id,'eventhandler',$v);
					}
					dcPage::addSuccessNotice(sprintf(
					__(
						'Event #%d (%s) unbound from %d related post',
						'Event #%d (%s) unbound from %d related posts',
						$posts->count()
					),
					$v,$event->post_title,$posts->count()));
				}
				$ap->redirect(false);
			} else {
				throw new Exception("adminEventhandler::doBindUnBind Should never happen, $action action called with no post nor event specified.");
			}
			$ap->redirect(true);
		}
	}

    public static function adminPostFormItems($main_items, $sidebar_items, $post=null) {
		global $core;

        if ($post === null) {
            return;
        }

		# Get linked events
		$events = null;
		$params = array();
		$params['post_id'] = $post->post_id;
		$params['no_content'] = true;

		try {
			$eventHandler = new eventHandler($core);
			$events = $eventHandler->getEventsByPost($params);
			if ($events->isEmpty()) {
				$events = null;
			}
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}

		$res = '<div>';
        $res .= '<h5 id="eventhandler-form-title">'.__('Events').'</h5>';
        $res .= '<div id="eventhandler-form-content">';

		# Related events
		if ($events) {
			$res .= '<ul class="event-list">';

			while($events->fetch()) {
				$res .= '<li class="event-node event-node-'.$events->getPeriod().'">';
                $res .= '<label title="'.__('Check to unbind').'" class="classic">';
                $res .= form::checkbox(array('eventhandler_events[]'), $events->post_id,'','event-node-value');
                $res .= html::escapeHTML($events->post_title).'</label></li>';
			}

			$res .= '</ul>';
		}

		# Bind a event to this post
		$res .= '<p><a href="plugin.php?p=eventHandler&amp;part=events&amp;from_id='.$post->post_id.'">'.__('Bind events').'</a>';

		# Change post into event publish,contenadmin
		if($core->auth->check('publish,contentadmin',$core->blog->id)) {
            $res .= '<p><a href="plugin.php?p=eventHandler';
            $res .= '&amp;part=event&amp;from_id='.$post->post_id;
            $res .= '" title="'.__('Change this entry into an event').'">'.__('Change into event').'</a>';
		}

		$res .= '</p></div></div>';

        $sidebar_items['metas-box']['items']['eventhandler'] = $res;
    }

	# post.php
	# This delete relation between this post and ckecked related event (without javascript)
	public static function adminAfterPostSave($cur, $post_id) {
		global $core;

		if (!$post_id) {
            return;
        }

		if (empty($_POST['eventhandler_events']) || !is_array($_POST['eventhandler_events'])) {
            return;
        }


		try {
			foreach($_POST['eventhandler_events'] as $event_id) {
				$event_id = abs((integer) $event_id);
				if (!$event_id) {
                    continue;
                }

				$core->meta->delPostMeta($post_id,'eventhandler',$event_id);
			}
		} catch (Exception $e) {
			//$core->error->add($e->getMessage());
		}

	}

	# post.php
	# This delete relation between this post and all there events
	public static function adminBeforePostDelete($post_id) {
		if (!$post_id) {
            return;
        }

		global $core;

		try {
			$core->meta->delPostMeta($post_id,'eventhandler');
		} catch (Exception $e) {
			//$core->error->add($e->getMessage());
		}
	}

        # Returns the admin css according to the darkmode setting
        public static function adminCss() {
            global $core;

            $style = "style.css";
            if ($core->auth->user_prefs->interface->darkmode == 1) {
                $style = "dark-style.css";
            }

            return '<link rel="stylesheet" type="text/css" href="index.php?pf=eventHandler/css/'.$style.'" />'."\n";
        }
}
