<?php

namespace Mmb\Support\Cast;

use Attribute;
use Mmb\Support\Caller\ParameterPassingInstead;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CastEnum extends ParameterPassingInstead
{

    public function getInsteadOf($value)
    {
        if($value instanceof \BackedEnum)
        {
            return $value->value;
        }
        elseif($value instanceof \UnitEnum)
        {
            return $value->name;
        }

        return $value;
    }

    public function cast($value, string $class)
    {
        if(!($value instanceof $class))
        {
            if(is_a($class, \BackedEnum::class, true))
            {
                return $class::tryFrom($value);
            }
            elseif(is_a($class, \UnitEnum::class, true))
            {
                foreach($class::cases() as $case)
                {
                    if($case->name == $value)
                    {
                        return $case;
                    }
                }
                return null;
            }
        }

        return $value;
    }

}
