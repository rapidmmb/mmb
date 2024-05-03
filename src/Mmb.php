<?php

namespace Mmb;

use Illuminate\Support\Facades\Facade;
use Mmb\Core\Bot;

class Mmb extends Facade
{

    protected static function getFacadeAccessor()
    {
        return Bot::class;
    }

}
