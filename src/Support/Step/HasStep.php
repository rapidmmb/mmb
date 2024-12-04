<?php

namespace Mmb\Support\Step;

use Mmb\Action\Memory\StepHandler;

trait HasStep
{

    public static function bootHasStep()
    {
    }

    public function initializeHasStep()
    {
        $this->mergeFillable([$this->getStepColumn()]);
        $this->mergeCasts([$this->getStepColumn() => StepCasting::class]);
    }

    // protected $stepColumn = 'step';

    /**
     * Get step column
     *
     * @return string
     */
    public function getStepColumn()
    {
        return isset($this->stepColumn) ? $this->stepColumn : 'step';
    }

    /**
     * Get current step
     *
     * @return ?StepHandler
     */
    public function getStep() : ?StepHandler
    {
        return ($step = $this->{$this->getStepColumn()}) instanceof StepHandler ? $step : null;
    }

    /**
     * Set current step
     *
     * @param ?StepHandler $stepHandler
     * @return void
     */
    public function setStep(?StepHandler $stepHandler)
    {
        $this->{$this->getStepColumn()} = $stepHandler;
    }

}
