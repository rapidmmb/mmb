<?php

namespace Mmb\Support\Action;

use Mmb\Action\Action;

/**
 * @deprecated 
 */
class EventProxy
{

    public function __construct(
        public Action $instance,
    )
    {
    }

    /**
     * Make proxy instance
     *
     * @template T
     * @param T $instance
     * @return static|T
     */
    public static function make(Action $instance)
    {
        return new static($instance);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->instance->invoke($name, ...$arguments);
    }

    public function __get(string $name)
    {
        return $this->instance->$name;
    }

    public function __set(string $name, $value) : void
    {
        $this->instance->$name = $value;
    }

    public function __invoke(...$args)
    {
        return $this->instance->invoke('__invoke', ...$args);
    }

    public function __toString() : string
    {
        return "$this->instance";
    }

}
