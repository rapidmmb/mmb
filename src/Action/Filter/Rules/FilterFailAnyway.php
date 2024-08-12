<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class FilterFailAnyway extends FilterRule
{

    public function __construct(
        public $message,
    )
    {
    }

    public function pass(Update $update, &$value)
    {
        $this->fail(value($this->message));
    }

}
