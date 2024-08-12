<?php

namespace Mmb\Support\Caller;

use Closure;

trait HasSimpleEvents
{

    /**
     * Events
     *
     * @var array
     */
    private array $_events = [];

    /**
     * Add event listener
     *
     * @param string  $event
     * @param Closure $callback
     * @return void
     */
    public function on(string $event, Closure $callback)
    {
        @$this->_events[strtolower($event)][] = $callback;
    }

    /**
     * Fire event
     *
     * @param string $event
     * @param        ...$args
     * @return bool
     */
    public function fire(string $event, ...$args)
    {
        [$normalArgs, $dynamicArgs] = Caller::splitArguments($args);
        $dynamicArgs += $this->getEventDynamicArgs();

        $event = strtolower($event);
        foreach($this->_events[$event] ?? [] as $listener)
        {
            if(Caller::invoke($listener, $normalArgs, $dynamicArgs))
            {
                return true;
            }
        }

        if(method_exists($this, 'on' . $event))
        {
            return (bool) $this->{'on' . $event}(...$args);
        }

        return false;
    }

    /**
     * Get event dynamic arguments
     *
     * @return array
     */
    protected function getEventDynamicArgs()
    {
        return [];
    }

}
