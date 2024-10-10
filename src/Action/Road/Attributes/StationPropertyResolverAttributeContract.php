<?php

namespace Mmb\Action\Road\Attributes;

use ReflectionProperty;

interface StationPropertyResolverAttributeContract
{

    public function getStationPropertyForStore(ReflectionProperty $parameter, $value);

    public function getStationPropertyForLoad(ReflectionProperty $parameter, $value);

}