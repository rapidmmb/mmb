<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StepHandlerArray extends StepHandlerAttribute
{

    public function onSave($data)
    {
        return collect($data)->toArray();
    }

    public function onLoad($data)
    {
        return (array) $data;
    }

}