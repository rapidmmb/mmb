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
class SignQuery extends SignWord
{

    protected Closure $from;

    /**
     * @param string|Closure $from
     * @return T
     */
    public function from(string|Closure $from)
    {
        if (is_string($from)) {
            $from = class_exists($from) || str_contains($from, '\\') ? function () use ($from) {
                return $from::query();
            } : function () use ($from) {
                return DB::table($from);
            };
        }

        $this->from = $from;
        return $this->sign;
    }

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
            'call' => EventCaller::CALL_LINEAR,
        ];
    }


    public function hasQuery(): bool
    {
        return isset($this->from);
    }

    public function getQuery(): Builder
    {
        $query = $this->call($this->from);

        if (!($query instanceof Builder)) {
            throw new \TypeError("Expected a builder, given " . smartTypeOf($query));
        }

        $this->call('using', $query);

        return $query;
    }

}