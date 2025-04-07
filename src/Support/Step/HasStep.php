<?php

namespace Mmb\Support\Step;

use Illuminate\Database\Eloquent\Model;
use Mmb\Action\Memory\StepHandler;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Support\Traits\HasPresentAttributes;

/**
 * @property ?StepHandler $step
 */
trait HasStep
{
    use HasPresentAttributes;

    protected ?StepHandler $_stepCached = null;

    protected static function bootHasStep(): void
    {
        static::extendPresent(function (Present $present) {
            $step = $present->json('step')->nullable()->noCast();

            $step->typeHint('null|' . StepHandler::class);

            $step->getUsing(function (?string $value, Model $record): ?StepHandler {
                if ($record->_stepCached !== null) {
                    return $record->_stepCached;
                }

                if ($value === null) {
                    return null;
                }

                return $record->_stepCached = StepGrammar::stringToStep($value);
            });

            $step->setUsing(function (?StepHandler $value, Model $record): ?string {
                if (!$value) {
                    return $record->_stepCached = null;
                }

                $record->_stepCached = $value;
                return StepGrammar::stepToString($value);
            });
        });
    }

    /**
     * Get current step
     *
     * @return ?StepHandler
     */
    public function getStep(): ?StepHandler
    {
        return $this->step;
    }

    /**
     * Set current step
     *
     * @param ?StepHandler $stepHandler
     * @return void
     */
    public function setStep(?StepHandler $stepHandler)
    {
        $this->step = $stepHandler;
    }
}
