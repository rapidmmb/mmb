<?php

namespace Mmb\Support\Caller;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed invoke($callable, array $normalArgs, array $dynamicArgs = [])
 */
class Caller extends Facade
{

    protected static function getFacadeAccessor()
    {
        return CallerFactory::class;
    }

}
