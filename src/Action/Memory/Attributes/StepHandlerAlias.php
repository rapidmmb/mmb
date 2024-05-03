<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StepHandlerAlias extends StepHandlerAttribute
{

    public function __construct(
        public string $alias,
    )
    {
    }

    public function getAlias()
    {
        return $this->alias;
    }

}