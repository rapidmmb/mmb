<?php

namespace Mmb\Support\Behavior;

use Illuminate\Support\Facades\Facade;

class Behavior extends Facade
{

    protected static function getFacadeAccessor()
    {
        return BehaviorFactory::class;
    }

}