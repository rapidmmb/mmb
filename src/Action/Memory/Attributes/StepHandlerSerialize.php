<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;
use Mmb\Support\Serialize\ShortSerialize;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StepHandlerSerialize extends StepHandlerAttribute
{

    public function onSave($data)
    {
        return ShortSerialize::serialize($data);
    }

    public function onLoad($data)
    {
        return ShortSerialize::tryUnserialize($data);
    }

}
