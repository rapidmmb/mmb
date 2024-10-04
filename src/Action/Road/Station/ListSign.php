<?php

namespace Mmb\Action\Road\Station;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Mmb\Action\Road\Sign;
use Mmb\Action\Road\Station;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\EventCaller;

class ListSign extends Sign
{

    public function createStation(Update $update) : Station
    {
        return new ListStation($this->road, $this, $update);
    }


    public Closure $createQueryUsing;

    /**
     * Create first time query using
     *
     * @param string|Closure(): Builder $callback
     * @return $this
     */
    public function queryUsing(string|Closure $callback)
    {
        if (is_string($callback))
        {
            $this->createQueryUsing = fn() => $callback::query();
        }
        else
        {
            $this->createQueryUsing = $callback;
        }

        return $this;
    }

    /**
     * Build the query using callback
     *
     * @param Closure(Builder): Builder $callback
     * @return $this
     */
    public function query(Closure $callback)
    {
        return $this->listen('query', $callback);
    }

    protected function getEventOptionsOnQuery()
    {
        return [
            'call' => EventCaller::CALL_BUILDER,
        ];
    }

}