<?php

namespace Mmb\Action\Road\Station\Concerns;

use Illuminate\Contracts\Database\Query\Builder;
use Mmb\Action\Road\Station;
use Mmb\Support\Caller\EventCaller;
use Closure;

trait SignWithQueryFilters
{

    /**
     * Filter and return the query
     *
     * @param Builder $query
     * @return Builder
     */
    public function getFilteredQuery(Station $station, Builder $query)
    {
        return $this->fireBy($station, 'queryUsing', $query);
    }

    /**
     * Build the query using callback
     *
     * @param Closure(Builder): Builder $callback
     * @return $this
     */
    public function queryUsing(Closure $callback)
    {
        return $this->listen('queryUsing', $callback);
    }

    protected function getEventOptionsOnQueryUsing()
    {
        return [
            'call' => EventCaller::CALL_BUILDER,
        ];
    }

}