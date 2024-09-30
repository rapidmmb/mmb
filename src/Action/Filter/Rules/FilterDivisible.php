<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class FilterDivisible extends FilterRule
{

    public function __construct(
        public $number,
        public $error,
    )
    {
    }

    public function pass(Update $update, &$value)
    {
        if(!is_numeric($value))
        {
            $this->fail(__('mmb::filter.numeric'));
        }

        $number = value($this->number);
        if ($value % $number != 0)
        {
            $this->fail(value($this->error ?? __('mmb::filter.divisible', ['number' => $number])));
        }
    }

}
