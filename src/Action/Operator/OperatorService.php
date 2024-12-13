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

    public static function make(Context $context)
    {
        return new static($context);
    }


    /**
     * @return StatefulOrderProxy<static>|$this
     */
    public function stateful(): StatefulOrderProxy|static
    {
        return new StatefulOrderProxy($this);
    }

    public function pov(): OperatorPov
    {
        return new OperatorPov($this);
    }

    // todo

}