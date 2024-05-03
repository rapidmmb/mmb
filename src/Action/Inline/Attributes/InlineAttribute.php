<?php

namespace Mmb\Action\Inline\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
abstract class InlineAttribute
{

    public function before($inline)
    {
    }

    public function after($inline)
    {
    }

    public function modifyArgs($inline, array &$args)
    {
    }

}
