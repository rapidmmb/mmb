<?php

namespace Mmb\Support\Caller\Attributes;

use Mmb\Context;
use ReflectionParameter;

interface CallingPassParameterInsteadContract
{

    public function getPassParameterInstead(Context $context, ReflectionParameter $parameter, $value);

}