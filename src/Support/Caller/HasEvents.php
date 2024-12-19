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
            array_filter($this->_listened_events[$on] ?? [], fn ($value) => $value !== $callback);

        return $this;
    }

    /**
     * Remove all listeners
     *
     * @param string|null $on
     * @return $this
     */
    public function removeListeners(?string $on = null)
    {
        if (isset($on))
        {
            unset($this->_listened_events[$on]);
        }
        else
        {
            $this->_listened_events = [];
        }

        return $this;
    }

    /**
     * Fire an event
     *
     * @param string|Closure|array $__event
     * @param                      ...$args
     * @return mixed
     */
    public function fire(string|Closure|array $__event, ...$args)
    {
        return $this->fireWithOptions(
            is_string($__event) ? $this->getEventOptions($__event) : [],
            $__event,
            ...$args
        );
    }

    /**
     * Fire an event with options
     *
     * @param array                $__options
     * @param string|Closure|array $__event
     * @param                      ...$args
     * @return mixed
     */
    public function fireWithOptions(array $__options, string|Closure|array $__event, ...$args)
    {
        if (is_array($__event))
        {
            // Empty array -> Nothing
            if (!$__event)
                return null;

            // Array<String> -> Call multiple events
            if (is_string(head($__event)))
            {
                $result = [];
                foreach ($__event as $ev)
                {
                    $result[] = $this->fireWithOptions($__options, $ev, ...$args);
                }

                return $result;
            }
        }

        if ($__event instanceof Closure)
        {
            $__event = [$__event];
        }

        [$normalArgs, $dynamicArgs] = Caller::splitArguments($args);

        return EventCaller::fire(
            $this->context,
            $__options,
            is_array($__event) ? $__event : $this->_listened_events[$__event] ?? [],
            $normalArgs,
            $dynamicArgs + (is_string($__event) ? $this->getEventDynamicArgs($__event) : $this->getEventDynamicArgs('*')),
            is_string($__event) && method_exists($this, $fn = 'on' . $__event) ? $this->$fn(...) : null,
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
    private function getEventDefaultOptions(string $event) : array
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
    private function getEventDefaultDynamicArgs(string $event) : array
    {
        if ($event != '*' && method_exists($this, $fn = 'getEventDynamicArgsOn' . $event))
        {
            return (array) $this->$fn();
        }

        return [];
    }

}