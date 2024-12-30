<?php

namespace Mmb\Action\Road\Attributes;

use Mmb\Context;
use ReflectionProperty;

interface StationPropertyResolverAttributeContract
{

    public function getStationPropertyForStore(Context $context, ReflectionProperty $parameter, $value);

    public function getStationPropertyForLoad(Context $context, ReflectionProperty $parameter, $value);

}