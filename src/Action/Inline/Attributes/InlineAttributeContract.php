<?php

namespace Mmb\Action\Inline\Attributes;

use Mmb\Action\Inline\Register\InlineRegister;
use Mmb\Context;

interface InlineAttributeContract
{

    public function registerInline(Context $context, InlineRegister $register);

}
