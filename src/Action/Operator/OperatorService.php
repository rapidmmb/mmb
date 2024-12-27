<?php

namespace Mmb\Action\Operator;

use Mmb\Context;

class OperatorService
{

    public function __construct(
        public Context $context,
    )
    {
    }

    public static function makeByContext(Context $context)
    {
        return new static($context);
    }

    public static function make(Context $context): StatefulOrderProxy|static
    {
        return (new static($context))->stateful();
    }


    /**
     * @return StatefulOrderProxy<static>|$this
     */
    public function stateful(): StatefulOrderProxy|static
    {
        return new StatefulOrderProxy($this);
    }

    /**
     * @template U
     * @param class-string<U> $class
     * @return EventTriggerProxy<U>|U
     */
    protected function event(string $class)
    {
        return new EventTriggerProxy($class, $this->context);
    }

    protected function fail(mixed $tag, ?string $message = null)
    {
        throw new OperatorFailed($tag, $message);
    }

}