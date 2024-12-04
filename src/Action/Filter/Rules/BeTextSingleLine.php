<?php

namespace Mmb\Action\Filter\Rules;

use Mmb\Context;
use Mmb\Core\Updates\Update;

class BeTextSingleLine extends BeText
{

    public function __construct(
        public $singleLineError = null,
        $textError = null,
        $messageError = null
    )
    {
        parent::__construct($textError, $messageError);
    }

    public function pass(Context $context, Update $update, &$value)
    {
        parent::pass($context, $update, $value);

        if(str_contains($value, "\n"))
        {
            $this->fail(value($this->singleLineError ?? __('mmb::filter.text-single-line')));
        }
    }

}
