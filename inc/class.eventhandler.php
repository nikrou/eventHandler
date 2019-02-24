<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2019 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
#
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class eventHandler
{
	public $core;
	public $con;

	protected $type;
	protected $table;
	protected $blog;

	public function __construct($core,$type='eventhandler') {
		$this->core = $core;
		$this->con = $core->con;
		$this->type = (string) $type;
		$this->table = $core->prefix.'eventhandler';
		$this->blog = $core->con->escape($core->blog->id);
	}

	public static function cleanedParams($params) {
		# Prepare params
		if (!isset($params['columns'])) $params['columns'] = array();
		if (!isset($params['from'])) $params['from'] = '';
		if (!isset($params['sql'])) $params['sql'] = '';

		return $params;
	}

	# Get record of events
	public function getEvents($params,$count_only=false) {
		$params = self::cleanedParams($params);

		# Regain post_id
		if (isset($params['event_id'])) {
			$params['post_id'] = $params['event_id'];
			unset($params['event_id']);
		}
		# Regain post_type
		if (isset($params['event_type'])) {
			$params['post_type'] = $params['event_type'];
			unset($params['event_type']);
		}
		# Default post_type
		if (!isset($params['post_type'])) {
			$params['post_type'] = $this->type;
		}

		# Columns of table eventhandler
		if (!isset($params['columns'])) {
			$params['columns'] = array();
		}
		//Fixed bug on some PHP version
		$col = (array) $params['columns'];
		$col[] = 'event_startdt';
		$col[] = 'event_enddt';
		$col[] = 'event_address';
		$col[] = 'event_latitude';
		$col[] = 'event_longitude';
		$col[] = 'event_zoom';
		$params['columns'] = $col;

		# Tables
		$params['from'] = 'INNER JOIN '.$this->table.' EH ON  EH.post_id = P.post_id'.$params['from'];

		# Period
		if (!empty($params['event_period']) && $params['event_period'] != 'all') {
			switch ($params['event_period'])
			{
				case 'ongoing':
					$op = array('<','>','AND'); break;
				case 'outgoing':
					$op = array('>','<','OR'); break;
				case 'notstarted':
				case 'scheduled':
					$op = array('>','!'); break;
				case 'started':
					$op = array('<','!'); break;
				case 'notfinished':
					$op = array('!','>'); break;
				case 'finished':
					$op = array('!','<'); break;
				default:
					$op = array('=','=','AND'); break;
			}
			$now = date('Y-m-d H:i:s');

			# sqlite does not understand the TIMESTAMP function but understands the 'Y-m-d H:i:s' format just fine
			$timestamp = $this->con->driver() == 'sqlite' ? "" : " TIMESTAMP";

			$params['sql'] .= $op[0] != '!' && $op[1] != '!' ? 'AND (' : 'AND ';

			if (!empty($params['event_startdt']) && $op[0] != '!') {
				$params['sql'] .= "EH.event_startdt ".$op[0].$timestamp." '".$this->con->escape($params['event_startdt'])."'";
			} elseif (empty($params['event_startdt']) && $op[0] != '!') {
				$params['sql'] .= "EH.event_startdt ".$op[0].$timestamp." '".$now."'";
			}

			$params['sql'] .= $op[0] != '!' && $op[1] != '!' ? ' '.$op[2].' ' : '';

			if (!empty($params['event_enddt']) && $op[1] != '!') {
				$params['sql'] .= "EH.event_enddt ".$op[1].$timestamp." '".$this->con->escape($params['event_enddt'])."'";
			} elseif (empty($params['event_enddt']) && $op[1] != '!') {
				$params['sql'] .= "EH.event_enddt ".$op[1].$timestamp." '".$now."'";
			}

			$params['sql'] .= $op[0] != '!' && $op[1] != '!' ? ') ' : ' ';
		}

		# Cut start date
		if (!empty($params['event_start_year'])) {
			$params['sql'] .= 'AND '.$this->con->dateFormat('EH.event_startdt','%Y').' = '.
			"'".sprintf('%04d',$params['event_start_year'])."' ";
		}
		if (!empty($params['event_start_month'])) {
			$params['sql'] .= 'AND '.$this->con->dateFormat('EH.event_startdt','%m').' = '.
			"'".sprintf('%02d',$params['event_start_month'])."' ";
		}
		if (!empty($params['event_start_day'])) {
			$params['sql'] .= 'AND '.$this->con->dateFormat('EH.event_startdt','%d').' = '.
			"'".sprintf('%02d',$params['event_start_day'])."' ";
		}

		# Cut end date
		if (!empty($params['event_end_year'])) {
			$params['sql'] .= 'AND '.$this->con->dateFormat('EH.event_enddt','%Y').' = '.
			"'".sprintf('%04d',$params['event_end_year'])."' ";
		}
		if (!empty($params['event_end_month'])) {
			$params['sql'] .= 'AND '.$this->con->dateFormat('EH.event_enddt','%m').' = '.
			"'".sprintf('%02d',$params['event_end_month'])."' ";
		}
		if (!empty($params['event_endt_day'])) {
			$params['sql'] .= 'AND '.$this->con->dateFormat('EH.event_enddt','%d').' = '.
			"'".sprintf('%02d',$params['event_end_day'])."' ";
		}

		# Localization
		if (!empty($params['event_address'])) {
			$params['sql'] .= "AND EH.event_address = '".$this->con->escape($params['event_address'])."' ";
		}

		# --BEHAVIOR-- coreEventHandlerBeforeGetEvents
		$this->core->callBehavior('coreEventHandlerBeforeGetEvents',$this,array('params' => &$params));

		$rs = $this->core->blog->getPosts($params,$count_only);

        if (empty($params['sql_only'])) {
            $rs->eventHandler = $this;
            $rs->extend('rsExtEventHandlerPublic');
        }

		# --BEHAVIOR-- coreEventHandlerGetEvents
		$this->core->callBehavior('coreEventHandlerGetEvents',$rs);

		return $rs;
	}

	# Get record of events linked to a "normal post"
	public function getEventsByPost($params=array(),$count_only=false) {
		$params = self::cleanedParams($params);

		if (!isset($params['post_id'])) {
			return null;
		}
		if (!isset($params['event_type'])) {
			$params['event_type'] = $this->type;
		}

		$params['from'] .= ', '.$this->core->prefix.'meta EM ';

		if (strpos($this->con->driver(),'mysql')!==false) {
			$params['sql'] .= 'AND EM.meta_id = CAST(P.post_id as char) ';
		} else {
			$params['sql'] .= 'AND CAST(EM.meta_id as int) = CAST(P.post_id as int) ';
		}

		$params['sql'] .= "AND EM.post_id = '".$this->con->escape($params['post_id'])."' ";
		$params['sql'] .= "AND EM.meta_type = '".$this->con->escape($params['event_type'])."' ";

		unset($params['post_id']);

		return $this->getEvents($params,$count_only);
	}

	# Get record of "normal posts" linked to an event
	public function getPostsByEvent($params=array(),$count_only=false) {
		$params = self::cleanedParams($params);

		if (!isset($params['event_id'])) {
			return null;
		}
		if (!isset($params['event_type'])) {
			$params['event_type'] = $this->type;
		}
		if(!isset($params['post_type'])) {
			$params['post_type'] = '';
		}
		$params['from'] .= ', '.$this->core->prefix.'meta EM ';
		$params['sql'] .= 'AND EM.post_id = P.post_id ';
		$params['sql'] .= "AND EM.meta_id = '".$this->con->escape($params['event_id'])."' ";
		$params['sql'] .= "AND EM.meta_type = '".$this->con->escape($params['event_type'])."' ";

		unset($params['event_id'],$params['event_type']);

		return $this->core->blog->getPosts($params,$count_only);
	}

	# Add an event
	public function addEvent($cur_post,$cur_event) {
		if (!$this->core->auth->check('usage,contentadmin',$this->blog)) {
			throw new Exception(__('You are not allowed to create an event'));
		}

		try {
			# Clean cursor
			$this->getEventCursor(null,$cur_post,$cur_event);

			# --BEHAVIOR-- coreEventHandlerBeforeEventAdd
			$this->core->callBehavior("coreEventHandlerBeforeEventAdd",$this,$cur_post,$cur_event);

			# Adding first part of event record
			$cur_event->post_id = $this->core->blog->addPost($cur_post);

			# Create second part of event record
			$cur_event->insert();
		} catch (Exception $e) {
			$this->con->rollback();
			throw $e;
		}

		# --BEHAVIOR-- coreEventHandlerAfterEventAdd
		$this->core->callBehavior("coreEventHandlerAfterEventAdd",$this,$cur_event->post_id,$cur_post,$cur_event);
		return $cur_event->post_id;
	}

	# Update an event
	public function updEvent($post_id,$cur_post,$cur_event) {
		if (!$this->core->auth->check('usage,contentadmin',$this->blog)) {
			throw new Exception(__('You are not allowed to update events'));
		}

		$post_id = (integer) $post_id;

		if (empty($post_id)) {
			throw new Exception(__('No such event ID'));
		}

		$this->con->begin();
		try {
			# Clean cursor
			$this->getEventCursor($post_id,$cur_post,$cur_event);

			# --BEHAVIOR-- coreEventHandlerBeforeEventUpdate
			$this->core->callBehavior('coreEventHandlerBeforeEventUpdate',$this,$post_id,$cur_post,$cur_event);
			# Update first part of event record
			$this->core->blog->updPost($post_id,$cur_post);

			# Set post_id
			$cur_event->post_id = $post_id;

			# update second part of event record
			$cur_event->update("WHERE post_id = '".$post_id."' ");
		} catch (Exception $e) {
			$this->con->rollback();
			throw $e;
		}
		$this->con->commit();
	}

	# Delete an event
	public function delEvent($post_id) {
		if (!$this->core->auth->check('delete,contentadmin',$this->blog)) {
			throw new Exception(__('You are not allowed to delete events'));
		}

		$post_id = (integer) $post_id;

		if (empty($post_id)) {
			throw new Exception(__('No such event ID'));
		}

		# --BEHAVIOR-- coreEventHandlerEventDelete
		$this->core->callBehavior("coreEventHandlerEventDelete",$this,$post_id);

		# Delete first part of event record
		$this->core->blog->delPost($post_id);

		//what about reference key?
		# Delete second part of event record
		$this->con->execute('DELETE FROM '.$this->table.' '.'WHERE post_id = '.$post_id.' ');
	}

	# Clean cursor
	private function getEventCursor($post_id,$cur_post,$cur_event) {
		# Required a start date
		if ($cur_event->event_startdt == '') {
			throw new Exception(__('No event start date'));
		}
		# Required an end date
		if ($cur_event->event_enddt == '') {
			throw new Exception(__('No event end date'));
		}
		# Compare dates
		if (strtotime($cur_event->event_enddt) < strtotime($cur_event->event_startdt)) {
			throw new Exception(__('Start date greater than end date'));
		}
		# Full coordiantes or nothing
		if(($cur_event->event_latitude != '' && $cur_event->event_longitude == '')
		   || ($cur_event->event_latitude == '' && $cur_event->event_longitude != '')) {
			throw new Exception(__('Not full coordinate'));
		}
		# Coordinates format
		if ($cur_event->event_latitude != '') {
			if (!preg_match('/^(-|)[0-9.]+$/',$cur_event->event_latitude)) {
				throw new Exception(__('Wrong format of coordinate'));
			}
		}
		# Coordinates format
		if ($cur_event->event_longitude != '') {
			if (!preg_match('/^(-|)[0-9.]+$/',$cur_event->event_longitude)) {
				throw new Exception(__('Wrong format of coordinate'));
			}
		}
		# Set post type
		if (!$post_id && $cur_post->post_type == '') {
			$cur_post->post_type = $this->type;
		}

		# Force no comment
		$cur_post->unsetField('post_open_comment');
		$cur_post->post_open_comment = 0;

		# Force no trackback
		$cur_post->unsetField('post_open_tb');
		$cur_post->post_open_tb = 0;

		# unset post_id
		$cur_event->unsetField('post_id');

		# --BEHAVIOR-- coreEventHandlerGetEventCursor
		$this->core->callBehavior('coreEventHandlerGetEventCursor',$this,$post_id,$cur_post,$cur_event);
	}

	# Get human readable duration from integer
	public static function getReadableDuration($int,$format='second') {
		$int = (integer) $int;
		$time = '';
		//$sec = $min = $hou = $day = 0;

		//todo format
		$sec = $int % 60; $int -= $sec; $int /= 60;
		$min = $int % 60; $int -= $min; $int /= 60;
		$hou = $int % 24; $int -= $hou; $int /= 24;
		$day = $int;

		if ($day>1) $time .= sprintf(__('%s days'),$day).' ';
		if ($day==1) $time .=__('one day').' ';
		if ($hou>1) $time .= sprintf(__('%s hours'),$hou).' ';
		if ($hou==1) $time .= __('one hour').' ';
		if ($min>1) $time .= sprintf(__('%s minutes'),$min).' ';
		if ($min==1) $time .= __('one minute').' ';
		if (!$day && !$min && !$day && !$hou) $time .= __('instantaneous');

		return $time;
	}

	# Build HTML content for events maps
	# markers are in lib.eventhandler.rs.extension.php
	public static function getMapContent($width,$height,$type,$zoom,$info,$lat,$lng,$markers) {
		global $core;

		$style = '';
		if ($width || $height) {
			$style = 'style="';
			if ($width) {
				$style .= 'width:'.$width.';';
			}
			if ($height) {
				$style .= 'height:'.$height.';';
			}
			$style .= '" ';
		}

		$res = '<div style="display:none;" class="event-map">'."\n";
		$res .= '<div '.$style.'class="event-map-place"><p>'.__("Please wait, try to create map...").'</p></div>'."\n";
		$res .= '<div style="display:none;" class="event-map-info">'."\n";
		$res .= '<p class="event-map-info-zoom">'.$zoom.'</p>'."\n";
		$res .= '<p class="event-map-info-type">'.$type.'</p>'."\n";
		$res .= '<p class="event-map-info-info">'.$info.'</p>'."\n";
		$res .= '<p class="event-map-info-lat">'.$lat.'</p>'."\n";
		$res .= '<p class="event-map-info-lng">'.$lng.'</p>'."\n";

		if ($core->blog->settings->eventHandler->map_tile_layer) {
			$res .= '<p class="event-map-info-tile-layer">'.$core->blog->settings->eventHandler->map_tile_layer.'</p>'."\n";
		}
		$res .= '</div>'."\n";
		$res .= $markers."\n";
		$res .= '</div>';

		return $res;
	}
}
