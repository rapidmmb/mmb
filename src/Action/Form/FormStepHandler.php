<?php

namespace Mmb\Action\Form;

use Mmb\Action\Memory\Attributes\StepHandlerAlias as Alias;
use Mmb\Action\Memory\Attributes\StepHandlerSafeClass as SafeClass;
use Mmb\Action\Memory\Attributes\StepHandlerSerialize as Serialize;
use Mmb\Action\Memory\Attributes\StepHandlerShortClass as ShortClass;
use Mmb\Action\Memory\Attributes\StepHandlerInstead as Instead;
use Mmb\Action\Memory\StepHandler;
use Mmb\Context;
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



    protected bool $isLoadedForm = false;

    protected ?Form $loadedForm;

    /**
     * Load and cache the form
     *
     * @param Context $context
     * @return Form|null
     */
    protected function getForm(Context $context) : ?Form
    {
        if (!$this->isLoadedForm)
        {
            $this->isLoadedForm = true;
            if ($this->class && class_exists($this->class) && is_a($this->class, Form::class, true))
            {
                /** @var Form $form */
                $form = $this->class::makeByContext($context);
                $form->loadStepHandler($this);

                $this->loadedForm = $form;
            }
            else
            {
                $this->loadedForm = null;
            }
        }

        return $this->loadedForm;
    }

    /**
     * Set the cached form
     *
     * @param Form $form
     * @return void
     */
    public function setForm(Form $form) : void
    {
        $this->isLoadedForm = true;
        $this->loadedForm = $form;
    }


    public function handle(Context $context, Update $update) : void
    {
        if ($form = $this->getForm($context))
        {
            $form->continueForm();
            return;
        }

        $update->skipHandler();
    }

    public function onBegin(Context $context, Update $update) : void
    {
        $this->getForm($context)?->beginUpdate();
    }

    public function onEnd(Context $context, Update $update) : void
    {
        $this->getForm($context)?->endUpdate();
    }

    public function onLost(Context $context, Update $update)
    {
        $this->getForm($context)?->lost();
    }
}
