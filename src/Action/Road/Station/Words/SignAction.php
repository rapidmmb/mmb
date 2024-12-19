<?php

namespace Mmb\Action\Road\Station\Words;

use Closure;
use Mmb\Action\Road\Station;

/**
 * @template T
 * @extends SignWord<T>
 */
class SignAction extends SignWord
{

    protected ?Closure $callback = null;

    public function set(Closure $callback)
    {
        $this->callback = $callback;
        return $this->sign;
    }

    public function add(Closure $callback)
    {
        $this->listen('callback', $callback);
        return $this->sign;
    }

    public function callAction(Station $station, ...$args): mixed
    {
        $this->fire('callback', ...$args, station: $station);

        if ($this->callback) {
            return $this->fire($this->callback, ...$args, station: $station);
        }

        return null;
    }

}