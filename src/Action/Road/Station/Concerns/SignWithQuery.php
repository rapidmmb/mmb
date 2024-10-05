<?php

namespace Mmb\Action\Road\Station\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Mmb\Action\Road\Station;
use Mmb\Support\Caller\EventCaller;
use Closure;

trait SignWithQuery
{

    /**
     * Get the query
     *
     * @param Station $station
     * @return Builder
     */
    public function getQuery(Station $station) : Builder
    {
        return $station->fireSign('queryUsing', $station->fireSign('createQuery'));
    }

    /**
     * Create first time query using
     *
     * @param string|Closure(): Builder $callback
     * @return $this
     */
    public function createQuery(string|Closure $callback)
    {
        if (is_string($callback))
        {
            $callback = static fn () => $callback::query();
        }

        return $this->listen('createQuery', $callback);
    }

    protected function getEventOptionsOnCreateQuery()
    {
        return [
            'call' => EventCaller::CALL_UNTIL_TRUE,
            'sort' => EventCaller::SORT_REVERSE,
        ];
    }

    protected function onCreateQuery(Station $station)
    {
        if ($globalQuery = $station->road->getQuery())
        {
            return $globalQuery;
        }

        throw new \InvalidArgumentException("No query or model passed");
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
