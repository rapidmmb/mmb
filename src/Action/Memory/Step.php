<?php

namespace Mmb\Action\Memory;

use Illuminate\Support\Facades\Facade;
use Mmb\Support\Step\Stepping;

/**
 * @method static void setModel(?Stepping $model)
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
