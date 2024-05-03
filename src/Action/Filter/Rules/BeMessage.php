<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Action\Filter\FilterRule;
use Mmb\Core\Updates\Update;

class BeMessage extends FilterRule
{

    public function __construct(
        public $messageError = null,
    )
    {
    }

    public function pass(Update $update, &$value)
    {
        if(!$update->message)
        {
            $this->fail(value($this->messageError ?? __('mmb.filter.message')));
        }

        $value = $update->message;
    }

}
