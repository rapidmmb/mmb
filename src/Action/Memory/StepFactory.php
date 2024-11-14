<?php

namespace Mmb\Action\Memory;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\ConvertableToStepping;
use Mmb\Support\Step\Stepping;

class StepFactory
{

    public ?Stepping $model = null;

    /**
     * Set related model
     *
     * @param Stepping|ConvertableToStepping|null $model
     * @return void
     */
    public function setModel(Stepping|ConvertableToStepping|null $model)
    {
        if ($model instanceof ConvertableToStepping)
        {
            $model = $model->toStepping();
        }

        $this->model = $model;
    }

    /**
     * Get related model
     *
     * @return Stepping|null
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
        if ($step instanceof ConvertableToStep)
        {
            $step = $step->toStep();
        }

        if (!$this->model)
        {
            throw new ModelNotFoundException("Related model not found");
        }

        $destroyedStep = $this->get();

        $this->model->setStep($step);

        $destroyedStep?->fire('lost', app(Update::class));
    }

    /**
     * Get current step
     *
     * @return StepHandler|null
     */
    public function get()
    {
        if (!$this->model)
        {
            throw new ModelNotFoundException("Related model not found");
        }

        return $this->model->getStep();
    }

}
