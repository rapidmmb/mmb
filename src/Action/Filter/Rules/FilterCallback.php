<?php

namespace Mmb\Action\Filter\Rules;

use Closure;
use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class FilterCallback extends FilterRule
{

    public function __construct(
        public Closure $callback,
    )
    {
    }

    public function pass(Update $update, &$value)
    {
        $fn = $this->callback;
        $fn($update, $value, $this);
    }

}
