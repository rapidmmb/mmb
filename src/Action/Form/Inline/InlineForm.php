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
        $this->form->on('finish', $callback);
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
        $this->form->on('cancel', $callback);
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
            $this->form->on('backDefault', $callback);
        }
        else
        {
            $this->form->on('backDefault', function ($finished) use($callback, $method)
            {
                if (is_string($callback) && is_a($callback, Action::class, true))
                {
                    $callback::make()->invokeDynamic($method, [$finished], $this->form->getEventDynamicArgs());
                }
                else
                {
                    Caller::invoke([$callback, $method], [$finished], $this->form->getEventDynamicArgs());
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

}
