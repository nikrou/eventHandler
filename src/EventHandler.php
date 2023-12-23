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

use Dotclear\App;
use Dotclear\Core\Auth;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;
use Dotclear\Database\Statement\JoinStatement;
use Dotclear\Database\Statement\SelectStatement;
use Exception;

class EventHandler
{
    final public const POST_TYPE = 'eventhandler';

    protected string $type;
    protected string $table;

    public function __construct(string $type = self::POST_TYPE)
    {
        $this->type = (string) $type;
        $this->table = App::con()->prefix() . 'eventhandler';
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public static function cleanedParams($params): array
    {
        if (!isset($params['columns'])) {
            $params['columns'] = [];
        }

        if (!isset($params['from'])) {
            $params['from'] = '';
        }

        if (!isset($params['sql'])) {
            $params['sql'] = '';
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getEvents(array $params = [], bool $count_only = false): MetaRecord
    {
        $params = self::cleanedParams($params);

        if (isset($params['event_id'])) {
            $params['post_id'] = $params['event_id'];
            unset($params['event_id']);
        }

        if (isset($params['event_type'])) {
            $params['post_type'] = $params['event_type'];
            unset($params['event_type']);
        }

        if (!isset($params['post_type'])) {
            $params['post_type'] = $this->type;
        }

        if (!isset($params['columns'])) {
            $params['columns'] = [];
        }

        $col = (array) $params['columns'];
        $col[] = 'event_startdt';
        $col[] = 'event_enddt';
        $col[] = 'event_address';
        $col[] = 'event_latitude';
        $col[] = 'event_longitude';
        $col[] = 'event_zoom';
        $params['columns'] = $col;

        $sql = new SelectStatement();
        $sql->join(
            (new JoinStatement())
                   ->type('INNER')
                   ->from($this->table . ' EH')
                   ->on('EH.post_id = P.post_id')
                   ->statement()
        );

        if (!empty($params['event_period']) && $params['event_period'] != 'all') {
            $op = match ($params['event_period']) {
                'ongoing' => ['<', '>', 'AND'],
                'outgoing' => ['>', '<', 'OR'],
                'notstarted', 'scheduled' => ['>', '!'],
                'started' => ['<', '!'],
                'notfinished' => ['!', '>'],
                'finished' => ['!', '<'],
                default => ['=', '=', 'AND'],
            };
            $now = date('Y-m-d H:i:s');

            // sqlite does not understand the TIMESTAMP function but understands the 'Y-m-d H:i:s' format just fine
            $timestamp = App::con()->driver() == 'sqlite' ? "" : " TIMESTAMP";

            $params['sql'] .= $op[0] != '!' && $op[1] != '!' ? 'AND (' : 'AND ';

            if (!empty($params['event_startdt']) && $op[0] != '!') {
                $params['sql'] .= "EH.event_startdt " . $op[0] . $timestamp . " '" . App::con()->escape($params['event_startdt']) . "'";
            } elseif (empty($params['event_startdt']) && $op[0] != '!') {
                $params['sql'] .= "EH.event_startdt " . $op[0] . $timestamp . " '" . $now . "'";
            }

            $params['sql'] .= $op[0] != '!' && $op[1] != '!' ? ' ' . $op[2] . ' ' : '';

            if (!empty($params['event_enddt']) && $op[1] != '!') {
                $params['sql'] .= "EH.event_enddt " . $op[1] . $timestamp . " '" . App::con()->escape($params['event_enddt']) . "'";
            } elseif (empty($params['event_enddt']) && $op[1] != '!') {
                $params['sql'] .= "EH.event_enddt " . $op[1] . $timestamp . " '" . $now . "'";
            }

            $params['sql'] .= $op[0] != '!' && $op[1] != '!' ? ') ' : ' ';
        }

        if (!empty($params['event_start_year'])) {
            $params['sql'] .= 'AND ' . App::con()->dateFormat('EH.event_startdt', '%Y') . ' = ' .
            "'" . sprintf('%04d', $params['event_start_year']) . "' ";
        }
        if (!empty($params['event_start_month'])) {
            $params['sql'] .= 'AND ' . App::con()->dateFormat('EH.event_startdt', '%m') . ' = ' .
            "'" . sprintf('%02d', $params['event_start_month']) . "' ";
        }
        if (!empty($params['event_start_day'])) {
            $params['sql'] .= 'AND ' . App::con()->dateFormat('EH.event_startdt', '%d') . ' = ' .
            "'" . sprintf('%02d', $params['event_start_day']) . "' ";
        }

        if (!empty($params['event_end_year'])) {
            $params['sql'] .= 'AND ' . App::con()->dateFormat('EH.event_enddt', '%Y') . ' = ' .
            "'" . sprintf('%04d', $params['event_end_year']) . "' ";
        }
        if (!empty($params['event_end_month'])) {
            $params['sql'] .= 'AND ' . App::con()->dateFormat('EH.event_enddt', '%m') . ' = ' .
            "'" . sprintf('%02d', $params['event_end_month']) . "' ";
        }
        if (!empty($params['event_endt_day'])) {
            $params['sql'] .= 'AND ' . App::con()->dateFormat('EH.event_enddt', '%d') . ' = ' .
            "'" . sprintf('%02d', $params['event_end_day']) . "' ";
        }

        // Localization
        if (!empty($params['event_address'])) {
            $params['sql'] .= "AND EH.event_address = '" . App::con()->escape($params['event_address']) . "' ";
        }

        // --BEHAVIOR-- coreEventHandlerBeforeGetEvents
        App::behavior()->callBehavior('coreEventHandlerBeforeGetEvents', $this, ['params' => &$params]);

        $rs = App::blog()->getPosts($params, $count_only, $sql);

        if (empty($params['sql_only'])) {
            $rs->eventHandler = $this;
            $rs->extend(RsExtension::class);
        }

        // --BEHAVIOR-- coreEventHandlerGetEvents
        App::behavior()->callBehavior('coreEventHandlerGetEvents', $rs);

        return $rs;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getEventsByPost(array $params = [], bool $count_only = false): ?MetaRecord
    {
        $params = self::cleanedParams($params);

        if (!isset($params['post_id'])) {
            return null;
        }
        if (!isset($params['event_type'])) {
            $params['event_type'] = $this->type;
        }

        $params['from'] .= ', ' . App::con()->prefix() . 'meta EM ';

        if (str_contains((string) App::con()->driver(), 'mysql')) {
            $params['sql'] .= 'AND EM.meta_id = CAST(P.post_id as char) ';
        } else {
            $params['sql'] .= 'AND CAST(EM.meta_id as int) = CAST(P.post_id as int) ';
        }

        $params['sql'] .= "AND EM.post_id = '" . App::con()->escape($params['post_id']) . "' ";
        $params['sql'] .= "AND EM.meta_type = '" . App::con()->escape($params['event_type']) . "' ";

        unset($params['post_id']);

        return $this->getEvents($params, $count_only);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getPostsByEvent(array $params = [], bool $count_only = false): ?MetaRecord
    {
        $params = self::cleanedParams($params);

        if (!isset($params['event_id'])) {
            return null;
        }
        if (!isset($params['event_type'])) {
            $params['event_type'] = $this->type;
        }
        if (!isset($params['post_type'])) {
            $params['post_type'] = '';
        }
        $params['from'] .= ', ' . App::con()->prefix() . 'meta EM ';
        $params['sql'] .= 'AND EM.post_id = P.post_id ';
        $params['sql'] .= "AND EM.meta_id = '" . App::con()->escape($params['event_id']) . "' ";
        $params['sql'] .= "AND EM.meta_type = '" . App::con()->escape($params['event_type']) . "' ";

        unset($params['event_id'],$params['event_type']);

        return App::blog()->getPosts($params, $count_only);
    }

    public function addEvent(Cursor $cur_post, Cursor $cur_event): int
    {
        if (!App::auth()->check(App::auth()->makePermissions([
            Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_USAGE,
        ]), App::blog()->id())) {
            throw new Exception(__('You are not allowed to create an event'));
        }

        try {
            $this->getEventCursor(null, $cur_post, $cur_event);

            // --BEHAVIOR-- coreEventHandlerBeforeEventAdd
            App::behavior()->callBehavior("coreEventHandlerBeforeEventAdd", $this, $cur_post, $cur_event);

            $cur_event->post_id = App::blog()->addPost($cur_post);
            $cur_event->insert();
        } catch (Exception $e) {
            App::con()->rollback();
            throw $e;
        }

        // --BEHAVIOR-- coreEventHandlerAfterEventAdd
        App::behavior()->callBehavior("coreEventHandlerAfterEventAdd", $this, $cur_event->post_id, $cur_post, $cur_event);
        return $cur_event->post_id;
    }

    public function updEvent(int $post_id, Cursor $cur_post, Cursor $cur_event): void
    {
        if (!App::auth()->check(App::auth()->makePermissions([
            Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_USAGE,
        ]), App::blog()->id())) {
            throw new Exception(__('You are not allowed to update events'));
        }

        $post_id = (int) $post_id;

        if (empty($post_id)) {
            throw new Exception(__('No such event ID'));
        }

        App::con()->begin();
        try {
            $this->getEventCursor($post_id, $cur_post, $cur_event);

            // --BEHAVIOR-- coreEventHandlerBeforeEventUpdate
            App::behavior()->callBehavior('coreEventHandlerBeforeEventUpdate', $this, $post_id, $cur_post, $cur_event);

            App::blog()->updPost($post_id, $cur_post);
            $cur_event->post_id = $post_id;
            $cur_event->update("WHERE post_id = '" . $post_id . "' ");
        } catch (Exception $e) {
            App::con()->rollback();
            throw $e;
        }
        App::con()->commit();
    }

    public function delEvent(int $post_id): void
    {
        if (App::auth()->check(App::auth()->makePermissions([
            Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_DELETE,
        ]), App::blog()->id())) {
            throw new Exception(__('You are not allowed to delete events'));
        }

        $post_id = (int) $post_id;

        if (empty($post_id)) {
            throw new Exception(__('No such event ID'));
        }

        // --BEHAVIOR-- coreEventHandlerEventDelete
        App::behavior()->callBehavior("coreEventHandlerEventDelete", $this, $post_id);

        App::blog()->delPost($post_id);
        App::con()->execute('DELETE FROM ' . $this->table . ' ' . 'WHERE post_id = ' . $post_id . ' ');
    }

    private function getEventCursor(?int $post_id, Cursor $cur_post, Cursor $cur_event): void
    {
        // Required a start date
        if ($cur_event->event_startdt == '') {
            throw new Exception(__('No event start date'));
        }
        // Required an end date
        if ($cur_event->event_enddt == '') {
            throw new Exception(__('No event end date'));
        }
        // Compare dates
        if (strtotime((string) $cur_event->event_enddt) < strtotime((string) $cur_event->event_startdt)) {
            throw new Exception(__('Start date greater than end date'));
        }
        // Full coordiantes or nothing
        if (($cur_event->event_latitude != '' && $cur_event->event_longitude == '')
           || ($cur_event->event_latitude == '' && $cur_event->event_longitude != '')) {
            throw new Exception(__('Not full coordinate'));
        }
        // Coordinates format
        if ($cur_event->event_latitude != '') {
            if (!preg_match('/^(-|)[0-9.]+$/', (string) $cur_event->event_latitude)) {
                throw new Exception(__('Wrong format of coordinate'));
            }
        }
        // Coordinates format
        if ($cur_event->event_longitude != '') {
            if (!preg_match('/^(-|)[0-9.]+$/', (string) $cur_event->event_longitude)) {
                throw new Exception(__('Wrong format of coordinate'));
            }
        }
        // Set post type
        if (!$post_id && $cur_post->post_type == '') {
            $cur_post->post_type = $this->type;
        }

        // Force no comment
        $cur_post->unsetField('post_open_comment');
        $cur_post->post_open_comment = 0;

        // Force no trackback
        $cur_post->unsetField('post_open_tb');
        $cur_post->post_open_tb = 0;

        // unset post_id
        $cur_event->unsetField('post_id');

        // --BEHAVIOR-- coreEventHandlerGetEventCursor
        App::behavior()->callBehavior('coreEventHandlerGetEventCursor', $this, $post_id, $cur_post, $cur_event);
    }

    // Get human readable duration from integer
    public static function getReadableDuration(int $timestamp, string $format = 'second'): string
    {
        $time = '';
        //$sec = $min = $hou = $day = 0;

        //todo format
        $sec = $timestamp % 60;
        $timestamp -= $sec;
        $timestamp /= 60;
        $min = $timestamp % 60;
        $timestamp -= $min;
        $timestamp /= 60;
        $hou = $timestamp % 24;
        $timestamp -= $hou;
        $timestamp /= 24;
        $day = $timestamp;

        if ($day > 1) {
            $time .= sprintf(__('%s days'), $day) . ' ';
        }
        if ($day == 1) {
            $time .= __('one day') . ' ';
        }
        if ($hou > 1) {
            $time .= sprintf(__('%s hours'), $hou) . ' ';
        }
        if ($hou == 1) {
            $time .= __('one hour') . ' ';
        }
        if ($min > 1) {
            $time .= sprintf(__('%s minutes'), $min) . ' ';
        }
        if ($min == 1) {
            $time .= __('one minute') . ' ';
        }
        if (!$day && !$min && !$hou) {
            $time .= __('instantaneous');
        }

        return $time;
    }

    // Build HTML content for events maps
    // markers are in RsExtension.php
    public static function getMapContent(string $width, string $height, string $type, int $zoom, int $info, float $lat, float $lng, string $markers): string
    {
        $style = '';
        if ($width || $height) {
            $style = 'style="';
            if ($width) {
                $style .= 'width:' . $width . ';';
            }
            if ($height) {
                $style .= 'height:' . $height . ';';
            }
            $style .= '" ';
        }

        $res = '<div style="display:none;" class="event-map">' . "\n";
        $res .= '<div ' . $style . 'class="event-map-place"><p>' . __("Please wait, try to create map...") . '</p></div>' . "\n";
        $res .= '<div style="display:none;" class="event-map-info">' . "\n";
        $res .= '<p class="event-map-info-zoom">' . $zoom . '</p>' . "\n";
        $res .= '<p class="event-map-info-type">' . $type . '</p>' . "\n";
        $res .= '<p class="event-map-info-info">' . $info . '</p>' . "\n";
        $res .= '<p class="event-map-info-lat">' . $lat . '</p>' . "\n";
        $res .= '<p class="event-map-info-lng">' . $lng . '</p>' . "\n";

        if (My::settings()->map_tile_layer) {
            $res .= '<p class="event-map-info-tile-layer">' . My::settings()->map_tile_layer . '</p>' . "\n";
        }
        $res .= '</div>' . "\n";
        $res .= $markers . "\n";
        $res .= '</div>';

        return $res;
    }
}
