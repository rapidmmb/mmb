<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class FilterNotForwarded extends FilterRule
{

    public function __construct(
        public $message = null,
        public $messageError = null,
    )
    {
    }

    public function pass(Context $context, Update $update, &$value)
    {
        if ($update->message?->isForwarded)
        {
            $this->fail(value($this->message ?? __('mmb::filter.not-forward')));
        }
    }

}
