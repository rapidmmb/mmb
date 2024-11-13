<?php

namespace Mmb\Action\Form\Inline;

use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Core\Updates\Update;

class InlineFormStepHandler extends InlineStepHandler
{

    #[Alias('i')]
    public $currentInput;

    #[Alias('a')]
    #[Serialize]
    public $attributes;

    #[Alias('k')]
    #[Serialize]
    public $keyMap;

    protected function makeInlineAction(Update $update) : ?InlineAction
    {
        return InlineForm::makeFromStep($this, $update);
    }

}
