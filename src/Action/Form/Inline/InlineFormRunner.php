<?php

namespace Mmb\Action\Form\Inline;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\FormStepHandler;
use Mmb\Action\Form\Input;
use Mmb\Support\Behavior\Behavior;
use Mmb\Support\Caller\Caller;

class InlineFormRunner extends Form
{
    protected $inputs = [];

    protected $inputInits = [];

    /**
     * Add new input
     *
     * @param string  $name
     * @param Closure $callback
     * @return void
     */
    public function newInput(string $name, Closure $callback)
    {
        $this->inputInits[$name] = $callback;
        $this->inputs[] = $name;
    }

    public function getInputType(string $name) : string
    {
        return array_key_exists($name, $this->inputInits) ?
            static::detectInputTypeFromCallback($this->inputInits[$name]) :
            Input::class;
    }

    protected function onInitializingInput(Input $input)
    {
        parent::onInitializingInput($input);

        if ($callback = $this->inputInits[$input->name] ?? false)
        {
            Caller::invoke($callback, [], [
                'input' => $input,
                'form' => $this,
            ]);
        }
    }

    public string $inlineDefinedClass;

    protected function onBackDefault(bool $finished = true)
    {
        if ($this->isDefinedEvent('backDefault')) return;

        Behavior::back($this->inlineDefinedClass, dynamicArgs: [
            'form' => $this,
            'finished' => $finished,
        ]);
    }

    public function onCancel()
    {
        if ($this->isDefinedEvent('cancel')) return;

        parent::onCancel();
    }

    public FormStepHandler $lastSavedStep;

    /**
     * @param bool $keep
     * @return FormStepHandler
     */
    public function storeStepHandler(bool $keep = true)
    {
        return $this->lastSavedStep = parent::storeStepHandler(false);
    }


    /**
     * @param bool $enable
     * @return $this
     */
    public function defaultFormKey(bool $enable = true)
    {
        $this->defaultFormKey = $enable;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableDefaultFormKey()
    {
        return $this->defaultFormKey(false);
    }

    /**
     * @param bool|string $text
     * @return $this
     */
    public function cancelKey(bool|string $text = true)
    {
        $this->cancelKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableCancelKey()
    {
        return $this->cancelKey(false);
    }

    /**
     * @param bool|string $text
     * @return $this
     */
    public function skipKey(bool|string $text = true)
    {
        $this->skipKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableSkipKey()
    {
        return $this->skipKey(false);
    }

    /**
     * @param bool|string $text
     * @return $this
     */
    public function previousKey(bool|string $text = true)
    {
        $this->previousKey = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function disablePreviousKey()
    {
        return $this->previousKey(false);
    }

    /**
     * @param bool $enable
     * @return $this
     */
    public function mirrorKey(bool $enable = true)
    {
        $this->mirrorKey = $enable;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableMirrorKey()
    {
        return $this->mirrorKey(false);
    }

}
