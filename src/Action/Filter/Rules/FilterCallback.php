<?php

namespace Mmb\Action\Filter\Rules;

use Closure;
use Mmb\Action\Filter\FilterRule;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class FilterCallback extends FilterRule
{

    public function __construct(
        public Closure $callback,
    )
    {
    }

    public function pass(Context $context, Update $update, &$value)
    {
        ($this->callback)($update, $value, $this);
    }

}
