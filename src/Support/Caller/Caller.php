<?php

namespace Mmb\Support\Caller;

use Illuminate\Support\Facades\Facade;
use Mmb\Action\Action;

/**
 * @method static mixed invoke($callable, array $normalArgs, array $dynamicArgs = [])
 * @method static mixed invokeAction(array|string|Action $callable, array $normalArgs, array $dynamicArgs = [])
 * @method static array splitArguments(array $args)
 */
class Caller extends Facade
{

    protected static function getFacadeAccessor()
    {
        return CallerFactory::class;
    }

}
