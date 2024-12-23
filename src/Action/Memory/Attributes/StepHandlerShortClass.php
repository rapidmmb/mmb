<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;
use Illuminate\Support\Str;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StepHandlerShortClass extends StepHandlerAttribute
{

    public function __construct(
        public string $prefix,
        public string $mark = '@',
    )
    {
    }

    public function onLoad($data)
    {
        if (@$data[0] == $this->mark) {
            return $this->prefix . substr($data, 1);
        }

        return $data;
    }

    public function onSave($data)
    {
        if (Str::startsWith($data, $this->prefix)) {
            return $this->mark . substr($data, strlen($this->prefix));
        }

        return $data;
    }

}