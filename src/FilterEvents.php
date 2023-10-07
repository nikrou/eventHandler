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

use dcCore;
use dcUtils;
use Dotclear\Core\Backend\Combos;
use Dotclear\Core\Backend\Filter\Filter;
use Dotclear\Core\Backend\Filter\Filters;
use Dotclear\Core\Backend\Filter\FiltersLibrary;
use Dotclear\Helper\Html\Html;

class FilterEvents extends Filters
{
    public function __construct(string $type = 'posts', private string $post_type = 'eventhandler')
    {
        parent::__construct($type);
        $this->add((new Filter('post_type', $post_type))->param('post_type'));

        $filters = new \ArrayObject([
            FiltersLibrary::getPageFilter(),
            $this->getPostUserFilter(),
            $this->getPostCategoriesFilter(),
            $this->getPeriodFilter(),
            $this->getPostStatusFilter(),
            $this->getPostMonthFilter(),
            $this->getPostLangFilter(),
            $this->getSortByFilter(),
        ]);

        $filters = $filters->getArrayCopy();

        $this->add($filters);
    }

    public function getPostUserFilter(): ?Filter
    {
        $users = null;

        try {
            $users = dcCore::app()->blog->getPostsUsers($this->post_type);
            if ($users->isEmpty()) {
                return null;
            }
        } catch (\Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return null;
        }

        $combo = Combos::getUsersCombo($users);
        dcUtils::lexicalKeySort($combo, dcUtils::ADMIN_LOCALE);

        return (new Filter('user_id'))
            ->param()
            ->title(__('Author:'))
            ->options(array_merge(
                ['-' => ''],
                $combo
            ))
            ->prime(true);
    }

    public function getPostCategoriesFilter(): ?Filter
    {
        $categories = null;

        try {
            $categories = dcCore::app()->blog->getCategories(['post_type' => $this->post_type]);
            if ($categories->isEmpty()) {
                return null;
            }
        } catch (\Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return null;
        }

        $combo = [
            '-' => '',
            __('(No cat)') => 'NULL',
        ];
        while ($categories->fetch()) {
            $combo[
                str_repeat('&nbsp;', ($categories->level - 1) * 4) .
                Html::escapeHTML($categories->cat_title) . ' (' . $categories->nb_post . ')'
            ] = $categories->cat_id;
        }

        return (new Filter('cat_id'))
            ->param()
            ->title(__('Category:'))
            ->options($combo)
            ->prime(true);
    }

    public function getPostStatusFilter(): Filter
    {
        return (new Filter('status'))
            ->param('post_status')
            ->title(__('Status:'))
            ->options(array_merge(
                ['-' => ''],
                Combos::getPostStatusesCombo()
            ))
            ->prime(true);
    }

    public function getPostMonthFilter(): ?Filter
    {
        $dates = null;

        try {
            $dates = dcCore::app()->blog->getDates([
                'type' => 'month',
                'post_type' => $this->post_type,
            ]);
            if ($dates->isEmpty()) {
                return null;
            }
        } catch (\Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return null;
        }

        return (new Filter('month'))
            ->param('post_month', fn ($f) => substr($f[0], 4, 2))
            ->param('post_year', fn ($f) => substr($f[0], 0, 4))
            ->title(__('Month:'))
            ->options(array_merge(
                ['-' => ''],
                Combos::getDatesCombo($dates)
            ));
    }

    public function getPostLangFilter(): ?Filter
    {
        $langs = null;

        try {
            $langs = dcCore::app()->blog->getLangs(['post_type' => $this->post_type]);
            if ($langs->isEmpty()) {
                return null;
            }
        } catch (\Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return null;
        }

        return (new Filter('lang'))
            ->param('post_lang')
            ->title(__('Lang:'))
            ->options(array_merge(
                ['-' => ''],
                Combos::getLangsCombo($langs, false)
            ));
    }

    public function getPeriodFilter(): ?Filter
    {
        return (new Filter('period'))
        ->param('event_period')
        ->title(__('Period:'))
        ->options([
            '-' => '',
            __('Not started') => 'scheduled',
            __('Started') => 'started',
            __('Finished') => 'finished',
            __('Not finished') => 'notfinished',
            __('Ongoing') => 'ongoing',
            __('Outgoing') => 'outgoing'
        ]);
    }

    public function getSelectedFilter(): ?Filter
    {
        return (new Filter('selected'))
        ->param('post_period')
        ->title(__('Selected:'))
        ->options([
            '-' => '',
            __('selected') => '1',
            __('not selected') => '0'
        ]);
    }

    public function getSortByFilter(): ?Filter
    {
        return (new Filter('sortby'))
        ->param('order')
        ->title(__('Sort by:'))
        ->options([
            __('Date') => 'post_dt',
            __('Title') => 'post_title',
            __('Category') => 'cat_title',
            __('Author') => 'user_id',
            __('Status') => 'post_status',
            __('Selected') => 'post_selected',
            __('Start date') => 'event_startdt',
            __('End date') => 'event_enddt',
            __('Localization') => 'event_address',
        ]);
    }
}
