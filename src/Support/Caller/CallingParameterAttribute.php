<?php

namespace Mmb\Support\Caller;

use Attribute;

/**
 * @deprecated
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class CallingParameterAttribute
{

    public function authorize($value)
    {
    }

    public function cast($value, string $class)
    {
        return $value;
    }

    public function castMultiple($value, array $classes)
    {
        if(count($classes) == 1)
        {
            return $this->cast($value, $classes[0]);
        }

        return $value;
    }

}
