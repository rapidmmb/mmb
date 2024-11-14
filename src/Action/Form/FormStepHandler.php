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



    protected bool $isLoadedForm = false;

    protected ?Form $loadedForm;

    /**
     * Load and cache the form
     *
     * @param Update $update
     * @return Form|null
     */
    protected function getForm(Update $update) : ?Form
    {
        if (!$this->isLoadedForm)
        {
            $this->isLoadedForm = true;
            if ($this->class && is_a($this->class, Form::class, true))
            {
                /** @var Form $form */
                $form = new $this->class($update);
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


    public function handle(Update $update) : void
    {
        if ($form = $this->getForm($update))
        {
            $form->continueForm();
            return;
        }

        $update->skipHandler();
    }

    public function onBegin(Update $update) : void
    {
        $this->getForm($update)?->beginUpdate();
    }

    public function onEnd(Update $update) : void
    {
        $this->getForm($update)?->endUpdate();
    }

    public function onLost(Update $update)
    {
        $this->getForm($update)?->lost();
    }
}
