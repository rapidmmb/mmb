<?php

namespace Mmb\Action\Road\Attributes;

use Mmb\Context;
use ReflectionParameter;

interface StationParameterResolverAttributeContract
{

    public function getStationParameterForStore(Context $context, ReflectionParameter $parameter, $value);

    public function getStationParameterForLoad(Context $context, ReflectionParameter $parameter, $value);

}