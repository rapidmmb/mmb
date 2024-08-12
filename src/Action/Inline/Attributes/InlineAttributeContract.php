<?php

namespace Mmb\Action\Inline\Attributes;

use Mmb\Action\Inline\Register\InlineRegister;

interface InlineAttributeContract
{

    public function registerInline(InlineRegister $register);

}
