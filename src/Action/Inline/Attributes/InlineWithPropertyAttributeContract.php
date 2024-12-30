<?php

namespace Mmb\Action\Inline\Attributes;

use Mmb\Action\Inline\InlineAction;
use Mmb\Context;

interface InlineWithPropertyAttributeContract
{

    public function getInlineWithPropertyForStore(Context $context, InlineAction $inline, string $name, $value);

    public function getInlineWithPropertyForLoad(Context $context, InlineAction $inline, string $name, $value);

}
