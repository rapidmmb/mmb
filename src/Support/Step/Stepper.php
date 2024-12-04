<?php

namespace Mmb\Support\Step;

use Mmb\Action\Memory\StepHandler;

interface Stepper
{

    /**
     * Get current step
     *
     * @return ?StepHandler
     */
    public function getStep() : ?StepHandler;

    /**
     * Set current step
     *
     * @param ?StepHandler $stepHandler
     * @return mixed
     */
    public function setStep(?StepHandler $stepHandler);

}
