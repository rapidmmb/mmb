<?php

namespace Mmb\Action\Inline\Attributes;

use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Context;

interface InlineParameterAttributeContract
{

    public function registerInlineParameter(Context $context, InlineRegister $register, string $name);

}
