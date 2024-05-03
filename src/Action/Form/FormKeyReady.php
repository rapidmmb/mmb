<?php

namespace Mmb\Action\Form;

use Closure;

class FormKeyReady
{

    public function __construct(
        public array|Closure $key,
    )
    {
    }

}
