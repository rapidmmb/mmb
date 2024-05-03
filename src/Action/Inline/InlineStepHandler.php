<?php

namespace Mmb\Action\Inline;

use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\StepHandler;

class InlineStepHandler extends StepHandler
{

    #[Alias('C')]
    #[SafeClass]
    public $initalizeClass;

    #[Alias('M')]
    public $initalizeMethod;


    #[Alias('d')]
    #[Serialize]
    public $withinData;

}
