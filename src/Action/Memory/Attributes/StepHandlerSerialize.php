<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StepHandlerSerialize extends StepHandlerAttribute
{

    public function onSave($data)
    {
        return serialize($data);
    }

    public function onLoad($data)
    {
        return @unserialize($data);
    }

}