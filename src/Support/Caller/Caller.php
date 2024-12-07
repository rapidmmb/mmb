<?php

namespace Mmb\Support\Caller;

use Illuminate\Support\Facades\Facade;
use Mmb\Action\Action;
use Mmb\Context;

/**
 * @method static mixed invoke($callable, array $normalArgs, array $dynamicArgs = [])
 * @method static mixed invokeAction(Context $context, array|string|Action $callable, array $normalArgs, array $dynamicArgs = [])
 * @method static array splitArguments(array $args)
 */
class Caller extends Facade
{

    protected static function getFacadeAccessor()
    {
        return CallerFactory::class;
    }

}
