<?php

namespace Mmb\Action\Memory;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mmb\Support\Step\Stepping;

class StepFactory
{

    public ?Stepping $model = null;

    /**
     * Set related model
     *
     * @param ?Stepping $model
     * @return void
     */
    public function setModel(?Stepping $model)
    {
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
        if($step instanceof ConvertableToStep)
        {
            $step = $step->toStep();
        }

        if(!$this->model)
        {
            throw new ModelNotFoundException("Related model not found");
        }

        $this->model->setStep($step);
    }

    /**
     * Get current step
     *
     * @return StepHandler|null
     */
    public function get()
    {
        if(!$this->model)
        {
            throw new ModelNotFoundException("Related model not found");
        }

        return $this->model->getStep();
    }

}
