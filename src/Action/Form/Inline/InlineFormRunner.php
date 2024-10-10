<?php

namespace Mmb\Action\Form\Inline;

use Closure;
use Mmb\Action\Form\Form;
use Mmb\Action\Form\FormStepHandler;
use Mmb\Action\Form\Input;
use Mmb\Support\Behavior\Behavior;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\EventCaller;

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
        Behavior::back($this->inlineDefinedClass, dynamicArgs: [
            'form' => $this,
            'finished' => $finished,
        ]);
    }

    protected function getEventOptionsOnBackDefault() : array
    {
        return [
            'default' => EventCaller::DEFAULT_WHEN_NOT_LISTENING,
        ];
    }

    protected function getEventOptionsOnFinish() : array
    {
        return [
            'default' => EventCaller::DEFAULT_WHEN_NOT_LISTENING,
        ];
    }

    protected function getEventOptionsOnCancel() : array
    {
        return [
            'default' => EventCaller::DEFAULT_WHEN_NOT_LISTENING,
        ];
    }

    protected function getEventOptionsOnBack() : array
    {
        return [
            'default' => EventCaller::DEFAULT_WHEN_NOT_LISTENING,
        ];
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
     * @param bool $enabled
     * @return $this
     */
    public function defaultFormKey(bool $enabled = true)
    {
        $this->defaultFormKey = $enabled;
        return $this;
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
     * @param bool|string $text
     * @return $this
     */
    public function skipKey(bool|string $text = true)
    {
        $this->skipKey = $text;
        return $this;
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
     * @param bool $enabled
     * @return $this
     */
    public function mirrorKey(bool $enabled = true)
    {
        $this->mirrorKey = $enabled;
        return $this;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function ineffectiveKey(bool $enabled)
    {
        $this->ineffectiveKey = $enabled;
        return $this;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function withoutChangesKey(bool $enabled)
    {
        $this->withoutChangesKey = $enabled;
        return $this;
    }

}
