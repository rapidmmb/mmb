<?php

namespace Mmb\Action\Inline\Attributes;

use Attribute;
use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Context;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
abstract class InlineAttribute implements InlineAttributeContract, InlineParameterAttributeContract
{

    public function registerInline(Context $context, InlineRegister $register)
    {
        throw new \Exception(sprintf("Attribute [%s] could not use in inline methods", static::class));
    }

    public function registerInlineParameter(Context $context, InlineRegister $register, string $name)
    {
        throw new \Exception(sprintf("Attribute [%s] could not use in inline parameter", static::class));
    }

}
