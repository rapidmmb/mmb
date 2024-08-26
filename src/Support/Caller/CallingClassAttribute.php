<?php

namespace Mmb\Support\Caller;

use Attribute;

/**
 * @deprecated
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CallingClassAttribute
{

    public function authorize()
    {
    }

}
