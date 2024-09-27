<?php

namespace Mmb\Action\Memory;

use Illuminate\Support\Facades\Facade;
use Mmb\Support\Step\ConvertableToStepping;
use Mmb\Support\Step\Stepping;

/**
 * @method static void setModel(Stepping|ConvertableToStepping|null $model)
 * @method static Stepping|null getModel()
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
