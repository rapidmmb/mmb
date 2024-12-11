<?php

namespace Mmb\Support\Caller;

use Closure;

/**
 * @deprecated 
 */
trait HasSimpleEvents
{

    /**
     * Events
     *
     * @var array
     */
    protected array $_events = [];

    /**
     * Add event listener
     *
     * @param string  $event
     * @param Closure $callback
     * @return $this
     */
    public function on(string $event, Closure $callback)
    {
        @$this->_events[strtolower($event)][] = $callback;
        return $this;
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
        foreach ($this->_events[$event] ?? [] as $listener)
        {
            if (Caller::invoke($this->context, $listener, $normalArgs, $dynamicArgs))
            {
                return true;
            }
        }

        if (method_exists($this, 'on' . $event))
        {
            return (bool) $this->{'on' . $event}(...$args);
        }

        return false;
    }

    /**
     * Check the event is already defined using `on` method
     *
     * @param string $event
     * @return bool
     */
    public function isDefinedEvent(string $event)
    {
        return array_key_exists(strtolower($event), $this->_events);
    }

    /**
     * Get event dynamic arguments
     *
     * @return array
     */
    public function getEventDynamicArgs()
    {
        return [];
    }

}
