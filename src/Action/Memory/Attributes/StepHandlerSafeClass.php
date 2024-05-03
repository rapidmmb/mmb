<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StepHandlerSafeClass extends StepHandlerAttribute
{

    public function __construct(
        public $default = null,
    )
    {
    }

    public function onLoad($data)
    {
        if(!class_exists($data))
        {
            return $this->default;
        }

        return $data;
    }

}