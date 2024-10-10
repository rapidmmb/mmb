<?php

namespace Mmb\Action\Form\Inline;

use Closure;
use Mmb\Action\Action;
use Mmb\Action\Form\FormStepHandler;
use Mmb\Action\Inline\InlineAction;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Action\Memory\Step;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;

class InlineForm extends InlineAction
{

    public InlineFormRunner $form;

    public function initializer($object, string $method)
    {
        $this->form = new InlineFormRunner;
        $this->form->inlineDefinedClass = get_class($object);

        return parent::initializer($object, $method);
    }


    protected $stepHandlerClass = InlineFormStepHandler::class;

    /**
     * @param InlineFormStepHandler $step
     * @return void
     */
    protected function saveToStep(InlineStepHandler $step)
    {
        parent::saveToStep($step);

        $formStep = $this->form->lastSavedStep ?? $this->form->storeStepHandler(false);
        $step->attributes = $formStep->attributes;
        $step->currentInput = $formStep->currentInput;
        $step->keyMap = $formStep->keyMap;
    }

    /**
     * @param InlineFormStepHandler $step
     * @param Update            $update
     * @return void
     */
    protected function loadFromStep(InlineStepHandler $step, Update $update)
    {
        parent::loadFromStep($step, $update);

        $formStep = new FormStepHandler();
        $formStep->attributes = $step->attributes;
        $formStep->currentInput = $step->currentInput;
        $formStep->keyMap = $step->keyMap;

        $this->form->loadStepHandler($formStep);
    }

    /**
     * @param Update $update
     * @return void
     */
    public function handle(Update $update)
    {
        $this->form->continueForm();

        if(isset($this->form->lastSavedStep))
        {
            $this->saveAction();
        }
    }

    /**
     * Request form
     *
     * @param array $attributes
     * @return void
     */
    public function request(array $attributes = [])
    {
        $this->form->request($attributes);

        if(isset($this->form->lastSavedStep))
        {
            $this->saveAction();
        }
    }

    /**
     * Create new input
     *
     * @param string  $name
     * @param Closure $callback
     * @return $this
     */
    public function input(string $name, Closure $callback)
    {
        $this->form->newInput($name, $callback);
        return $this;
    }

    /**
     * Finish event
     *
     * @param Closure $callback
     * @return $this
     */
    public function finish(Closure $callback)
    {
        $this->form->listen('finish', $callback);
        return $this;
    }

    /**
     * Cancel event
     *
     * @param Closure $callback
     * @return $this
     */
    public function cancel(Closure $callback)
    {
        $this->form->listen('cancel', $callback);
        return $this;
    }

    /**
     * Default back event
     *
     * @param Closure|array|string $callback
     * @param string|null          $method
     * @return $this
     */
    public function back(Closure|array|string $callback, ?string $method = null)
    {
        if (is_string($callback) && $method === null)
        {
            $method = $callback;
            $callback = null;
        }
        elseif (is_array($callback))
        {
            [$callback, $method] = $callback;
        }

        if ($callback instanceof Closure)
        {
            $this->form->listen('backDefault', $callback);
        }
        else
        {
            $this->form->listen('backDefault', function ($finished) use($callback, $method)
            {
                if (is_string($callback) && is_a($callback, Action::class, true))
                {
                    $callback::make()->invokeDynamic($method, [$finished], $this->form->getEventDynamicArgs('backDefault'));
                }
                else
                {
                    Caller::invoke([$callback, $method], [$finished], $this->form->getEventDynamicArgs('backDefault'));
                }
            });
        }

        return $this;
    }

    /**
     * Get attribute
     *
     * @param string $name
     * @param        $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return parent::get($name, function() use($name, $default)
        {
            return $this->form->get($name, $default);
        });
    }

    /**
     * Apply scope
     *
     * @param InlineFormScope $scope
     * @return $this
     */
    public function scope(InlineFormScope $scope)
    {
        $scope->apply($this);
        return $this;
    }

    /**
     * Apply default delete scope
     *
     * @param string|null $prompt
     * @param string|null $confirm
     * @param string|null $cancel
     * @return $this
     */
    public function deleteScope(
        ?string $prompt = null,
        ?string $confirm = null,
        ?string $cancel = null,
    )
    {
        return $this->scope(new Scopes\IFDeleteScope($prompt, $confirm, $cancel));
    }

    /**
     * Request the form
     *
     * @return void
     */
    public function invoke()
    {
        $this->request();
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function defaultFormKey(bool $enabled = true)
    {
        $this->form->defaultFormKey($enabled);
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
        $this->form->cancelKey($text);
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
        $this->form->skipKey($text);
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
        $this->form->previousKey($text);
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
     * @param bool $enabled
     * @return $this
     */
    public function mirrorKey(bool $enabled = true)
    {
        $this->form->mirrorKey($enabled);
        return $this;
    }

    /**
     * @return $this
     */
    public function disableMirrorKey()
    {
        return $this->mirrorKey(false);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function ineffectiveKey(bool $enabled)
    {
        $this->form->ineffectiveKey($enabled);
        return $this;
    }

    /**
     * @return $this
     */
    public function disableIneffectiveKey()
    {
        return $this->ineffectiveKey(false);
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function withoutChangesKey(bool $enabled)
    {
        $this->form->withoutChangesKey($enabled);
        return $this;
    }

    /**
     * @return $this
     */
    public function disableWithoutChangesKey()
    {
        return $this->withoutChangesKey(false);
    }

}
