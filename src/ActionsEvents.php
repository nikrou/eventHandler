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

use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Html;
use Dotclear\App;
use Exception;

class ActionsEvents extends ActionsPosts
{
    final public const BIND_EVENT_ACTION = 'eventhandler_bind_event';
    final public const UNBIND_POST_ACTION = 'eventhandler_unbind_post';

    protected bool $use_render = true;

    /**
     * @param array<string, mixed> $redirect_args
     */
    public function __construct(?string $uri, array $redirect_args = [])
    {
        parent::__construct($uri, $redirect_args);

        $this->redirect_fields = ['part'];
        $this->caller_title = __('Events');
    }

    public function beginPage(string $breadcrumb = '', string $head = ''): void
    {
        Page::openModule(__('Events'), Page::jsLoad('js/_posts_actions.js') . $head);
        echo $breadcrumb, '<p><a class="back" href="' . $this->getRedirection(true) . '">' . __('Back to events list') . '</a></p>';
    }

    public function endPage(): void
    {
        Page::closeModule();
    }

    public function error(Exception $e): void
    {
        App::error()->add($e->getMessage());
        $this->beginPage(
            Page::breadcrumb(
                [
                    Html::escapeHTML(App::blog()->name()) => '',
                    $this->getCallerTitle() => $this->getRedirection(true),
                    __('Events actions') => '',
                ]
            )
        );
        $this->endPage();
    }

    protected function loadDefaults(): void
    {
        // We could have added a behavior here, but we want default action to be setup first
        ActionsEventsDefault::adminEventsActionsPage($this);
        // --BEHAVIOR-- adminPostsActions -- Actions
        App::behavior()->callBehavior('adminActionsEvents', $this);
    }

    public function process()
    {
        $this->from['post_type'] = EventHandler::POST_TYPE;

        return parent::process();
    }
}
