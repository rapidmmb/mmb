<?php

namespace Mmb\Action\Update;

use Closure;

class HandlerExtends
{

    public array $firsts = [];

    /**
     * Add first event (before all events)
     *
     * @param Closure $callback
     * @return $this
     */
    public function first(Closure $callback)
    {
        $this->firsts[] = $callback;
        return $this;
    }


    public array $lasts = [];

    /**
     * Add last event (after all events)
     *
     * @param Closure $callback
     * @return $this
     */
    public function last(Closure $callback)
    {
        $this->lasts[] = $callback;
        return $this;
    }


    public array $events = [];

    /**
     * Add custom event
     *
     * @param string  $name
     * @param Closure $callback
     * @return $this
     */
    public function event(string $name, Closure $callback)
    {
        @$this->events[$name][] = $callback;
        return $this;
    }
    

    public array $handles = [];

    /**
     * Add inherited handler
     *
     * @param Closure $callback
     * @param string  $name
     * @return $this
     */
    public function handle(Closure $callback, string $name = 'default')
    {
        @$this->handles[$name][] = $callback;
        return $this;
    }

}