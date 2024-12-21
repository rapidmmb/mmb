<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignAction extends SignWord
{

    protected ?Closure $callback = null;

    /**
     * @param Closure $callback
     * @return T
     */
    public function set(Closure $callback)
    {
        $this->callback = $callback;
        return $this->sign;
    }

    /**
     * @param Closure $callback
     * @return T
     */
    public function add(Closure $callback)
    {
        $this->listen('callback', $callback);
        return $this->sign;
    }

    public function callAction(...$args): mixed
    {
        $this->call('callback', ...$args);

        if ($this->callback) {
            return $this->call($this->callback, ...$args);
        }

        return null;
    }

}