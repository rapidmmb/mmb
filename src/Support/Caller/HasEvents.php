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
     * @param string $event
     * @param        ...$args
     * @return mixed
     */
    public function fire(string $event, ...$args)
    {
        return $this->fireWithOptions($this->getEventOptions($event), $event, ...$args);
    }

    /**
     * Fire an event with options
     *
     * @param array  $options
     * @param string $event
     * @param        ...$args
     * @return mixed
     */
    public function fireWithOptions(array $options, string $event, ...$args)
    {
        [$normalArgs, $dynamicArgs] = Caller::splitArguments($args);

        return EventCaller::fire(
            $options,
            $this->_listened_events[$event] ?? [],
            $normalArgs,
            $dynamicArgs,
            method_exists($this, $fn = 'on' . $event) ? $this->$fn(...) : null,
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