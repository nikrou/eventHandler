<?php
/*
 *  -- BEGIN LICENSE BLOCK ----------------------------------
 *
 *  This file is part of eventHandler, a plugin for Dotclear 2.
 *
 *  Copyright(c) 2014-2023 Nicolas Roudaire <nikrou77@gmail.com> https://www.nikrou.net
 *
 *  Copyright (c) 2009-2013 Jean-Christian Denis and contributors
 *  contact@jcdenis.fr https://chez.jcdenis.fr/
 *
 *  Licensed under the GPL version 2.0 license.
 *  A copy of this license is available in LICENSE file or at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 *  -- END LICENSE BLOCK ------------------------------------
 */

declare(strict_types=1);

namespace Dotclear\Plugin\eventHandler;

use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Date;
use ArrayObject;
use Dotclear\App;

class Template
{
    public static function BlogTimezone(mixed $a): string
    {
        return self::tplValue($a, 'dcCore::app()->blog->settings->system->blog_timezone');
    }

    public static function EventsURL(mixed $a): string
    {
        return self::tplValue($a, 'App::blog()->url().App::url()->getBase("eventhandler_list")');
    }

    public static function EventsFeedURL(mixed $a): string
    {
        $type = !empty($a['type']) ? $a['type'] : 'atom';

        if (!preg_match('#^(rss2|atom)$#', (string) $type)) {
            $type = 'atom';
        }

        return self::tplValue($a, 'App::blog()->url().App::url()->getBase("eventhandler_feed").(App::frontend()->context()->exists("categories") ? "/category/".App::frontend()->context()->categories->cat_url : "")."/' . $type . '"');
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsMenuPeriod(ArrayObject $attr, string $content): string
    {
        $menus = !empty($attr['menus']) ? $attr['menus'] : '';
        $separator = !empty($attr['separator']) ? $attr['separator'] : '';
        $list = !empty($attr['list']) ? $attr['list'] : '';
        $item = !empty($attr['item']) ? $attr['item'] : '';
        $active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';

        return "<?php echo Dotclear\\Plugin\\eventHandler\\Template::EventsMenuPeriodHelper('" . addslashes((string) $menus) . "','" . addslashes((string) $separator) . "','" . addslashes((string) $list) . "','" . addslashes((string) $item) . "','" . addslashes((string) $active_item) . "'); ?>";
    }

    public static function EventsMenuPeriodHelper(string $menus, string $separator, string $list, string $item, string $active_item): string
    {
        $default_menu = [
            'all' => __('All'),
            'ongoing' => __('Ongoing'),
            'outgoing' => __('Outgoing'),
            'scheduled' => __('Scheduled'),
            'started' => __('Started'),
            'notfinished' => __('Not finished'),
            'finished' => __('Finished'),
        ];

        $menu = $default_menu;
        if (!empty($menus)) {
            $final_menu = [];
            $menus = explode(',', $menus);
            foreach ($menus as $k) {
                if (isset($default_menu[$k])) {
                    $final_menu[$k] = $default_menu[$k];
                }
            }
            if (!empty($final_menu)) {
                $menu = $final_menu;
            }
        }

        $separator = $separator ? Html::decodeEntities($separator) : '';
        $list = $list ? Html::decodeEntities($list) : '<ul>%s</ul>';
        $item = $item ? Html::decodeEntities($item) : '<li><a href="%s">%s</a>%s</li>';
        $active_item = $active_item ? Html::decodeEntities($active_item) : '<li class="nav-active"><a href="%s">%s</a>%s</li>';
        $url = App::blog()->url() . App::url()->getBase("eventhandler_list") . '/';
        if (App::frontend()->context()->exists('categories')) {
            $url .= 'category/' . App::frontend()->context()->categories->cat_url . '/';
        }

        $i = 1;
        $res = '';
        foreach ($menu as $id => $name) {
            $i++;
            $sep = $separator && $i < count($menu) + 1 ? $separator : '';

            if (isset(App::frontend()->context()->event_params['event_period']) && App::frontend()->context()->event_params['event_period'] == $id) {
                $res .= sprintf($active_item, $url . $id, $name, $sep);
            } else {
                $res .= sprintf($item, $url . $id, $name, $sep);
            }
        }

        return '<div id="eventhandler-menu-period">' . sprintf($list, $res) . '</div>';
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsMenuSortOrder(ArrayObject $attr): string
    {
        $menus = !empty($attr['menus']) ? $attr['menus'] : '';
        $separator = !empty($attr['separator']) ? $attr['separator'] : '';
        $list = !empty($attr['list']) ? $attr['list'] : '';
        $item = !empty($attr['item']) ? $attr['item'] : '';
        $active_item = !empty($attr['active_item']) ? $attr['active_item'] : '';

        return "<?php echo Dotclear\\Plugin\\eventHandler\\Template::EventsMenuSortOrdertHelper('" . addslashes((string) $menus) . "','" . addslashes((string) $separator) . "','" . addslashes((string) $list) . "','" . addslashes((string) $item) . "','" . addslashes((string) $active_item) . "'); ?>";
    }

    public static function EventsMenuSortOrdertHelper(string $menus, string $separator, string $list, string $item, string $active_item): string
    {
        $default_sort_id = [
            'title' => 'LOWER(post_title)',
            'selected' => 'post_selected',
            'author' => 'LOWER(user_id)',
            'date' => 'post_dt',
            'startdt' => 'event_startdt',
            'enddt' => 'event_enddt',
        ];
        $default_sort_text = [
            'title' => __('Title'),
            'selected' => __('Selected'),
            'author' => __('Author'),
            'date' => __('Published date'),
            'startdt' => __('Start date'),
            'enddt' => __('End date'),
        ];

        $menu = $default_sort_id;
        if (!empty($menus)) {
            $final_menu = [];
            $menus = explode(',', $menus);
            foreach ($menus as $k) {
                if (isset($default_sort_id[$k])) {
                    $final_menu[$k] = $default_sort_id[$k];
                }
            }
            if (!empty($final_menu)) {
                $menu = $final_menu;
            }
        }

        $separator = $separator ? Html::decodeEntities($separator) : '';
        $list = $list ? Html::decodeEntities($list) : '<ul>%s</ul>';
        $item = $item ? Html::decodeEntities($item) : '<li><a href="%s">%s</a>%s</li>';
        $active_item = $active_item ? Html::decodeEntities($active_item) : '<li class="nav-active"><a href="%s">%s</a>%s</li>';
        $period = !empty(App::frontend()->context()->event_params['event_period']) ? App::frontend()->context()->event_params['event_period'] : 'all';
        $url = App::blog()->url() . App::url()->getBase("eventhandler_list") . '/';
        if (App::frontend()->context()->exists('categories')) {
            $url .= 'category/' . App::frontend()->context()->categories->cat_url . '/';
        }
        $url .= $period;

        $sortstr = $sortby = $sortorder = null;
        $quoted_default_sort_id = [];
        foreach ($default_sort_id as $k => $v) {
            $quoted_default_sort_id[$k] = preg_quote($v);
        }

        if (isset(App::frontend()->context()->event_params['order'])
            && preg_match('/(' . implode('|', $quoted_default_sort_id) . ')\s(ASC|DESC)/i', (string) App::frontend()->context()->event_params['order'], $sortstr)) {
            $sortby = in_array($sortstr[1], $default_sort_id) ? $sortstr[1] : '';
            $sortorder = preg_match('#ASC#i', $sortstr[2]) ? 'asc' : 'desc';
        }

        $i = 1;
        $res = '';
        foreach ($menu as $id => $name) {
            $i++;
            $sep = $separator && $i < count($menu) + 1 ? $separator : '';

            if ($sortby == $name) {
                $ord = $sortorder == 'asc' ? 'desc' : 'asc';
                $res .= sprintf($active_item, $url . '/' . $id . '/' . $ord, $default_sort_text[$id], $sep);
            } else {
                $ord = $sortorder == 'desc' ? 'desc' : 'asc';
                $res .= sprintf($item, $url . '/' . $id . '/' . $ord, $default_sort_text[$id], $sep);
            }
        }

        return '<div id="eventhandler-menu-sortorder">' . sprintf($list, $res) . '</div>';
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsPeriod(ArrayObject $attr): string
    {
        if (!isset($attr['fulltext'])) {
            $fulltext = 0;
        } elseif (empty($attr['fulltext'])) {
            $fulltext = 1;
        } else {
            $fulltext = 2;
        }

        return "<?php echo Dotclear\\Plugin\\eventHandler\\Template::EventsPeriodHelper('" . $fulltext . "'); ?>";
    }

    public static function EventsPeriodHelper(int $fulltext): string
    {
        if ($fulltext === 2) {
            $text = [
                'all' => __('All events'),
                'ongoing' => __('Current events'),
                'outgoing' => __('Event not being'),
                'scheduled' => __('Scheduled events'),
                'started' => __('Started events'),
                'notfinished' => __('Unfinished events'),
                'finished' => __('Completed events'),
            ];
        } elseif ($fulltext === 1) {
            $text = [
                'all' => __('All'),
                'ongoing' => __('Ongoing'),
                'outgoing' => __('Outgoing'),
                'scheduled' => __('Scheduled'),
                'started' => __('Started'),
                'notfinished' => __('Not finished'),
                'finished' => __('Finished'),
            ];
        } else {
            $text = [
                'all' => 'all',
                'ongoing' => 'ongoing',
                'outgoing' => 'outgoing',
                'scheduled' => 'scheduled',
                'started' => 'started',
                'notfinished' => 'notfinished',
                'finished' => 'finished',
            ];
        }
        return isset(App::frontend()->context()->event_params['event_period']) && isset($text[App::frontend()->context()->event_params['event_period']])
        ? $text[App::frontend()->context()->event_params['event_period']] : $text['all'];
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsInterval(ArrayObject $attr): string
    {
        $format = !empty($attr['format']) ? addslashes((string) $attr['format']) : __('%m %d %Y');

        return "<?php echo Dotclear\\Plugin\\eventHandler\\Template::EventsIntervalHelper('" . $format . "'); ?>";
    }

    public static function EventsIntervalHelper(string $format): string
    {
        if (!empty(App::frontend()->context()->event_params['event_start_year'])) {
            if (!empty(App::frontend()->context()->event_params['event_start_day'])) {
                $dt = Date::str($format, mktime(0, 0, 0, App::frontend()->context()->event_params['event_start_month'], App::frontend()->context()->event_params['event_start_day'], App::frontend()->context()->event_params['event_start_year']));
                return sprintf(__('For the day of %s'), $dt);
            } elseif (!empty(App::frontend()->context()->event_params['event_start_month'])) {
                $dt = Date::str(__('%m %Y'), mktime(0, 0, 0, App::frontend()->context()->event_params['event_start_month'], 1, App::frontend()->context()->event_params['event_start_year']));
                return sprintf(__('For the month of %s'), $dt);
            } elseif (!empty(App::frontend()->context()->event_params['event_start_year'])) {
                return sprintf(__('For the year of %s'), App::frontend()->context()->event_params['event_start_year']);
            } else {
                return '';
            }
        } else {
            $start = Date::dt2str($format, App::frontend()->context()->event_params['event_startdt']);
            $end = Date::dt2str($format, App::frontend()->context()->event_params['event_enddt']);

            if (strtotime((string) App::frontend()->context()->event_params['event_startdt']) < strtotime((string) App::frontend()->context()->event_params['event_enddt'])) {
                return sprintf(__('For the period between %s and %s'), $start, $end);
            } else {
                return sprintf(__('For the period through %s and %s'), $end, $start);
            }
        }
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsIf(ArrayObject $attr, string $content): string
    {
        $if = [];

        $operator = isset($attr['operator']) ? App::frontend()->template()->getOperator($attr['operator']) : '&&';

        if (isset($attr['has_interval'])) {
            $sign = (bool) $attr['has_interval'] ? '!' : '';
            $if[] = $sign . 'empty(App::frontend()->context()->event_params["event_interval"])';
        }

        if (isset($attr['has_category'])) {
            $sign = (bool) $attr['has_category'] ? '' : '!';
            $if[] = $sign . 'App::frontend()->context()->exists("categories")';
        }

        if (isset($attr['has_period'])) {
            if ($attr['has_period']) {
                $if[] = '!empty(App::frontend()->context()->event_params["event_period"]) && App::frontend()->context()->event_params["event_period"] != "all"';
            } else {
                $if[] = 'empty(App::frontend()->context()->event_params["event_period"]) || !empty(App::frontend()->context()->event_params["event_period"]) && App::frontend()->context()->event_params["event_period"] == "all"';
            }
        }

        if (isset($attr['period'])) {
            $if[] =
                '(!empty(App::frontend()->context()->event_params["event_period"]) && App::frontend()->context()->event_params["event_period"] == "' . addslashes((string) $attr['period']) . '" ' .
                '|| empty(App::frontend()->context()->event_params["event_period"]) && ("" == "' . addslashes((string) $attr['period']) . '" || "all" == "' . addslashes((string) $attr['period']) . '")))';
        }

        if (!empty($if)) {
            return '<?php if(' . implode(' ' . $operator . ' ', $if) . ') : ?>' . $content . '<?php endif; ?>';
        } else {
            return $content;
        }
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsCount(ArrayObject $attr, string $content): string
    {
        $if = '';

        if (isset($attr['value'])) {
            $sign = (bool) $attr['value'] ? '>' : '==';
            $if = 'App::frontend()->context()->nb_posts ' . $sign . ' 0';
        }

        if ($if) {
            return '<?php if(' . $if . ') : ?>' . $content . '<?php endif; ?>';
        } else {
            return $content;
        }
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsEntries(ArrayObject $attr, string $content): string
    {
        $lastn = -1;
        if (isset($attr['lastn'])) {
            $lastn = abs((int) $attr['lastn']) + 0;
        }

        $p = 'if (!isset($_page_number)) { $_page_number = 1; }' . "\n";

        if ($lastn != 0) {
            if ($lastn > 0) {
                $p .= "\$params['limit'] = " . $lastn . ";\n";
            } else {
                $p .= "\$params['limit'] = App::frontend()->context()->nb_entry_per_page;\n";
            }

            if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
                $p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
            } else {
                $p .= "\$params['limit'] = array(0, \$params['limit']);\n";
            }
        }

        if (isset($attr['author'])) {
            $p .= "\$params['user_id'] = '" . addslashes((string) $attr['author']) . "';\n";
        }

        if (isset($attr['category'])) {
            $p .= "\$params['cat_url'] = '" . addslashes((string) $attr['category']) . "';\n";
            $p .= "context::categoryPostParam(\$params);\n";
        }

        if (isset($attr['no_category']) && $attr['no_category']) {
            $p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
            $p .= "unset(\$params['cat_url']);\n";
        }

        if (!empty($attr['type'])) {
            $p .= "\$params['post_type'] = preg_split('/\s*,\s*/','" . addslashes((string) $attr['type']) . "',-1,PREG_SPLIT_NO_EMPTY);\n";
        }

        if (!empty($attr['url'])) {
            $p .= "\$params['post_url'] = '" . addslashes((string) $attr['url']) . "';\n";
        }

        if (isset($attr['period'])) {
            $p .= "\$params['event_period'] = '" . addslashes((string) $attr['period']) . "';\n";
        }

        if (empty($attr['no_context'])) {
            $p .=
                'if (App::frontend()->context()->exists("users")) { ' .
                "\$params['user_id'] = App::frontend()->context()->users->user_id; " .
                "}\n";

            $p .=
                'if (App::frontend()->context()->exists("categories")) { ' .
                "\$params['cat_id'] = App::frontend()->context()->categories->cat_id; " .
                "}\n";

            $p .=
                'if (App::frontend()->context()->exists("archives")) { ' .
                "\$params['post_year'] = App::frontend()->context()->archives->year(); " .
                "\$params['post_month'] = App::frontend()->context()->archives->month(); ";
            if (!isset($attr['lastn'])) {
                $p .= "unset(\$params['limit']); ";
            }
            $p .=
                "}\n";

            $p .=
                'if (App::frontend()->context()->exists("langs")) { ' .
                "\$params['post_lang'] = App::frontend()->context()->langs->post_lang; " .
                "}\n";

            $p .=
                'if (isset($_search)) { ' .
                "\$params['search'] = \$_search; " .
                "}\n";

            $p .=
                'if (App::frontend()->context()->exists("event_params")) { ' .
                "\$params = array_merge(\$params,App::frontend()->context()->event_params); " .
                "}\n";
        }

        if (!empty($attr['order']) || !empty($attr['sortby'])) {
            $p .= "\$params['order'] = '" . App::frontend()->template()->getSortByStr($attr, 'eventhandler') . "';\n";
        } else {
            $order = $field = $table = '';
            if (My::settings()->public_events_list_sortby && str_contains((string) My::settings()->public_events_list_sortby, ':')) {
                [$table, $field] = explode(':', (string) My::settings()->public_events_list_sortby);
            }
            if (My::settings()->public_events_list_order) {
                $order = My::settings()->public_events_list_order;
            }
            $special_attr = new ArrayObject($special_attr = ['order' => $order, 'sortby' => $field]);
            $p .= "\$params['order'] = '" . App::frontend()->template()->getSortByStr($special_attr, $table) . "';\n";
        }

        if (isset($attr['no_content']) && $attr['no_content']) {
            $p .= "\$params['no_content'] = true;\n";
        }

        if (isset($attr['selected'])) {
            $p .= "\$params['post_selected'] = " . (int) (bool) $attr['selected'] . ";";
        }

        if (isset($attr['age'])) {
            $age = App::frontend()->template()->getAge($attr);
            $p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'" . $age . "\'';\n" : '';
        }

        return
            "<?php\n" .
            'if(!isset($eventHandler)) { $eventHandler = new Dotclear\\Plugin\\eventHandler\\EventHandler(); } ' . "\n" .
            '$params = array(); ' . "\n" .
            $p .
            'App::frontend()->context()->post_params = $params; ' . "\n" .
            'App::frontend()->context()->posts = $eventHandler->getEvents($params); unset($params); ' . "\n" .
            'App::frontend()->context()->nb_posts = count(App::frontend()->context()->posts); ' . "\n" .
            "?>\n" .
            '<?php while (App::frontend()->context()->posts->fetch()) : ?>' . $content . '<?php endwhile; ' .
            'App::frontend()->context()->posts = null; App::frontend()->context()->post_params = null; ?>';
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsPagination(ArrayObject $attr, string $content): string
    {
        $p =
            "<?php\n" .
            'if(!isset($eventHandler)) { $eventHandler = new Dotclear\\Plugin\\eventHandler\\EventHandler(); } ' . "\n" .
            '$params = App::frontend()->context()->post_params; ' . "\n" .
            'App::frontend()->context()->pagination = $eventHandler->getEvents($params,true); unset($params); ' . "\n" .
            "?>\n";

        if (isset($attr['no_context']) && $attr['no_context']) {
            return $p . $content;
        }

        return
            $p .
            '<?php if (App::frontend()->context()->pagination->f(0) > App::frontend()->context()->posts->count()) : ?>' .
            $content .
            '<?php endif; ?>';
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsEntryIf(ArrayObject $attr, string $content): string
    {
        $if = [];

        $operator = isset($attr['operator']) ? App::frontend()->template()->getOperator($attr['operator']) : '&&';

        if (isset($attr['has_category'])) {
            $sign = (bool) $attr['has_category'] ? '' : '!';
            $if[] = $sign . 'App::frontend()->context()->posts->cat_id';
        }

        if (isset($attr['has_address'])) {
            $sign = (bool) $attr['has_address'] ? '!' : '=';
            $if[] = "'' " . $sign . '= App::frontend()->context()->posts->event_address';
        }

        if (isset($attr['has_geo'])) {
            $sign = (bool) $attr['has_geo'] ? '' : '!';
            $if[] = $sign . '("" != App::frontend()->context()->posts->event_latitude && "" != App::frontend()->context()->posts->event_longitude)';
        }

        if (isset($attr['period'])) {
            $if[] = 'App::frontend()->context()->posts->getPeriod() == "' . addslashes((string) $attr['period']) . '"';
        }

        if (isset($attr['sameday'])) {
            $sign = (bool) $attr['sameday'] ? '' : '!';
            $if[] = $sign . "App::frontend()->context()->posts->isOnSameDay()";
        }

        if (isset($attr['oneday'])) {
            $sign = (bool) $attr['oneday'] ? '' : '!';
            $if[] = $sign . "App::frontend()->context()->posts->isOnOneDay()";
        }

        if (!empty($attr['orderedby'])) {
            if (str_starts_with((string) $attr['orderedby'], '!')) {
                $sign = '!';
                $orderedby = substr((string) $attr['orderedby'], 1);
            } else {
                $sign = '';
                $orderedby = $attr['orderedby'];
            }

            $default_sort = [
                'date' => 'post_dt',
                'startdt' => 'event_startdt',
                'enddt' => 'event_enddt',
            ];

            if (isset($default_sort[$orderedby])) {
                $orderedby = $default_sort[$orderedby];

                $if[] = $sign . "strstr(App::frontend()->context()->post_params['order'],'" . addslashes($orderedby) . "')";
            }
        }

        if (!empty($if)) {
            return '<?php if(' . implode(' ' . $operator . ' ', $if) . ') : ?>' . $content . '<?php endif; ?>';
        } else {
            return $content;
        }
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsDateHeader(ArrayObject $attr, string $content): string
    {
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
            "<?php " .
            'if (App::frontend()->context()->posts->firstEventOfDay("' . $type . '")) : ?>' .
            $content .
            "<?php endif; ?>";
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsDateFooter(ArrayObject $attr, string $content): string
    {
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
            "<?php " .
            'if (App::frontend()->context()->posts->lastEventOfDay("' . $type . '")) : ?>' .
            $content .
            "<?php endif; ?>";
    }

    public static function EventsEntryDate(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';
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
            return self::tplValue($a, "App::frontend()->context()->posts->getEventRFC822Date('" . $type . "')");
        } elseif ($iso8601) {
            return self::tplValue($a, "App::frontend()->context()->posts->getEventISO8601Date('" . $type . "')");
        } else {
            return self::tplValue($a, "App::frontend()->context()->posts->getEventDate('" . $format . "','" . $type . "')");
        }
    }

    public static function EventsEntryTime(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';
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

        return self::tplValue($a, "App::frontend()->context()->posts->getEventTime('" . $format . "','" . $type . "')");
    }

    public static function EventsEntryCategoryURL(mixed $a): string
    {
        return self::tplValue($a, 'App::blog()->url().App::url()->getBase("eventhandler_list")."/category/".Html::sanitizeURL(App::frontend()->context()->posts->cat_url)');
    }

    public static function EventsEntryAddress(mixed $a): string
    {
        $ics = !empty($a['ics']) ? '"LOCATION;CHARSET=UTF-8:".' : '';

        return self::tplValue($a, $ics . 'App::frontend()->context()->posts->event_address');
    }

    public static function EventsEntryLatitude(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->posts->event_latitude');
    }

    public static function EventsEntryLongitude(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->posts->event_longitude');
    }

    public static function EventsEntryZoom(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->posts->event_zoom');
    }

    public static function EventsEntryDuration(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';

        return self::tplValue($a, "Dotclear\\Plugin\\eventHandler\\EventHandler::getReadableDuration((strtotime(App::frontend()->context()->posts->event_enddt) - strtotime(App::frontend()->context()->posts->event_startdt)),'" . $format . "')");
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsEntryPeriod(ArrayObject $attr): string
    {
        $scheduled = $attr['scheduled'] ?? 'scheduled';
        if (empty($attr['strict'])) {
            $scheduled = __($scheduled);
        }

        $ongoing = $attr['ongoing'] ?? 'ongoing';
        if (empty($attr['strict'])) {
            $ongoing = __($ongoing);
        }

        $finished = $attr['finished'] ?? 'finished';
        if (empty($attr['strict'])) {
            $finished = __($finished);
        }

        $f = App::frontend()->template()->getFilters($attr);

        return
            "<?php \$time = time() + Date::getTimeOffset(App::frontend()->context()->posts->post_tz)*2;\n" .
            "if (App::frontend()->context()->posts->getEventTS('startdt') > \$time) {\n" .
            " echo " . sprintf($f, "'" . $scheduled . "'") . "; }\n" .
            "elseif (App::frontend()->context()->posts->getEventTS('startdt') < \$time && App::frontend()->context()->posts->getEventTS('enddt') > \$time) {\n" .
            " echo " . sprintf($f, "'" . $ongoing . "'") . "; }\n" .
            "elseif (App::frontend()->context()->posts->getEventTS('enddt') < \$time) {\n" .
            " echo " . sprintf($f, "'" . $finished . "'") . "; }\n" .
            "unset(\$time); ?>\n";
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsEntryMap(ArrayObject $attr): string
    {
        if (!empty($attr['map_zoom'])) {
            $map_zoom = abs((int) $attr['map_zoom']);
        } else {
            $map_zoom = '(App::frontend()->context()->posts->event_zoom)?App::frontend()->context()->posts->event_zoom:Dotclear\\Plugin\\eventHandler\\My::settings()->public_map_zoom';
        }
        $map_type = !empty($attr['map_type']) ? '"' . Html::escapeHTML($attr['map_type']) . '"' : 'Dotclear\\Plugin\\eventHandler\\My::settings()->public_map_type';
        $map_info = isset($attr['map_info']) && $attr['map_info'] == '0' ? '0' : '1';

        return '<?php echo Dotclear\\Plugin\\eventHandler\\EventHandler::getMapContent("","",' . $map_type . ',' . $map_zoom . ',' . $map_info . ',App::frontend()->context()->posts->event_latitude,App::frontend()->context()->posts->event_longitude,App::frontend()->context()->posts->getMapVEvent()); ?>';
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsOfPost(ArrayObject $attr, string $content): string
    {
        $p = '';

        $lastn = -1;
        if (isset($attr['lastn'])) {
            $lastn = abs((int) $attr['lastn']) + 0;
            if ($lastn > 0) {
                $p .= "\$params['limit'] = " . $lastn . ";\n";
            }
        }

        if (isset($attr['event'])) {
            $p .= "\$params['event_id'] = '" . abs((int) $attr['event']) . "';\n";
        }

        if (isset($attr['author'])) {
            $p .= "\$params['user_id'] = '" . addslashes((string) $attr['author']) . "';\n";
        }

        if (isset($attr['category'])) {
            $p .= "\$params['cat_url'] = '" . addslashes((string) $attr['category']) . "';\n";
            $p .= "context::categoryPostParam(\$params);\n";
        }

        if (isset($attr['no_category']) && $attr['no_category']) {
            $p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
            $p .= "unset(\$params['cat_url']);\n";
        }

        if (isset($attr['post'])) {
            $p .= "\$params['post_id'] = '" . abs((int) $attr['post']) . "';\n";
        }

        if (!empty($attr['type'])) {
            $p .= "\$params['post_type'] = preg_split('/\s*,\s*/','" . addslashes((string) $attr['type']) . "',-1,PREG_SPLIT_NO_EMPTY);\n";
        }

        if (!empty($attr['order']) || !empty($attr['sortby'])) {
            $p .= "\$params['order'] = '" . App::frontend()->template()->getSortByStr($attr, 'eventhandler') . "';\n";
        } else {
            $p .= "\$params['order'] = '" . App::frontend()->template()->getSortByStr($attr, 'post') . "';\n";
        }

        if (isset($attr['no_content']) && $attr['no_content']) {
            $p .= "\$params['no_content'] = true;\n";
        }

        if (isset($attr['selected'])) {
            $p .= "\$params['post_selected'] = " . (int) (bool) $attr['selected'] . ";";
        }

        if (isset($attr['age'])) {
            $age = App::frontend()->template()->getAge($attr);
            $p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'" . $age . "\'';\n" : '';
        }

        return
            "<?php\n" .
            'if(!isset($eventHandler)) { $eventHandler = new Dotclear\\Plugin\\eventHandler\\EventHandler(); } ' . "\n" .
            '$params = array(); ' . "\n" .
            '$public_hidden_categories = @unserialize(Dotclear\\Plugin\\eventHandler\\My::settings()->public_hidden_categories); ' .
            'if (is_array($public_hidden_categories)) { ' .
            ' foreach($public_hidden_categories as $hidden_cat) { ' .
            '  @$params[\'sql\'] .= " AND C.cat_id != \'".dcCore::app()->con->escape($hidden_cat)."\' "; ' .
            ' } ' .
            "} \n" .
            'if (App::frontend()->context()->exists("posts") && App::frontend()->context()->posts->post_id) { ' .
            '$params["post_id"] = App::frontend()->context()->posts->post_id; ' .
            "} \n" .
            $p .
            'if (!empty($params["post_id"])) { ' . "\n" .
            'App::frontend()->context()->eventsofpost_params = $params;' . "\n" .
            'App::frontend()->context()->eventsofpost = $eventHandler->getEventsByPost($params); unset($params); ' . "\n" .
            'while (App::frontend()->context()->eventsofpost->fetch()) : ?>' . $content . '<?php endwhile; ' .
            '} ' . "\n" .
            'App::frontend()->context()->eventsofpost = null; App::frontend()->context()->eventsofpost_params = null; ?>';
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsOfPostHeader(ArrayObject $attr, string $content): string
    {
        return
            "<?php if (App::frontend()->context()->eventsofpost->isStart()) : ?>" .
            $content .
            "<?php endif; ?>";
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventsOfPostFooter(ArrayObject $attr, string $content): string
    {
        return
            "<?php if (App::frontend()->context()->eventsofpost->isEnd()) : ?>" .
            $content .
            "<?php endif; ?>";
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventOfPostIf(ArrayObject $attr, string $content): string
    {
        $if = [];

        $operator = isset($attr['operator']) ? App::frontend()->template()->getOperator($attr['operator']) : '&&';

        if (isset($attr['has_category'])) {
            $sign = (bool) $attr['has_category'] ? '' : '!';
            $if[] = $sign . 'App::frontend()->context()->eventsofpost->cat_id';
        }

        if (isset($attr['has_address'])) {
            $sign = (bool) $attr['has_address'] ? '!' : '=';
            $if[] = "'' " . $sign . '= App::frontend()->context()->eventsofpost->event_address';
        }

        if (isset($attr['period'])) {
            $if[] = 'App::frontend()->context()->eventsofpost->getPeriod() == "' . addslashes((string) $attr['period']) . '"';
        }

        if (isset($attr['sameday'])) {
            $sign = (bool) $attr['sameday'] ? '' : '!';
            $if[] = $sign . "App::frontend()->context()->eventsofpost->isOnSameDay()";
        }

        if (isset($attr['oneday'])) {
            $sign = (bool) $attr['oneday'] ? '' : '!';
            $if[] = $sign . "App::frontend()->context()->eventsofpost->isOnOneDay()";
        }

        if (!empty($attr['orderedby'])) {
            if (str_starts_with((string) $attr['orderedby'], '!')) {
                $sign = '!';
                $orderedby = substr((string) $attr['orderedby'], 1);
            } else {
                $sign = '';
                $orderedby = $attr['orderedby'];
            }

            $default_sort = [
                'date' => 'post_dt',
                'startdt' => 'event_startdt',
                'enddt' => 'event_enddt',
            ];

            if (isset($default_sort[$orderedby])) {
                $orderedby = $default_sort[$orderedby];

                $if[] = $sign . "strstr(App::frontend()->context()->eventsofpost['order'],'" . addslashes($orderedby) . "')";
            }
        }

        if (!empty($if)) {
            return '<?php if(' . implode(' ' . $operator . ' ', $if) . ') : ?>' . $content . '<?php endif; ?>';
        } else {
            return $content;
        }
    }

    public static function EventOfPostTitle(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->eventsofpost->post_title');
    }

    public static function EventOfPostURL(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->eventsofpost->getURL()');
    }

    public static function EventOfPostDate(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';
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
            return self::tplValue($a, "App::frontend()->context()->eventsofpost->getEventRFC822Date('" . $type . "')");
        } elseif ($iso8601) {
            return self::tplValue($a, "App::frontend()->context()->eventsofpost->getEventISO8601Date('" . $type . "')");
        } else {
            return self::tplValue($a, "App::frontend()->context()->eventsofpost->getEventDate('" . $format . "','" . $type . "')");
        }
    }

    public static function EventOfPostTime(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';
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

        return self::tplValue($a, "App::frontend()->context()->eventsofpost->getEventTime('" . $format . "','" . $type . "')");
    }

    public static function EventOfPostAuthorCommonName(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->eventsofpost->getAuthorCN()');
    }

    public static function EventOfPostAuthorLink(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->eventsofpost->getAuthorLink()');
    }

    public static function EventOfPostCategory(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->eventsofpost->cat_title');
    }

    public static function EventOfPostCategoryURL(mixed $a): string
    {
        return self::tplValue($a, 'App::blog()->url().App::url()->getBase("eventhandler_list")."/category/".Html::sanitizeURL(App::frontend()->context()->eventsofpost->cat_url)');
    }

    public static function EventOfPostAddress(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->eventsofpost->event_address');
    }

    public static function EventOfPostDuration(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';

        return self::tplValue($a, "Dotclear\\Plugin\\eventHandler\\EventHandler::getReadableDuration((strtotime(App::frontend()->context()->eventsofpost->event_enddt) - strtotime(App::frontend()->context()->eventsofpost->event_startdt)),'" . $format . "')");
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function EventOfPostPeriod($attr): string
    {
        $scheduled = $attr['scheduled'] ?? 'scheduled';
        if (empty($attr['strict'])) {
            $scheduled = __($scheduled);
        }

        $ongoing = $attr['ongoing'] ?? 'ongoing';
        if (empty($attr['strict'])) {
            $ongoing = __($ongoing);
        }

        $finished = $attr['finished'] ?? 'finished';
        if (empty($attr['strict'])) {
            $finished = __($finished);
        }

        $f = App::frontend()->template()->getFilters($attr);

        return
            "<?php \$time = time() + Date::getTimeOffset(App::frontend()->context()->eventsofpost->post_tz)*2;\n" .
            "if (App::frontend()->context()->eventsofpost->getEventTS('startdt') > \$time) {\n" .
            " echo " . sprintf($f, "'" . $scheduled . "'") . "; }\n" .
            "elseif (App::frontend()->context()->eventsofpost->getEventTS('startdt') < \$time && App::frontend()->context()->eventsofpost->getEventTS('enddt') > \$time) {\n" .
            " echo " . sprintf($f, "'" . $ongoing . "'") . "; }\n" .
            "elseif (App::frontend()->context()->eventsofpost->getEventTS('enddt') < \$time) {\n" .
            " echo " . sprintf($f, "'" . $finished . "'") . "; }\n" .
            "unset(\$time); ?>\n";
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function PostsOfEvent(ArrayObject $attr, string $content): string
    {
        $p = '';

        $lastn = -1;
        if (isset($attr['lastn'])) {
            $lastn = abs((int) $attr['lastn']) + 0;
            if ($lastn > 0) {
                $p .= "\$params['limit'] = " . $lastn . ";\n";
            }
        }

        if (isset($attr['event'])) {
            $p .= "\$params['event_id'] = '" . abs((int) $attr['event']) . "';\n";
        }

        if (isset($attr['author'])) {
            $p .= "\$params['user_id'] = '" . addslashes((string) $attr['author']) . "';\n";
        }

        if (isset($attr['category'])) {
            $p .= "\$params['cat_url'] = '" . addslashes((string) $attr['category']) . "';\n";
            $p .= "context::categoryPostParam(\$params);\n";
        }

        if (isset($attr['no_category']) && $attr['no_category']) {
            $p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
            $p .= "unset(\$params['cat_url']);\n";
        }

        if (!empty($attr['type'])) {
            $p .= "\$params['post_type'] = preg_split('/\s*,\s*/','" . addslashes((string) $attr['type']) . "',-1,PREG_SPLIT_NO_EMPTY);\n";
        }

        $p .= "\$params['order'] = '" . App::frontend()->template()->getSortByStr($attr, 'post') . "';\n";

        if (isset($attr['no_content']) && $attr['no_content']) {
            $p .= "\$params['no_content'] = true;\n";
        }

        if (isset($attr['selected'])) {
            $p .= "\$params['post_selected'] = " . (int) (bool) $attr['selected'] . ";";
        }

        if (isset($attr['age'])) {
            $age = App::frontend()->template()->getAge($attr);
            $p .= !empty($age) ? "@\$params['sql'] .= ' AND P.post_dt > \'" . $age . "\'';\n" : '';
        }

        return
            "<?php\n" .
            "\$postsofeventHandler = new Dotclear\\Plugin\\eventHandler\\EventHandler(); \n" .
            'if (App::frontend()->context()->exists("posts") && App::frontend()->context()->posts->post_id) { ' .
            " \$params['event_id'] = App::frontend()->context()->posts->post_id; " .
            "} \n" .
            $p .
            'App::frontend()->context()->postsofevent_params = $params;' . "\n" .
            'App::frontend()->context()->postsofevent = $postsofeventHandler->getPostsByEvent($params); unset($params);' . "\n" .
            "?>\n" .
            '<?php while (App::frontend()->context()->postsofevent->fetch()) : ?>' . $content . '<?php endwhile; ' .
            'App::frontend()->context()->postsofevent = null; App::frontend()->context()->postsofevent_params = null; $postsofeventHandler = null; ?>';
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function PostsOfEventHeader(ArrayObject $attr, string $content): string
    {
        return
            "<?php if (App::frontend()->context()->postsofevent->isStart()) : ?>" .
            $content .
            "<?php endif; ?>";
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function PostsOfEventFooter(ArrayObject $attr, string $content): string
    {
        return
            "<?php if (App::frontend()->context()->postsofevent->isEnd()) : ?>" .
            $content .
            "<?php endif; ?>";
    }

    /**
    * @param ArrayObject<string, mixed> $attr
    */
    public static function PostOfEventIf(ArrayObject $attr, string $content): string
    {
        $if = [];

        $operator = isset($attr['operator']) ? App::frontend()->template()->getOperator($attr['operator']) : '&&';

        if (isset($attr['type'])) {
            $type = trim((string) $attr['type']);
            $type = !empty($type) ? $type : 'post';
            $if[] = 'App::frontend()->context()->postsofevent->post_type == "' . addslashes($type) . '"';
        }

        if (isset($attr['has_category'])) {
            $sign = (bool) $attr['has_category'] ? '' : '!';
            $if[] = $sign . 'App::frontend()->context()->postsofevent->cat_id';
        }

        if (!empty($if)) {
            return '<?php if(' . implode(' ' . $operator . ' ', $if) . ') : ?>' . $content . '<?php endif; ?>';
        } else {
            return $content;
        }
    }

    public static function PostOfEventTitle(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->postsofevent->post_title');
    }

    public static function PostOfEventURL(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->postsofevent->getURL()');
    }

    public static function PostOfEventDate(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';
        $iso8601 = !empty($a['iso8601']);
        $rfc822 = !empty($a['rfc822']);
        $type = (!empty($a['creadt']) ? 'creadt' : '');
        $type = (!empty($a['upddt']) ? 'upddt' : '');

        if ($rfc822) {
            return self::tplValue($a, "App::frontend()->context()->postsofevent->getRFC822Date('" . $type . "')");
        } elseif ($iso8601) {
            return self::tplValue($a, "App::frontend()->context()->postsofevent->getISO8601Date('" . $type . "')");
        } else {
            return self::tplValue($a, "App::frontend()->context()->postsofevent->getDate('" . $format . "','" . $type . "')");
        }
    }

    public static function PostOfEventTime(mixed $a): string
    {
        $format = !empty($a['format']) ? addslashes((string) $a['format']) : '';
        $type = (!empty($a['creadt']) ? 'creadt' : '');
        $type = (!empty($a['upddt']) ? 'upddt' : '');

        return self::tplValue($a, "App::frontend()->context()->postsofevent->getTime('" . $format . "','" . $type . "')");
    }

    public static function PostOfEventAuthorCommonName(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->postsofevent->getAuthorCN()');
    }

    public static function PostOfEventAuthorLink(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->postsofevent->getAuthorLink()');
    }

    public static function PostOfEventCategory(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->postsofevent->cat_title');
    }

    public static function PostOfEventCategoryURL(mixed $a): string
    {
        return self::tplValue($a, 'App::frontend()->context()->postsofevent->getCategoryURL()');
    }

    protected static function tplValue(mixed $a, string $v): string
    {
        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($a), $v) . '; ?>';
    }
}
