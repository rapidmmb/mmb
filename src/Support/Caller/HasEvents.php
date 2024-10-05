<?php

namespace Mmb\Support\Caller;

use Closure;

trait HasEvents
{

    /**
     * Listened on events
     *
     * @var array
     */
    protected array $_listened_events = [];

    /**
     * Listen on an event
     *
     * @param string  $on
     * @param Closure $callback
     * @return $this
     */
    public function listen(string $on, Closure $callback)
    {
        @$this->_listened_events[$on][] = $callback;

        return $this;
    }

    /**
     * Remove a listener
     *
     * @param string  $on
     * @param Closure $callback
     * @return $this
     */
    public function removeListener(string $on, Closure $callback)
    {
        $this->_listened_events[$on] =
            array_filter($this->_listened_events[$on] ?? [], fn($value) => $value !== $callback);

        return $this;
    }

    /**
     * Fire an event
     *
     * @param string|Closure|array $event
     * @param                      ...$args
     * @return mixed
     */
    public function fire(string|Closure|array $event, ...$args)
    {
        return $this->fireWithOptions(
            is_string($event) ? $this->getEventOptions($event) : [],
            $event,
            ...$args
        );
    }

    /**
     * Fire an event with options
     *
     * @param array                $options
     * @param string|Closure|array $event
     * @param                      ...$args
     * @return mixed
     */
    public function fireWithOptions(array $options, string|Closure|array $event, ...$args)
    {
        if (is_array($event))
        {
            // Empty array -> Nothing
            if (!$event) return null;

            // Array<String> -> Call multiple events
            if (is_string(head($event)))
            {
                $result = [];
                foreach ($event as $ev)
                {
                    $result[] = $this->fireWithOptions($options, $ev, ...$args);
                }

                return $result;
            }
        }

        if ($event instanceof Closure)
        {
            $event = [$event];
        }

        [$normalArgs, $dynamicArgs] = Caller::splitArguments($args);

        return EventCaller::fire(
            $options,
            is_array($event) ? $event : $this->_listened_events[$event] ?? [],
            $normalArgs,
            $dynamicArgs,
            is_string($event) && method_exists($this, $fn = 'on' . $event) ? $this->$fn(...) : null,
        );
    }


    /**
     * Get event options
     *
     * @param string $event
     * @return array
     */
    protected function getEventOptions(string $event) : array
    {
        return $this->getEventDefaultOptions($event);
    }

    /**
     * Get default event options
     *
     * @param string $event
     * @return array
     */
    private function getEventDefaultOptions(string $event): array
    {
        if (method_exists($this, $fn = 'getEventOptionsOn' . $event))
        {
            return (array) $this->$fn();
        }

        return [];
    }

    /**
     * Get event dynamic args
     *
     * @param string $event
     * @return array
     */
    protected function getEventDynamicArgs(string $event) : array
    {
        return $this->getEventDefaultDynamicArgs($event);
    }

    /**
     * Get event default dynamic args
     *
     * @param string $event
     * @return array
     */
    private function getEventDefaultDynamicArgs(string $event): array
    {
        if (method_exists($this, $fn = 'getEventDynamicArgsOn' . $event))
        {
            return (array) $this->$fn();
        }

        return [];
    }

}