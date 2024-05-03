<?php

namespace Mmb\Action\Memory\Attributes;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StepHandlerInstead extends StepHandlerAttribute
{

    public function __construct(
        public array $instead,
        public $saveDefault = null,
        public $loadDefault = null,
    )
    {
    }

    public function onLoad($data)
    {
        if(($key = array_search($data, $this->instead)) !== false)
        {
            return $key;
        }

        return $this->loadDefault;
    }

    public function onSave($data)
    {
        if(array_key_exists($data, $this->instead))
        {
            return $this->instead[$data];
        }

        return $this->saveDefault;
    }

}