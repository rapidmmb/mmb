<?php

namespace Mmb\Action\Road\Station\List;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Mmb\Action\Road\Station\ListStation;
use Mmb\Action\Section\Menu;
use Mmb\Action\Section\MenuKey;
use Mmb\Support\Format\KeyFormatter;
use Mmb\Support\Format\KeyFormatterBuilder;

abstract class ListViewer
{

    public ListStation $station;

    public bool $needsPage = false;

    public bool $needsCursor = false;


    /**
     * Use a station
     *
     * @param ListStation $station
     * @return $this
     */
    public function use(ListStation $station)
    {
        $this->station = $station;
        return $this;
    }

    /**
     * Boot the pagination with query and return that the list is not empty.
     *
     * @param Builder $query
     * @return bool
     */
    public abstract function bootPagination(Builder $query) : bool;

    /**
     * Render the list
     *
     * @param Menu $menu
     * @return KeyFormatterBuilder
     */
    public abstract function renderList(Menu $menu) : KeyFormatterBuilder;

    /**
     * Render the paginator
     *
     * @param Menu $menu
     * @return KeyFormatterBuilder
     */
    public abstract function renderPaginator(Menu $menu) : KeyFormatterBuilder;

    /**
     * Render when the list is empty
     *
     * @param Menu $menu
     * @return KeyFormatterBuilder
     */
    public function renderEmpty(Menu $menu) : KeyFormatterBuilder
    {
        return KeyFormatter::for(
            [
                [$menu->key($this->station->)]
            ]
        );
    }

    /**
     * Render page label key
     *
     * @param Menu $menu
     * @return MenuKey|null
     */
    public function renderTitleKey(Menu $menu) : ?MenuKey
    {
        $text = $this->renderTitleKeyLabel();

        if (empty($text))
        {
            return null;
        }

        $action = $this->renderTitleKeyAction();

        return $menu->key($text, $action);
    }

    /**
     * Render page label key text
     *
     * @return string|null
     */
    public function renderTitleKeyLabel() : ?string
    {
        return null;
    }

    /**
     * Render page label action
     *
     * @return Closure
     */
    public function renderTitleKeyAction() : Closure
    {
        return fn () => $this->station->fireSign('pageLabelAction');
    }

}