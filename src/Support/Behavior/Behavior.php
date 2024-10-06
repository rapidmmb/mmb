<?php

namespace Mmb\Support\Behavior;

use Illuminate\Support\Facades\Facade;
use Mmb\Support\Behavior\Contracts\BackSystem;

/**
 * @method static void setDefaultBackSystem(BackSystem $system)
 * @method static void back(string $class = null, array $args = [], array $dynamicArgs = [])
 */
class Behavior extends Facade
{

    protected static function getFacadeAccessor()
    {
        return BehaviorFactory::class;
    }

}