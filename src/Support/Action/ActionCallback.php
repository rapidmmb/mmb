<?php

namespace Mmb\Support\Action;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

class ActionCallback implements Arrayable
{

    public array $withArgs = [];

    public function __construct(
        public       $action,
        public array $defaultArgs = [],
    )
    {
    }

    public function addArgs(array $args)
    {
        array_push($this->defaultArgs, ...$args);
        return $this;
    }

    /**
     * Check action is string
     *
     * @return bool
     */
    public function isNamed(): bool
    {
        return is_string($this->action);
    }

    /**
     * Checks action is storable
     *
     * @return bool
     */
    public function isStorable(): bool
    {
        return is_string($this->action) || is_array($this->action);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->defaultArgs ? [$this->action, $this->defaultArgs] : [$this->action];
    }

    /**
     * Make from array
     *
     * @param array $array
     * @return ?static
     */
    public static function fromArray(array $array): ?static
    {
        if (count($array) == 0) {
            return null;
        }

        if (!is_string($array[0])) {
            return null;
        }

        return count($array) == 1 ? new static($array[0]) : new static($array[0], $array[1]);
    }

    /**
     * Set 'with' arguments
     *
     * @param array $with
     * @return $this
     */
    public function with(array $with)
    {
        $this->withArgs = $with;
        return $this;
    }

    /**
     * Invoke method
     *
     * @param        $object
     * @param Context $context
     * @param array $args
     * @param array $dynamicArgs
     * @return mixed
     */
    public function invoke($object, Context $context, array $args, array $dynamicArgs)
    {
        $args = [...$this->defaultArgs, ...$this->withArgs, ...$args];

        // String action
        if (is_string($this->action)) {
            if (str_contains($this->action, '@')) {
                [$class, $method] = explode($this->action, '@', 2);
                return $class::makeByContext($context)->invokeDynamic($method, $args, $dynamicArgs);
            }

            return $object->invokeDynamic($this->action, $args, $dynamicArgs);
        }

        // Array action
        if (is_array($this->action)) {
            [$class, $method] = $this->action;
            return $class::makeByContext($context)->invokeDynamic($method, $args, $dynamicArgs);
        }

        // Closure action
        if ($this->action instanceof Closure) {
            return Caller::invoke($context, $this->action, $args, $dynamicArgs);
        }

        throw new \TypeError(sprintf("Invalid action type, given [%s]", smartTypeOf($this->action)));
    }

}
