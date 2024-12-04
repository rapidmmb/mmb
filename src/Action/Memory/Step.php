<?php

namespace Mmb\Action\Memory;

use Illuminate\Support\Facades\Facade;
use Mmb\Support\Step\ConvertableToStepper;
use Mmb\Support\Step\Stepper;

/**
 * @deprecated
 * @method static void setModel(Stepper|ConvertableToStepper|null $model)
 * @method static Stepper|null getModel()
 * @method static void set(StepHandler|ConvertableToStep|null $step)
 * @method static StepHandler|null get()
 */
class Step extends Facade
{

    protected static function getFacadeAccessor()
    {
        return StepFactory::class;
    }

}
