<?php

namespace Mmb\Support\Caller;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CallingMethodAttribute
{

    public function authorize()
    {
    }

}
