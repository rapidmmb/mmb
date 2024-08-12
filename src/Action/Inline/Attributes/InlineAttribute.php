<?php

namespace Mmb\Action\Inline\Attributes;

use Attribute;
use Mmb\Action\Inline\Register\InlineRegister;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
abstract class InlineAttribute implements InlineAttributeContract, InlineParameterAttributeContract
{

    public function registerInline(InlineRegister $register)
    {
        throw new \Exception(sprintf("Attribute [%s] could not use in inline methods", static::class));
    }

    public function registerInlineParameter(InlineRegister $register, string $name)
    {
        throw new \Exception(sprintf("Attribute [%s] could not use in inline parameter", static::class));
    }

}
