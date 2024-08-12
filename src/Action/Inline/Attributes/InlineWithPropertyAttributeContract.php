<?php

namespace Mmb\Action\Inline\Attributes;

use Mmb\Action\Inline\InlineAction;

interface InlineWithPropertyAttributeContract
{

    public function getInlineWithPropertyForStore(InlineAction $inline, string $name, $value);

    public function getInlineWithPropertyForLoad(InlineAction $inline, string $name, $value);

}
