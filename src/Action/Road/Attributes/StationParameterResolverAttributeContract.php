<?php

namespace Mmb\Action\Road\Attributes;

use ReflectionParameter;

interface StationParameterResolverAttributeContract
{

    public function getStationParameterForStore(ReflectionParameter $parameter, $value);

    public function getStationParameterForLoad(ReflectionParameter $parameter, $value);

}