<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of eventHandler, a plugin for Dotclear 2.
#
# Copyright(c) 2014-2015 Nicolas Roudaire <nikrou77@gmail.com> http://www.nikrou.net
#
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# Template values
class tplEventHandler
{
	#
	# Missing values
	#
	public static function BlogTimezone($a) {
		return self::tplValue($a,'$core->blog->settings->system->blog_timezone');
	}

	#
	# Events page
	#
	# URL of page of events list
	public static function EventsURL($a) {
		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_list")');
	}

	# Feed Url
	public static function EventsFeedURL($a) {
		$type = !empty($a['type']) ? $a['type'] : 'atom';

		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}

		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_feed").($_ctx->exists("categories") ? "/category/".$_ctx->categories->cat_url : "")."/'.$type.'"');
	}

	# Navigation menu
	public static function EventsMenuPeriod($attr, $content) {
		$menus = !empty($attr['menus']) ? $attr['menus'] : '';
		$separator = !empty($attr['separator']) ? $attr['separator'] : '';
		$list = !empty($attr['list']) ? $attr['list'] : '';
		$item = !empty($attr['item']) ? $attr['item'] : '';
		$active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';

		return "<?php echo tplEventHandler::EventsMenuPeriodHelper('".addslashes($menus)."','".addslashes($separator)."','".addslashes($list)."','".addslashes($item)."','".addslashes($active_item)."'); ?>";
	}

	# Navigation menu helper
	public static function EventsMenuPeriodHelper($menus, $separator, $list, $item, $active_item) {
		global $core, $_ctx;

		$default_menu = array(
			'all' => __('All') ,
			'ongoing' => __('Ongoing'),
			'outgoing' => __('Outgoing'),
			'scheduled' => __('Scheduled'),
			'started' => __('Started'),
			'notfinished' => __('Not finished'),
			'finished' => __('Finished')
		);
		# Only requested menus
		$menu = $default_menu;
		if (!empty($menus)) {
			$final_menu = array();
			$menus = explode(',',$menus);
			foreach($menus as $k) {
				if (isset($default_menu[$k])) {
					$final_menu[$k] = $default_menu[$k];
				}
			}
			if (!empty($final_menu)) {
				$menu = $final_menu;
			}
		}

		$separator = $separator ? html::decodeEntities($separator) : '';
		$list = $list ? html::decodeEntities($list) : '<ul>%s</ul>';
		$item = $item ? html::decodeEntities($item) : '<li><a href="%s">%s</a>%s</li>';
		$active_item = $active_item ? html::decodeEntities($active_item) : '<li class="nav-active"><a href="%s">%s</a>%s</li>';
		$url = $core->blog->url.$core->url->getBase("eventhandler_list").'/';
		if ($_ctx->exists('categories')) {
			$url .= 'category/'.$_ctx->categories->cat_url.'/';
		}

		$i = 1;
		$res = '';
		foreach($menu as $id => $name) {
			$i++;
			$sep = $separator && $i < count($menu)+1 ? $separator : '';

			if (isset($_ctx->event_params['event_period']) && $_ctx->event_params['event_period'] == $id) {
				$res .= sprintf($active_item,$url.$id,$name,$sep);
			} else {
				$res .= sprintf($item,$url.$id,$name,$sep);
			}
		}

		return '<div id="eventhandler-menu-period">'.sprintf($list,$res).'</div>';
	}

	# Sort order menu
	public static function EventsMenuSortOrder($attr) {
		$menus = !empty($attr['menus']) ? $attr['menus'] : '';
		$separator = !empty($attr['separator']) ? $attr['separator'] : '';
		$list = !empty($attr['list']) ? $attr['list'] : '';
		$item = !empty($attr['item']) ? $attr['item'] : '';
		$active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';

		return "<?php echo tplEventHandler::EventsMenuSortOrdertHelper('".addslashes($menus)."','".addslashes($separator)."','".addslashes($list)."','".addslashes($item)."','".addslashes($active_item)."'); ?>";
	}

	# Sort order menu helper
	public static function EventsMenuSortOrdertHelper($menus, $separator, $list, $item, $active_item) {
		global $core, $_ctx;

		$default_sort_id = array(
			'title' => 'LOWER(post_title)',
			'selected' => 'post_selected',
			'author' => 'LOWER(user_id)',
			'date' => 'post_dt',
			'startdt' => 'event_startdt',
			'enddt' =>'event_enddt'
		);
		$default_sort_text = array(
			'title' => __('Title'),
			'selected' => __('Selected'),
			'author' => __('Author'),
			'date' => __('Published date'),
			'startdt' => __('Start date'),
			'enddt' => __('End date')
		);

		# Only requested menus
		$menu = $default_sort_id;
		if (!empty($menus)) {
			$final_menu = array();
			$menus = explode(',',$menus);
			foreach($menus as $k) {
				if (isset($default_sort_id[$k])) {
					$final_menu[$k] = $default_sort_id[$k];
				}
			}
			if (!empty($final_menu)) {
				$menu = $final_menu;
			}
		}

		$separator = $separator ? html::decodeEntities($separator) : '';
		$list = $list ? html::decodeEntities($list) : '<ul>%s</ul>';
		$item = $item ? html::decodeEntities($item) : '<li><a href="%s">%s</a>%s</li>';
		$active_item = $active_item ? html::decodeEntities($active_item) : '<li class="nav-active"><a href="%s">%s</a>%s</li>';
		$period = !empty($_ctx->event_params['event_period']) ? $_ctx->event_params['event_period'] : 'all';
		$url = $core->blog->url.$core->url->getBase("eventhandler_list").'/';
		if ($_ctx->exists('categories')) {
			$url .= 'category/'.$_ctx->categories->cat_url.'/';
		}
		$url .= $period;

		$sortstr = $sortby = $sortorder = null;
		# Must quote array
		$quoted_default_sort_id = array();
		foreach($default_sort_id as $k => $v) {
			$quoted_default_sort_id[$k] = preg_quote($v);
		}

		if (isset($_ctx->event_params['order'])
            && preg_match('/('.implode('|',$quoted_default_sort_id).')\s(ASC|DESC)/i',$_ctx->event_params['order'],$sortstr)) {
			$sortby = in_array($sortstr[1],$default_sort_id) ? $sortstr[1]: '';
			$sortorder = preg_match('#ASC#i',$sortstr[2]) ? 'asc' : 'desc';
		}

		$i = 1;
		$res = '';
		foreach($menu as $id => $name) {
			$i++;
			$sep = $separator && $i < count($menu)+1 ? $separator : '';

			if ($sortby == $name) {
				$ord = $sortorder == 'asc' ? 'desc' : 'asc';
				$res .= sprintf($active_item,$url.'/'.$id.'/'.$ord,$default_sort_text[$id],$sep);
			} else {
				$ord = $sortorder == 'desc' ? 'desc' : 'asc';
				$res .= sprintf($item,$url.'/'.$id.'/'.$ord,$default_sort_text[$id],$sep);
			}
		}

		return '<div id="eventhandler-menu-sortorder">'.sprintf($list,$res).'</div>';
	}

	# Period info
	public static function EventsPeriod($attr) {
		if (!isset($attr['fulltext'])) {
			$fulltext = 0;
		} elseif(empty($attr['fulltext'])) {
			$fulltext = 1;
		} else {
			$fulltext = 2;
		}

		return "<?php echo tplEventHandler::EventsPeriodHelper('".$fulltext."'); ?>";
	}

	# Period helper
	public static function EventsPeriodHelper($fulltext) {
		global $_ctx;

		if ($fulltext == 2) {
			$text = array(
				'all' => __('All events'),
				'ongoing' => __('Current events'),
				'outgoing' => __('Event not being'),
				'scheduled' => __('Scheduled events'),
				'started' => __('Started events'),
				'notfinished' => __('Unfinished events'),
				'finished' => __('Completed events')
			);
		} elseif ($fulltext == 1) {
			$text = array(
				'all' => __('All'),
				'ongoing' => __('Ongoing'),
				'outgoing' => __('Outgoing'),
				'scheduled' => __('Scheduled'),
				'started' => __('Started'),
				'notfinished' => __('Not finished'),
				'finished' => __('Finished')
			);
		} else {
			$text = array(
				'all' => 'all',
				'ongoing' => 'ongoing',
				'outgoing' => 'outgoing',
				'scheduled' => 'scheduled',
				'started' => 'started',
				'notfinished' => 'notfinished',
				'finished' => 'finished'
			);
		}
		return isset($_ctx->event_params['event_period']) && isset($text[$_ctx->event_params['event_period']]) ? $text[$_ctx->event_params['event_period']] : $text['all'];
	}

	# Interval info
	public static function EventsInterval($attr) {
		$format = !empty($attr['format']) ? addslashes($attr['format']) : __('%m %d %Y');

		return "<?php echo tplEventHandler::EventsIntervalHelper('".$format."'); ?>";
	}

	# Interval info helper
	public static function EventsIntervalHelper($format) {
		global $_ctx;

		if (!empty($_ctx->event_params['event_start_year'])) {
			if (!empty($_ctx->event_params['event_start_day'])) {
				$dt = dt::str($format,mktime(0,0,0,$_ctx->event_params['event_start_month'],$_ctx->event_params['event_start_day'],$_ctx->event_params['event_start_year']));
				return sprintf(__('For the day of %s'),$dt);
			} elseif (!empty($_ctx->event_params['event_start_month'])) {
				$dt = dt::str(__('%m %Y'),mktime(0,0,0,$_ctx->event_params['event_start_month'],1,$_ctx->event_params['event_start_year']));
				return sprintf(__('For the month of %s'),$dt);
			} elseif (!empty($_ctx->event_params['event_start_year'])) {
				return sprintf(__('For the year of %s'),$_ctx->event_params['event_start_year']);
			}
		} else {
			$start = dt::dt2str($format,$_ctx->event_params['event_startdt']);
			$end = dt::dt2str($format,$_ctx->event_params['event_enddt']);

			if (strtotime($_ctx->event_params['event_startdt']) < strtotime($_ctx->event_params['event_enddt'])) {
				return sprintf(__('For the period between %s and %s'),$start,$end);
			} else {
				return sprintf(__('For the period through %s and %s'),$end,$start);
			}
		}
	}

	# Conditions
	public static function EventsIf($attr, $content) {
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['has_interval'])) {
			$sign = (boolean) $attr['has_interval'] ? '!' : '';
			$if[] = $sign.'empty($_ctx->event_params["event_interval"])';
		}

		if (isset($attr['has_category'])) {
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->exists("categories")';
		}

		if (isset($attr['has_period'])) {
			if ($attr['has_period']) {
				$if[] = '!empty($_ctx->event_params["event_period"]) && $_ctx->event_params["event_period"] != "all"';
			} else {
				$if[] = 'empty($_ctx->event_params["event_period"]) || !empty($_ctx->event_params["event_period"]) && $_ctx->event_params["event_period"] == "all"';
			}
		}

		if (isset($attr['period'])) {
			$if[] =
                '(!empty($_ctx->event_params["event_period"]) && $_ctx->event_params["event_period"] == "'.addslashes($attr['period']).'" '.
                '|| empty($_ctx->event_params["event_period"]) && ("" == "'.addslashes($attr['period']).'" || "all" == "'.addslashes($attr['period']).'")))';
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

    public static function EventsCount($attr, $content) {
		global $core;

        $if = '';

		if (isset($attr['value'])) {
            $sign = (boolean) $attr['value'] ? '>' : '==';
			$if = '$_ctx->nb_posts '.$sign.' 0';
		}

        if ($if) {
			return '<?php if('.$if.') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
    }

	#
	# Entries (on events page)
	#
	public static function EventsEntries($attr, $content) {
		global $core;

		$lastn = -1;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}

		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";

		if ($lastn != 0) {
			if ($lastn > 0) {
				$p .= "\$params['limit'] = ".$lastn.";\n";
			} else {
				$p .= "\$params['limit'] = \$_ctx->nb_entry_per_page;\n";
			}

			if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
				$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
			} else {
				$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
			}
		}

		if (isset($attr['author'])) {
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}

		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}

		if (isset($attr['no_category']) && $attr['no_category']) {
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}

		if (!empty($attr['type'])) {
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}

		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}

		if (isset($attr['period'])) {
			$p .= "\$params['event_period'] = '".addslashes($attr['period'])."';\n";
		}

		if (empty($attr['no_context'])) {
			$p .=
                'if ($_ctx->exists("users")) { '.
				"\$params['user_id'] = \$_ctx->users->user_id; ".
                "}\n";

			$p .=
                'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
                "}\n";

			$p .=
                'if ($_ctx->exists("archives")) { '.
				"\$params['post_year'] = \$_ctx->archives->year(); ".
				"\$params['post_month'] = \$_ctx->archives->month(); ";
			if (!isset($attr['lastn'])) {
				$p .= "unset(\$params['limit']); ";
			}
			$p .=
                "}\n";

			$p .=
                'if ($_ctx->exists("langs")) { '.
				"\$params['post_lang'] = \$_ctx->langs->post_lang; ".
                "}\n";

			$p .=
                'if (isset($_search)) { '.
				"\$params['search'] = \$_search; ".
                "}\n";

			$p .=
                'if ($_ctx->exists("event_params")) { '.
				"\$params = array_merge(\$params,\$_ctx->event_params); ".
                "}\n";
		}

		if (!empty($attr['order']) || !empty($attr['sortby'])) {
			$p .= "\$params['order'] = '".$core->tpl->getSortByStr($attr,'eventhandler')."';\n";
		} else {
            $special_attr = array();$order = $field = $table = '';
            if ($core->blog->settings->eventHandler->public_events_list_sortby && strpos($core->blog->settings->eventHandler->public_events_list_sortby,':')!==false) {
                list($table,$field) = explode(':', $core->blog->settings->eventHandler->public_events_list_sortby);
            }
            if ($core->blog->settings->eventHandler->public_events_list_order) {
                $order = $core->blog->settings->eventHandler->public_events_list_order;
            }
            $special_attr = array('order' => $order, 'sortby' => $field);
            $p .= "\$params['order'] = '".$core->tpl->getSortByStr($special_attr,$table)."';\n";
        }

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		if (isset($attr['selected'])) {
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}

		if (isset($attr['age'])) {
			$age = $core->tpl->getAge($attr);
			$p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'".$age."\'';\n" : '';
		}

		return
            "<?php\n".
            'if(!isset($eventHandler)) { $eventHandler = new eventHandler($core); } '."\n".
            '$params = array(); '."\n".
            $p.
            '$_ctx->post_params = $params; '."\n".
            '$_ctx->posts = $eventHandler->getEvents($params); unset($params); '."\n".
            '$_ctx->nb_posts = count($_ctx->posts); '."\n".
            "?>\n".
            '<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
            '$_ctx->posts = null; $_ctx->post_params = null; ?>';
	}

	# Pagination
	public static function EventsPagination($attr, $content) {
		$p =
            "<?php\n".
            'if(!isset($eventHandler)) { $eventHandler = new eventHandler($core); } '."\n".
            '$params = $_ctx->post_params; '."\n".
            '$_ctx->pagination = $eventHandler->getEvents($params,true); unset($params); '."\n".
            "?>\n";

		if (isset($attr['no_context']) && $attr['no_context']) {
			return $p.$content;
		}

		return
            $p.
            '<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
            $content.
            '<?php endif; ?>';
	}

	# Conditions
	public static function EventsEntryIf($attr,$content) {
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['has_category'])) {
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->cat_id';
		}

		if (isset($attr['has_address'])) {
			$sign = (boolean) $attr['has_address'] ? '!' : '=';
			$if[] = "'' ".$sign.'= $_ctx->posts->event_address';
		}

		if (isset($attr['has_geo'])) {
			$sign = (boolean) $attr['has_geo'] ? '' : '!';
			$if[] = $sign.'("" != $_ctx->posts->event_latitude && "" != $_ctx->posts->event_longitude)';
		}

		if (isset($attr['period'])) {
			$if[] = '$_ctx->posts->getPeriod() == "'.addslashes($attr['period']).'"';
		}

		if (isset($attr['sameday'])) {
			$sign = (boolean) $attr['sameday'] ? '' : '!';
			$if[] = $sign."\$_ctx->posts->isOnSameDay()";
		}

		if (isset($attr['oneday'])) {
			$sign = (boolean) $attr['oneday'] ? '' : '!';
			$if[] = $sign."\$_ctx->posts->isOnOneDay()";
		}

		if (!empty($attr['orderedby'])) {
			if (substr($attr['orderedby'],0,1) == '!') {
				$sign = '!';
				$orderedby = substr($attr['orderedby'],1);
			} else {
				$sign = '';
				$orderedby = $attr['orderedby'];
			}

			$default_sort = array(
				'date' => 'post_dt',
				'startdt' => 'event_startdt',
				'enddt' =>'event_enddt'
			);

			if (isset($default_sort[$orderedby])) {
				$orderedby = $default_sort[$orderedby];

				$if[] = $sign."strstr(\$_ctx->post_params['order'],'".addslashes($orderedby)."')";
			}
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

	# First event date
	public static function EventsDateHeader($attr, $content) {
		$type = '';
		if (!empty($attr['creadt'])) {
            $type = 'creadt';
        }
		if (!empty($attr['upddt'])) {
            $type = 'upddt';
        }
		if (!empty($attr['enddt'])) {
            $type = 'enddt';
        }
		if (!empty($attr['startdt'])) {
            $type = 'startdt';
        }

		return
            "<?php ".
            'if ($_ctx->posts->firstEventOfDay("'.$type.'")) : ?>'.
            $content.
            "<?php endif; ?>";
	}

	# Last event date
	public static function EventsDateFooter($attr,$content) {
		$type = '';
		if (!empty($attr['creadt'])) {
            $type = 'creadt';
        }
		if (!empty($attr['upddt'])) {
            $type = 'upddt';
        }
		if (!empty($attr['enddt'])) {
            $type = 'enddt';
        }
		if (!empty($attr['startdt'])) {
            $type = 'startdt';
        }

		return
            "<?php ".
            'if ($_ctx->posts->lastEventOfDay("'.$type.'")) : ?>'.
            $content.
            "<?php endif; ?>";
	}

	# Date of selected type
	public static function EventsEntryDate($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$iso8601 = !empty($a['iso8601']);
		$rfc822 = !empty($a['rfc822']);

		$type = '';
		if (!empty($a['creadt'])) {
            $type = 'creadt';
        }
		if (!empty($a['upddt'])) {
            $type = 'upddt';
        }
		if (!empty($a['enddt'])) {
            $type = 'enddt';
        }
		if (!empty($a['startdt'])) {
            $type = 'startdt';
        }

		if ($rfc822) {
			return self::tplValue($a,"\$_ctx->posts->getEventRFC822Date('".$type."')");
		} elseif ($iso8601) {
			return self::tplValue($a,"\$_ctx->posts->getEventISO8601Date('".$type."')");
		} else {
			return self::tplValue($a,"\$_ctx->posts->getEventDate('".$format."','".$type."')");
		}
	}

	# Time of selected type
	public static function EventsEntryTime($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$type = '';
		if (!empty($a['creadt'])) {
            $type = 'creadt';
        }
		if (!empty($a['upddt'])) {
            $type = 'upddt';
        }
		if (!empty($a['enddt'])) {
            $type = 'enddt';
        }
		if (!empty($a['startdt'])) {
            $type = 'startdt';
        }

		return self::tplValue($a,"\$_ctx->posts->getEventTime('".$format."','".$type."')");
	}

	# Category url
	public static function EventsEntryCategoryURL($a) {
		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_list")."/category/".html::sanitizeURL($_ctx->posts->cat_url)');
	}

	# Address
	public static function EventsEntryAddress($a) {
		$ics = !empty($a['ics']) ? '"LOCATION;CHARSET=UTF-8:".' : '';

		return self::tplValue($a,$ics.'$_ctx->posts->event_address');
	}

	# Latitude
	public static function EventsEntryLatitude($a) {
		return self::tplValue($a,'$_ctx->posts->event_latitude');
	}

	# Longitude
	public static function EventsEntryLongitude($a) {
		return self::tplValue($a,'$_ctx->posts->event_longitude');
	}

    # Zoom
	public static function EventsEntryZoom($a) {
		return self::tplValue($a,'$_ctx->posts->event_zoom');
	}

	# Duration
	public static function EventsEntryDuration($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';

		return self::tplValue($a,"eventHandler::getReadableDuration((strtotime(\$_ctx->posts->event_enddt) - strtotime(\$_ctx->posts->event_startdt)),'".$format."')");
	}

	# Period
	public static function EventsEntryPeriod($attr) {
		$scheduled = isset($attr['scheduled']) ? $attr['scheduled'] : 'scheduled';
		if (empty($attr['strict'])) {
            $scheduled = __($scheduled);
        }

		$ongoing = isset($attr['ongoing']) ? $attr['ongoing'] : 'ongoing';
		if (empty($attr['strict'])) {
            $ongoing = __($ongoing);
        }

		$finished = isset($attr['finished']) ? $attr['finished'] : 'finished';
		if (empty($attr['strict'])) {
            $finished = __($finished);
        }

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
            "<?php \$time = time() + dt::getTimeOffset(\$_ctx->posts->post_tz)*2;\n".
            "if (\$_ctx->posts->getEventTS('startdt') > \$time) {\n".
            " echo ".sprintf($f,"'".$scheduled."'")."; }\n".
            "elseif (\$_ctx->posts->getEventTS('startdt') < \$time && \$_ctx->posts->getEventTS('enddt') > \$time) {\n".
            " echo ".sprintf($f,"'".$ongoing."'")."; }\n".
            "elseif (\$_ctx->posts->getEventTS('enddt') < \$time) {\n".
            " echo ".sprintf($f,"'".$finished."'")."; }\n".
            "unset(\$time); ?>\n";
	}

	# Map
	public static function EventsEntryMap($attr) {
        if (!empty($attr['map_zoom'])) {
            $map_zoom =  abs((integer) $attr['map_zoom']);
        } else {
            $map_zoom =  '($_ctx->posts->event_zoom)?$_ctx->posts->event_zoom:$core->blog->settings->eventHandler->public_map_zoom';
        }
		$map_type = !empty($attr['map_type']) ? '"'.html::escapeHTML($attr['map_type']).'"' : '$core->blog->settings->eventHandler->public_map_type';
		$map_info = isset($attr['map_info']) && $attr['map_info'] == '0' ? '0' : '1';

		return '<?php echo eventHandler::getMapContent("","",'.$map_type.','.$map_zoom.','.$map_info.',$_ctx->posts->event_latitude,$_ctx->posts->event_longitude,$_ctx->posts->getMapVEvent()); ?>';
	}

	#
	# Events of an entry (on posts context)
	#
	public static function EventsOfPost($attr,$content) {
		global $core;

		$p = '';

		$lastn = -1;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
			if ($lastn > 0) {
				$p .= "\$params['limit'] = ".$lastn.";\n";
			}
		}

		if (isset($attr['event'])) {
			$p .= "\$params['event_id'] = '".abs((integer) $attr['event'])."';\n";
		}

		if (isset($attr['author'])) {
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}

		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}

		if (isset($attr['no_category']) && $attr['no_category']) {
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}

		if (isset($attr['post'])) {
			$p .= "\$params['post_id'] = '".abs((integer) $attr['post'])."';\n";
		}

		if (!empty($attr['type'])) {
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}

		$p .= "\$params['order'] = '".$core->tpl->getSortByStr($attr,'post')."';\n";

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		if (isset($attr['selected'])) {
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}

		if (isset($attr['age'])) {
			$age = $core->tpl->getAge($attr);
			$p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'".$age."\'';\n" : '';
		}

		return
            "<?php\n".
            'if(!isset($eventHandler)) { $eventHandler = new eventHandler($core); } '."\n".
            '$params = array(); '."\n".
            '$public_hidden_categories = @unserialize($core->blog->settings->eventHandler->public_hidden_categories); '.
            'if (is_array($public_hidden_categories)) { '.
            ' foreach($public_hidden_categories as $hidden_cat) { '.
            '  @$params[\'sql\'] .= " AND C.cat_id != \'".$core->con->escape($hidden_cat)."\' "; '.
            ' } '.
            "} \n".
            'if ($_ctx->exists("posts") && $_ctx->posts->post_id) { '.
            '$params["post_id"] = $_ctx->posts->post_id; '.
            "} \n".
            $p.
            'if (!empty($params["post_id"])) { '."\n".
            '$_ctx->eventsofpost_params = $params;'."\n".
            '$_ctx->eventsofpost = $eventHandler->getEventsByPost($params); unset($params); '."\n".
            'while ($_ctx->eventsofpost->fetch()) : ?>'.$content.'<?php endwhile; '.
            '} '."\n".
            '$_ctx->eventsofpost = null; $_ctx->eventsofpost_params = null; ?>';
	}

	public static function EventsOfPostHeader($attr, $content) {
		return
            "<?php if (\$_ctx->eventsofpost->isStart()) : ?>".
            $content.
            "<?php endif; ?>";
	}

	public static function EventsOfPostFooter($attr,$content) {
		return
            "<?php if (\$_ctx->eventsofpost->isEnd()) : ?>".
            $content.
            "<?php endif; ?>";
	}

	public static function EventOfPostIf($attr,$content) {
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['has_category'])) {
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->eventsofpost->cat_id';
		}

		if (isset($attr['has_address'])) {
			$sign = (boolean) $attr['has_address'] ? '!' : '=';
			$if[] = "'' ".$sign.'= $_ctx->eventsofpost->event_address';
		}

		if (isset($attr['period'])) {
			$if[] = '$_ctx->eventsofpost->getPeriod() == "'.addslashes($attr['period']).'"';
		}

		if (isset($attr['sameday'])) {
			$sign = (boolean) $attr['sameday'] ? '' : '!';
			$if[] = $sign."\$_ctx->eventsofpost->isOnSameDay()";
		}

		if (isset($attr['oneday'])) {
			$sign = (boolean) $attr['oneday'] ? '' : '!';
			$if[] = $sign."\$_ctx->eventsofpost->isOnOneDay()";
		}

		if (!empty($attr['orderedby'])) {
			if (substr($attr['orderedby'],0,1) == '!') {
				$sign = '!';
				$orderedby = substr($attr['orderedby'],1);
			} else {
				$sign = '';
				$orderedby = $attr['orderedby'];
			}

			$default_sort = array(
				'date' => 'post_dt',
				'startdt' => 'event_startdt',
				'enddt' =>'event_enddt'
			);

			if (isset($default_sort[$orderedby])) {
				$orderedby = $default_sort[$orderedby];

				$if[] = $sign."strstr(\$_ctx->eventsofpost['order'],'".addslashes($orderedby)."')";
			}
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

	public static function EventOfPostTitle($a) {
		return self::tplValue($a,'$_ctx->eventsofpost->post_title');
	}

	public static function EventOfPostURL($a) {
		return self::tplValue($a,'$_ctx->eventsofpost->getURL()');
	}

	public static function EventOfPostDate($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$iso8601 = !empty($a['iso8601']);
		$rfc822 = !empty($a['rfc822']);

		$type = '';
		if (!empty($a['creadt'])) {
            $type = 'creadt';
        }
		if (!empty($a['upddt'])) {
            $type = 'upddt';
        }
		if (!empty($a['enddt'])) {
            $type = 'enddt';
        }
		if (!empty($a['startdt'])) {
            $type = 'startdt';
        }

		if ($rfc822) {
			return self::tplValue($a,"\$_ctx->eventsofpost->getEventRFC822Date('".$type."')");
		} elseif ($iso8601) {
			return self::tplValue($a,"\$_ctx->eventsofpost->getEventISO8601Date('".$type."')");
		} else {
			return self::tplValue($a,"\$_ctx->eventsofpost->getEventDate('".$format."','".$type."')");
		}
	}

	public static function EventOfPostTime($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$type = '';
		if (!empty($a['creadt'])) {
            $type = 'creadt';
        }
		if (!empty($a['upddt'])) {
            $type = 'upddt';
        }
		if (!empty($a['enddt'])) {
            $type = 'enddt';
        }
		if (!empty($a['startdt'])) {
            $type = 'startdt';
        }

		return self::tplValue($a,"\$_ctx->eventsofpost->getEventTime('".$format."','".$type."')");
	}

	public static function EventOfPostAuthorCommonName($a) {
		return self::tplValue($a,'$_ctx->eventsofpost->getAuthorCN()');
	}

	public static function EventOfPostAuthorLink($a) {
		return self::tplValue($a,'$_ctx->eventsofpost->getAuthorLink()');
	}

	public static function EventOfPostCategory($a) {
		return self::tplValue($a,'$_ctx->eventsofpost->cat_title');
	}

	public static function EventOfPostCategoryURL($a) {
		return self::tplValue($a,'$core->blog->url.$core->url->getBase("eventhandler_list")."/category/".html::sanitizeURL($_ctx->eventsofpost->cat_url)');
	}

	public static function EventOfPostAddress($a) {
		return self::tplValue($a,'$_ctx->eventsofpost->event_address');
	}

	public static function EventOfPostDuration($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';

		return self::tplValue($a,"eventHandler::getReadableDuration((strtotime(\$_ctx->eventsofpost->event_enddt) - strtotime(\$_ctx->eventsofpost->event_startdt)),'".$format."')");
	}

	public static function EventOfPostPeriod($attr) {
		$scheduled = isset($attr['scheduled']) ? $attr['scheduled'] : 'scheduled';
		if (empty($attr['strict'])) {
            $scheduled = __($scheduled);
        }

		$ongoing = isset($attr['ongoing']) ? $attr['ongoing'] : 'ongoing';
		if (empty($attr['strict'])) {
            $ongoing = __($ongoing);
        }

		$finished = isset($attr['finished']) ? $attr['finished'] : 'finished';
		if (empty($attr['strict'])) {
            $finished = __($finished);
        }

		$f = $GLOBALS['core']->tpl->getFilters($attr);

		return
            "<?php \$time = time() + dt::getTimeOffset(\$_ctx->eventsofpost->post_tz)*2;\n".
            "if (\$_ctx->eventsofpost->getEventTS('startdt') > \$time) {\n".
            " echo ".sprintf($f,"'".$scheduled."'")."; }\n".
            "elseif (\$_ctx->eventsofpost->getEventTS('startdt') < \$time && \$_ctx->eventsofpost->getEventTS('enddt') > \$time) {\n".
            " echo ".sprintf($f,"'".$ongoing."'")."; }\n".
            "elseif (\$_ctx->eventsofpost->getEventTS('enddt') < \$time) {\n".
            " echo ".sprintf($f,"'".$finished."'")."; }\n".
            "unset(\$time); ?>\n";
	}

	#
	# Entries of an event (on events context)
	#
	public static function PostsOfEvent($attr,$content) {
		global $core;

		$p = '';

		$lastn = -1;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
			if ($lastn > 0) {
				$p .= "\$params['limit'] = ".$lastn.";\n";
			}
		}

		if (isset($attr['event'])) {
			$p .= "\$params['event_id'] = '".abs((integer) $attr['event'])."';\n";
		}

		if (isset($attr['author'])) {
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}

		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}

		if (isset($attr['no_category']) && $attr['no_category']) {
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}

		if (!empty($attr['type'])) {
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}

		$p .= "\$params['order'] = '".$core->tpl->getSortByStr($attr,'post')."';\n";

		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}

		if (isset($attr['selected'])) {
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}

		if (isset($attr['age'])) {
			$age = $core->tpl->getAge($attr);
			$p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'".$age."\'';\n" : '';
		}

		return
            "<?php\n".
            "\$postsofeventHandler = new eventHandler(\$core); \n".
            'if ($_ctx->exists("posts") && $_ctx->posts->post_id) { '.
            " \$params['event_id'] = \$_ctx->posts->post_id; ".
            "} \n".
            $p.
            '$_ctx->postsofevent_params = $params;'."\n".
            '$_ctx->postsofevent = $postsofeventHandler->getPostsByEvent($params); unset($params);'."\n".
            "?>\n".
            '<?php while ($_ctx->postsofevent->fetch()) : ?>'.$content.'<?php endwhile; '.
            '$_ctx->postsofevent = null; $_ctx->postsofevent_params = null; $postsofeventHandler = null; ?>';

		return $res;
	}

	public static function PostsOfEventHeader($attr, $content) {
		return
            "<?php if (\$_ctx->postsofevent->isStart()) : ?>".
            $content.
            "<?php endif; ?>";
	}

	public static function PostsOfEventFooter($attr, $content) {
		return
            "<?php if (\$_ctx->postsofevent->isEnd()) : ?>".
            $content.
            "<?php endif; ?>";
	}

	public static function PostOfEventIf($attr, $content) {
		global $core;

		$if = array();

		$operator = isset($attr['operator']) ? $core->tpl->getOperator($attr['operator']) : '&&';

		if (isset($attr['type'])) {
			$type = trim($attr['type']);
			$type = !empty($type)?$type:'post';
			$if[] = '$_ctx->postsofevent->post_type == "'.addslashes($type).'"';
		}

		if (isset($attr['has_category'])) {
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->postsofevent->cat_id';
		}

		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}

	public static function PostOfEventTitle($a) {
		return self::tplValue($a,'$_ctx->postsofevent->post_title');
	}

	public static function PostOfEventURL($a) {
		return self::tplValue($a,'$_ctx->postsofevent->getURL()');
	}

	public static function PostOfEventDate($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$iso8601 = !empty($a['iso8601']);
		$rfc822 = !empty($a['rfc822']);
		$type = (!empty($a['creadt']) ? 'creadt' : '');
		$type = (!empty($a['upddt']) ? 'upddt' : '');

		if ($rfc822) {
			return self::tplValue($a,"\$_ctx->postsofevent->getRFC822Date('".$type."')");
		} elseif ($iso8601) {
			return self::tplValue($a,"\$_ctx->postsofevent->getISO8601Date('".$type."')");
		} else {
			return self::tplValue($a,"\$_ctx->postsofevent->getDate('".$format."','".$type."')");
		}
	}

	public static function PostOfEventTime($a) {
		$format = !empty($a['format']) ? addslashes($a['format']) : '';
		$type = (!empty($a['creadt']) ? 'creadt' : '');
		$type = (!empty($a['upddt']) ? 'upddt' : '');

		return self::tplValue($a,"\$_ctx->postsofevent->getTime('".$format."','".$type."')");
	}

	public static function PostOfEventAuthorCommonName($a) {
		return self::tplValue($a,'$_ctx->postsofevent->getAuthorCN()');
	}

	public static function PostOfEventAuthorLink($a) {
		return self::tplValue($a,'$_ctx->postsofevent->getAuthorLink()');
	}

	public static function PostOfEventCategory($a) {
		return self::tplValue($a,'$_ctx->postsofevent->cat_title');
	}

	public static function PostOfEventCategoryURL($a) {
		return self::tplValue($a,'$_ctx->postsofevent->getCategoryURL()');
	}

	# Generic template value
	protected static function tplValue($a, $v) {
		return '<?php echo '.sprintf($GLOBALS['core']->tpl->getFilters($a),$v).'; ?>';
	}
}
