<?php

namespace Mmb\Action\Road\Station\List;

use Illuminate\Contracts\Database\Query\Builder;
use Mmb\Action\Road\Station\ListStation;
use Mmb\Support\Format\KeyFormatterBuilder;
use Mmb\Support\KeySchema\KeyboardInterface;

abstract class ListViewer
{

    public bool $needsPage = false;

    public bool $needsCursor = false;


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
     * @param KeyboardInterface $keyboard
     * @return KeyFormatterBuilder
     */
    public abstract function renderList(KeyboardInterface $keyboard) : KeyFormatterBuilder;

    /**
     * Render the paginator
     *
     * @param KeyboardInterface $keyboard
     * @return KeyFormatterBuilder
     */
    public abstract function renderPaginator(KeyboardInterface $keyboard) : KeyFormatterBuilder;


    /**
     * Render page label key text
     *
     * @return ?string
     */
    public function renderTitleKeyLabel() : ?string
    {
        return null;
    }

    /**
     * Render page label action
     *
     * @return void
     */
    public function onTitleKeyAction()
    {
    }


    /**
     * Render empty key text
     *
     * @return ?string
     */
    public function renderEmptyKeyLabel() : ?string
    {
        return null;
    }

    /**
     * Render empty key action
     *
     * @return void
     */
    public function onEmptyKeyAction()
    {
    }

}