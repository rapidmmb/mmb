<?php

namespace Mmb\Action\Section;

use Mmb\Action\Inline\InlineAction;
use Mmb\Core\Updates\Update;

class DialogStepHandler extends MenuStepHandler
{

    protected function makeInlineAction(Update $update) : ?InlineAction
    {
        return Dialog::makeFromStep($this, $update);
    }

}
