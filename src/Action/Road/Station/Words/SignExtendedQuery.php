<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Illuminate\Support\Facades\DB;
use Mmb\Support\Caller\EventCaller;
use RectorPrefix202408\Illuminate\Contracts\Database\Query\Builder;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignExtendedQuery extends SignWord
{

    /**
     * @param Closure $callback
     * @return T
     */
    public function using(Closure $callback)
    {
        $this->listen('using', $callback);
        return $this->sign;
    }

    protected function getEventOptionsOnUsing()
    {
        return [
            EventCaller::CALL_LINEAR,
        ];
    }


    public function applyQuery(Builder $query): void
    {
        $this->call('using', $query);
    }

}