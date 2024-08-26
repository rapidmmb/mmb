<?php

namespace Mmb\Support\Caller;

use Attribute;

/**
 * @deprecated
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CallingMethodAttribute
{

    public function authorize()
    {
    }

}
