<?php

namespace Mmb\Action\Section;

use Mmb\Action\Inline\InlineAction;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class DialogStepHandler extends MenuStepHandler
{

    protected function makeInlineAction(Context $context, Update $update) : ?InlineAction
    {
        return Dialog::makeFromStep($context, $this, $update);
    }

}
