<?php

namespace Mmb\Action\Memory;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mmb\Action\Inline\InlineStepHandler;
use Mmb\Context;
use Mmb\Support\Step\Contracts\ConvertableToStepper;
use Mmb\Support\Step\Contracts\Stepper;

class StepFactory
{

    public function __construct(
        public Context $context,
    )
    {
    }

    public ?Stepper $model = null;

    /**
     * Set related model
     *
     * @param Stepper|ConvertableToStepper|null $model
     * @return void
     */
    public function setModel(Stepper|ConvertableToStepper|null $model)
    {
        if ($model instanceof ConvertableToStepper) {
            $model = $model->toStepper();
        }

        $this->model = $model;
    }

    /**
     * Get related model
     *
     * @return Stepper|null
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set step
     *
     * @param StepHandler|ConvertableToStep|null $step
     * @return void
     */
    public function set(StepHandler|ConvertableToStep|null $step)
    {
        if ($step instanceof ConvertableToStep) {
            $step = $step->toStep();
        }

        if (!$this->model) {
            throw new ModelNotFoundException("Related model not found");
        }

        $destroyedStep = $this->get();

        $this->model->setStep($step);

        $destroyedStep?->fire('lost', $this->context, $this->context->update);
    }

    /**
     * Get current step
     *
     * @return StepHandler|null
     */
    public function get()
    {
        if (!$this->model) {
            throw new ModelNotFoundException("Related model not found");
        }

        return $this->model->getStep();
    }

    /**
     * Fire current step event
     *
     * @param string $event
     * @param ...$args
     * @return mixed
     */
    public function fire(string $event, ...$args)
    {
        return $this->get()?->fire($event, ...$args);
    }

    /**
     * Check the handler is a class type
     *
     * @param StepHandler $step
     * @param string $class
     * @return bool
     */
    public function isTypeOf(StepHandler $step, string $class): bool
    {
        return $step instanceof $class;
    }

    /**
     * Check the inline handler is for a class
     *
     * @param StepHandler $step
     * @param string $class
     * @param string|null $method
     * @return bool
     */
    public function isInlineOf(StepHandler $step, string $class, ?string $method = null): bool
    {
        if (!($step instanceof InlineStepHandler)) {
            return false;
        }

        return $step->initalizeClass == $class && (!isset($method) || $step->initalizeMethod == $method);
    }

    /**
     * Check the handle is type of the class
     *
     * @param StepHandler $step
     * @param string $class
     * @return bool
     */
    public function is(StepHandler $step, string $class): bool
    {
        if (is_a($class, StepHandler::class, true)) {
            return $this->isTypeOf($step, $class);
        }

        return $this->isInlineOf($step, $class);
    }

}
