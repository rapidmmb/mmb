<?php

namespace Mmb\Support\Step;

use Mmb\Action\Memory\StepHandler;
use Mmb\Action\Memory\StepMemory;

/**
 * @property ?StepHandler $step
 */
trait HasStep
{

    public function initializeHasStep()
    {
        $this->mergeFillable(['step']);
    }

    protected ?StepHandler $_stepCached = null;

    /**
     * Get current step
     *
     * @return ?StepHandler
     */
    public function getStep(): ?StepHandler
    {
        if ($this->_stepCached === null) {
            if (!@$this->attributes['step']) {
                return null;
            }

            return $this->_stepCached = $this->detectStepValue();
        }

        return $this->_stepCached;
    }

    /**
     * Set current step
     *
     * @param ?StepHandler $stepHandler
     * @return void
     */
    public function setStep(?StepHandler $stepHandler)
    {
        if (!$stepHandler) {
            $this->_stepCached = null;
            $this->attributes['step'] = ''; // todo: empty string or null?
            return;
        }

        $stepHandler->save($memory = StepMemory::make());

        $this->_stepCached = $stepHandler;
        $this->attributes['step'] = json_encode([
            '_' => get_class($stepHandler),
            'm' => $memory->toArray(),
        ]);
    }

    protected function detectStepValue(): ?StepHandler
    {
        if (!$value = @$this->attributes['step']) {
            return null;
        }

        if (!is_array($data = @json_decode($value, true))) {
            goto returnNull;
        }

        if (
            !array_key_exists('_', $data) ||
            !class_exists($data['_']) ||
            !is_a($data['_'], StepHandler::class, true)
        ) {
            goto returnNull;
        }

        // Try to create step handler
        try {
            $memory = StepMemory::make(is_array(@$data['m']) ? $data['m'] : []);
            return $data['_']::make($memory);
        } catch (\Throwable) {
            goto returnNull;
        }

        returnNull:
        $this->attributes['step'] = ''; // todo: empty string or null?
        /*
         * or:
         * [ '_' => null ]
         */
        return null;
    }

    public function getStepAttribute(): ?StepHandler
    {
        return $this->getStep();
    }

    public function setStepAttribute(?StepHandler $step)
    {
        $this->setStep($step);
    }

}
