<?php

namespace Mmb\Support\Telegram;

use Illuminate\Support\Facades\Facade;

class Keys extends Facade
{

    protected static function getFacadeAccessor()
    {
        return KeysFactory::class;
    }

}
