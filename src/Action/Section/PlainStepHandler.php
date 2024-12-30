<?php

namespace Mmb\Action\Section;

use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class PlainStepHandler extends InlineStepHandler
{

    protected function makeInlineAction(Context $context, Update $update): ?InlineAction
    {
        return Plain::makeFromStep($context, $this, $update);
    }

}