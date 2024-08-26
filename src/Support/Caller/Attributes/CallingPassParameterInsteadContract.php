<?php

namespace Mmb\Support\Caller\Attributes;

use ReflectionParameter;

interface CallingPassParameterInsteadContract
{

    public function getPassParameterInstead(ReflectionParameter $parameter, $value);

}