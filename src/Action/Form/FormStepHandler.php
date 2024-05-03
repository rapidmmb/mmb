<?php

namespace Mmb\Action\Form;

use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\Attributes\StepHandlerShortClass as ShortClass;
use Mmb\Action\Memory\Attributes\StepHandlerInstead as Instead;
use Mmb\Action\Memory\StepHandler;
use Mmb\Core\Updates\Update;

class FormStepHandler extends StepHandler
{

    #[Alias('C')]
    #[SafeClass]
    public $class;

    #[Alias('i')]
    public $currentInput;

    #[Alias('a')]
    #[Serialize]
    public $attributes;

    #[Alias('k')]
    #[Serialize]
    public $keyMap;

    public function handle(Update $update)
    {
        if($this->class && is_a($this->class, Form::class, true))
        {
            /** @var Form $form */
            $form = new $this->class($update);
            $form->loadStepHandler($this);
            $form->continueForm();

            return true;
        }

        return false;
    }

}
