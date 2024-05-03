<?php

namespace Mmb\Action\Form;

use Exception;

class ForceActionFormException extends Exception
{

    public function __construct(
        public bool $store = false,
    )
    {
        parent::__construct("This is builtin exception for [".Form::class."]");
    }

}
