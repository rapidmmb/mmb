<?php

namespace Mmb\Action\Section;

use Mmb\Action\Action;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\Attributes\StepHandlerShortClass as ShortClass;
use Mmb\Action\Memory\Attributes\StepHandlerArray as AsArray;
use Mmb\Action\Memory\StepHandler;
use Mmb\Context;
use Mmb\Core\Updates\Update;

class MenuStepHandler extends InlineStepHandler
{

    #[Alias('m')]
    #[AsArray]
    public $storableKeyMap;

    protected function makeInlineAction(Context $context, Update $update) : ?InlineAction
    {
        return Menu::makeFromStep($context, $this, $update);
    }

}
