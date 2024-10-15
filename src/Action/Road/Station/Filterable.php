<?php

namespace Mmb\Action\Road\Station;

use Illuminate\Contracts\Database\Query\Builder;
use Mmb\Action\Section\Menu;

interface Filterable
{

    public function initializeFirst(ListStation $station);

    public function applyOnWhere(ListStation $station, Builder $query, $value);

    public function applyOnQuery(ListStation $station, Builder $query, $value);

    public function fireFilter(ListStation $station);

    public function initializeMenu(ListStation $station, Menu $menu);

    public function getCurrentLabel(ListStation $station) : string;

}