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

use Dotclear\Core\Backend\Listing\ListingPosts;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;
use Dotclear\Helper\Network\Http;
use Dotclear\App;
use Dotclear\Core\Auth;
use Dotclear\Core\Blog;
use Exception;
use formSelectOption;

class ManageEvent extends Process
{
    public static function init(): bool
    {
        if (My::checkContext(My::MANAGE)) {
            self::status(empty($_REQUEST['part']) || $_REQUEST['part'] === 'event');
        }

        return self::status();
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $eventHandler = new EventHandler();

        // Post part
        $post_id = '';
        $cat_id = '';
        $post_dt = '';
        $post_format = App::auth()->getOption('post_format');
        $post_editor = App::auth()->getOption('editor');
        $post_password = '';
        $post_url = '';
        $post_lang = App::auth()->getInfo('user_lang');
        $post_title = '';
        $post_excerpt = '';
        $post_excerpt_xhtml = '';
        $post_content = '';
        $post_content_xhtml = '';
        $post_notes = '';
        $post_status = App::auth()->getInfo('user_post_status');
        $post_selected = false;

        // This 3 options are disabled
        $post_open_comment = 0;
        $post_open_tb = 0;
        $post_media = [];

        // Event part
        $event_startdt = '';
        $event_enddt = '';
        $event_address = '';
        $event_latitude = '';
        $event_longitude = '';
        $event_zoom = 0;

        $page_title = __('New event');

        $can_edit_post = App::auth()->check(App::auth()->makePermissions([Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_USAGE]), App::blog()->id());
        $can_publish = App::auth()->check(App::auth()->makePermissions([Auth::PERMISSION_CONTENT_ADMIN, Auth::PERMISSION_PUBLISH]), App::blog()->id());
        $can_delete = false;

        $post_headlink = '<link rel="%s" title="%s" href="' . App::backend()->getPageURL() . '&part=event&amp;id=%s" />';
        $post_link = '<a href="' . App::backend()->getPageURL() . '&part=event&amp;id=%s" title="%s">%s</a>';
        $next_link = $prev_link = $next_headlink = $prev_headlink = null;

        // settings
        $events_default_zoom = My::settings()->public_map_zoom;
        $map_provider = My::settings()->map_provider ?: 'googlemaps';
        $map_api_key = My::settings()->map_api_key;

        $preview_url = '';

        // If user can't publish
        if (!$can_publish) {
            $post_status = Blog::POST_PENDING;
        }

        // Getting categories
        $categories_combo = ['&nbsp;' => ''];
        try {
            $categories = App::blog()->getCategories(['post_type' => 'post']);
            while ($categories->fetch()) {
                $categories_combo[] = new formSelectOption(
                    str_repeat('&nbsp;&nbsp;', $categories->level - 1) . '&bull; ' . Html::escapeHTML($categories->cat_title),
                    $categories->cat_id
                );
            }
        } catch (Exception $e) {
        }

        // Status combo
        foreach (App::blog()->getAllPostStatus() as $k => $v) {
            $status_combo[$v] = (string) $k;
        }

        // Formaters combo
        $core_formaters = App::formater()->getFormaters();
        $available_formats = ['' => ''];
        foreach ($core_formaters as $editor => $formats) {
            foreach ($formats as $format) {
                $available_formats[$format] = $format;
            }
        }

        // Languages combo
        $rs = App::blog()->getLangs(['order' => 'asc']);
        $all_langs = L10n::getISOcodes(false, true);
        $lang_combo = ['' => '', __('Most used') => [], __('Available') => L10n::getISOcodes(true, true)];
        while ($rs->fetch()) {
            if (isset($all_langs[$rs->post_lang])) {
                $lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
                unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
            } else {
                $lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
            }
        }
        unset($all_langs, $rs);

        // Change a post to an event
        $change = false;
        if (!empty($_REQUEST['from_id'])) {
            $post = App::blog()->getPosts(['post_id' => (int) $_REQUEST['from_id'], 'post_type' => '']);

            if ($post->isEmpty()) {
                App::error()->add(__('This entry does not exist.'));
                unset($post);
            } else {
                $change = true;
            }
        }

        // Get entry informations
        if (!empty($_REQUEST['id'])) {
            $post = $eventHandler->getEvents(['post_id' => (int) $_REQUEST['id']]);

            if ($post->isEmpty()) {
                App::error()->add(__('This event does not exist.'));
                unset($post);
            }
        }

        if (isset($post)) {
            $post_id = $post->post_id;
            $cat_id = $post->cat_id;
            $post_dt = date('Y-m-d H:i', strtotime((string) $post->post_dt));
            $post_format = $post->post_format;
            $post_password = $post->post_password;
            $post_url = $post->post_url;
            $post_lang = $post->post_lang;
            $post_title = $post->post_title;
            $post_excerpt = $post->post_excerpt;
            $post_excerpt_xhtml = $post->post_excerpt_xhtml;
            $post_content = $post->post_content;
            $post_content_xhtml = $post->post_content_xhtml;
            $post_notes = $post->post_notes;
            $post_status = $post->post_status;
            $post_selected = (bool) $post->post_selected;
            $post_open_comment = false;
            $post_open_tb = false;

            if ($change) {
                $post_type = 'eventhandler';
                $page_title = __('Change entry into event');
            } else {
                $event_startdt = date('Y-m-d\TH:i', strtotime((string) $post->event_startdt));
                $event_enddt = date('Y-m-d\TH:i', strtotime((string) $post->event_enddt));
                $event_address = $post->event_address;
                $event_latitude = $post->event_latitude;
                $event_longitude = $post->event_longitude;
                $event_zoom = $post->event_zoom;

                $page_title = __('Edit event');

                $next_rs = App::blog()->getNextPost($post, 1);
                $prev_rs = App::blog()->getNextPost($post, -1);

                if ($next_rs !== null) {
                    $next_link = sprintf(
                        $post_link,
                        $next_rs->post_id,
                        Html::escapeHTML($next_rs->post_title),
                        __('next event') . '&nbsp;&#187;'
                    );
                    $next_headlink = sprintf(
                        $post_headlink,
                        'next',
                        Html::escapeHTML($next_rs->post_title),
                        $next_rs->post_id
                    );
                }

                if ($prev_rs !== null) {
                    $prev_link = sprintf(
                        $post_link,
                        $prev_rs->post_id,
                        Html::escapeHTML($prev_rs->post_title),
                        '&#171;&nbsp;' . __('previous event')
                    );
                    $prev_headlink = sprintf(
                        $post_headlink,
                        'previous',
                        Html::escapeHTML($prev_rs->post_title),
                        $prev_rs->post_id
                    );
                }
            }

            $can_edit_post = $post->isEditable();
            $can_delete = $post->isDeletable();

            $post_media = [];
        }

        // Format excerpt and content
        if (!empty($_POST) && $can_edit_post) {
            $post_format = $_POST['post_format'];
            $post_excerpt = $_POST['post_excerpt'];
            $post_content = $_POST['post_content'];

            $post_title = $_POST['post_title'];

            $cat_id = (int) $_POST['cat_id'];

            if (isset($_POST['post_status'])) {
                $post_status = (int) $_POST['post_status'];
            }

            if (empty($_POST['post_dt'])) {
                $post_dt = '';
            } else {
                $post_dt = strtotime((string) $_POST['post_dt']);
                $post_dt = date('Y-m-d\TH:i', $post_dt);
            }

            $post_open_comment = false;
            $post_open_tb = false;
            $post_selected = !empty($_POST['post_selected']);
            $post_lang = $_POST['post_lang'];
            $post_password = !empty($_POST['post_password']) ? $_POST['post_password'] : null;

            $post_notes = $_POST['post_notes'];

            if (isset($_POST['post_url'])) {
                $post_url = $_POST['post_url'];
            }

            App::blog()->setPostContent(
                $post_id,
                $post_format,
                $post_lang,
                $post_excerpt,
                $post_excerpt_xhtml,
                $post_content,
                $post_content_xhtml
            );

            if (empty($_POST['event_startdt'])) {
                $event_startdt = '';
            } else {
                $event_startdt = strtotime((string) $_POST['event_startdt']);
                $event_startdt = date('Y-m-d H:i', $event_startdt);
            }

            if (empty($_POST['event_enddt'])) {
                $event_enddt = '';
            } else {
                $event_enddt = strtotime((string) $_POST['event_enddt']);
                $event_enddt = date('Y-m-d H:i', $event_enddt);
            }
            $event_address = $_POST['event_address'];
            $event_latitude = $_POST['event_latitude'];
            $event_longitude = $_POST['event_longitude'];
            $event_zoom = (int) $_POST['event_zoom'];
        }

        // Create or update post
        if (!empty($_POST) && !empty($_POST['save']) && $can_edit_post) {
            $cur_post = App::con()->openCursor(App::con()->prefix() . 'post');

            $cur_post->cat_id = ($cat_id ?: null);
            $cur_post->post_dt = $post_dt ? date('Y-m-d H:i:00', strtotime($post_dt)) : '';
            $cur_post->post_format = $post_format;
            $cur_post->post_password = $post_password;
            $cur_post->post_lang = $post_lang;
            $cur_post->post_title = $post_title;
            $cur_post->post_excerpt = $post_excerpt;
            $cur_post->post_excerpt_xhtml = $post_excerpt_xhtml;
            $cur_post->post_content = $post_content;
            $cur_post->post_content_xhtml = $post_content_xhtml;
            $cur_post->post_notes = $post_notes;
            $cur_post->post_status = $post_status;
            $cur_post->post_selected = (int) $post_selected;
            $cur_post->post_open_comment = 0;
            $cur_post->post_open_tb = 0;

            if (isset($_POST['post_url'])) {
                $cur_post->post_url = $post_url;
            }

            $cur_event = App::con()->openCursor(App::con()->prefix() . 'eventhandler');

            $cur_event->event_startdt = $event_startdt ? date('Y-m-d H:i:00', strtotime($event_startdt)) : '';
            $cur_event->event_enddt = $event_enddt ? date('Y-m-d H:i:00', strtotime($event_enddt)) : '';
            $cur_event->event_address = $event_address;
            $cur_event->event_latitude = $event_latitude;
            $cur_event->event_longitude = $event_longitude;
            $cur_event->event_zoom = $event_zoom;

            // Update post
            if ($post_id) {
                try {
                    // --BEHAVIOR-- adminBeforeEventHandlerUpdate
                    App::behavior()->callBehavior('adminBeforeEventHandlerUpdate', $cur_post, $cur_event, $post_id);

                    $eventHandler->updEvent((int) $post_id, $cur_post, $cur_event);

                    // --BEHAVIOR-- adminAfterEventHandlerUpdate
                    App::behavior()->callBehavior('adminAfterEventHandlerUpdate', $cur_post, $cur_event, $post_id);

                    Notices::addSuccessNotice(__('Event has been updated.'));
                    App::backend()->url()->redirect('admin.plugin.eventHandler', ['part' => 'event', 'id' => $post_id]);
                } catch (Exception $e) {
                    App::error()->add($e->getMessage());
                }
            } else {
                $cur_post->user_id = App::auth()->userID();

                try {
                    // --BEHAVIOR-- adminBeforeEventHandlerCreate
                    App::behavior()->callBehavior('adminBeforeEventHandlerCreate', $cur_post, $cur_event);

                    $return_id = $eventHandler->addEvent($cur_post, $cur_event);

                    // --BEHAVIOR-- adminAfterEventHandlerCreate
                    App::behavior()->callBehavior('adminAfterEventHandlerCreate', $cur_post, $cur_event, $return_id);

                    Notices::addSuccessNotice(__('Event has been created.'));
                    App::backend()->url()->redirect('admin.plugin.eventHandler', ['part' => 'event', 'id' => $return_id]);
                } catch (Exception $e) {
                    App::error()->add($e->getMessage());
                }
            }
        }

        if ($post_id && isset($post)) {
            $preview_url = App::blog()->url() .
                App::url()->getURLFor(
                    'eventhandler_preview',
                    App::auth()->userID() . '/' .
                    Http::browserUID(DC_MASTER_KEY . App::auth()->userID() . App::auth()->getInfo('user_pwd')) .
                    '/' . $post->post_url
                );
        }

        if (!empty($_POST['delete']) && $can_delete) {
            try {
                // --BEHAVIOR-- adminBeforeEventHandlerDelete
                App::behavior()->callBehavior('adminBeforeEventHandlerDelete', $post_id);

                $eventHandler->delEvent($post_id);
                App::backend()->url()->redirect('admin.plugin.eventHandler', ['part' => 'events']);
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        // Get bind entries
        if ($post_id && !$change) {
            $page = !empty($_GET['page']) ? (int) $_GET['page'] : 1;
            $nb_per_page = 30;

            if (!empty($_GET['nb']) && (int) $_GET['nb'] > 0) {
                $nb_per_page = (int) $_GET['nb'];
            }

            $params = [];
            $params['event_id'] = $post_id;
            $params['limit'] = [(($page - 1) * $nb_per_page), $nb_per_page];
            $params['no_content'] = true;

            try {
                $posts = $eventHandler->getPostsByEvent($params);
                $counter = $eventHandler->getPostsByEvent($params, true);
                $posts_list = new ListingPosts($posts, $counter->f(0));
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        /* DISPLAY
        -------------------------------------------------------- */
        $default_tab = 'edit-entry';
        if (!$can_edit_post) {
            $default_tab = '';
        }

        if (!empty($_GET['tab'])) {
            $default_tab = $_GET['tab'];
        }

        $admin_post_behavior = '';
        if ($post_editor && !empty($post_editor[$post_format])) {
            $admin_post_behavior = App::behavior()->callBehavior(
                'adminPostEditor',
                $post_editor[$post_format],
                'event',
                ['#post_content', '#post_excerpt']
            );
        }

        // XHTML conversion
        if (!empty($_GET['xconv'])) {
            $post_excerpt = $post_excerpt_xhtml;
            $post_content = $post_content_xhtml;
            $post_format = 'xhtml';

            $message = __('Don\'t forget to validate your XHTML conversion by saving your post.');
        }

        // --BEHAVIOR-- adminEventHandlerBeforeEventTpl - to use a custom tpl e.g.
        App::behavior()->callBehavior('adminEventHandlerCustomEventTpl');

        include(__DIR__ . '/../tpl/edit_event.tpl');
    }
}
