<?php

namespace Mmb\Action\Inline\Attributes;

use Mmb\Action\Inline\Register\InlineRegister;

interface InlineParameterAttributeContract
{

    public function registerInlineParameter(InlineRegister $register, string $name);

}
