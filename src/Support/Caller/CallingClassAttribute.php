<?php

namespace Mmb\Support\Caller;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class CallingClassAttribute
{

    public function authorize()
    {
    }

}
