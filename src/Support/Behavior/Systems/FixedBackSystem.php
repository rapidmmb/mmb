<?php

namespace Mmb\Support\Behavior\Systems;

use Mmb\Auth\AreaRegister;
use Mmb\Support\Behavior\Behavior;
use Mmb\Support\Behavior\Contracts\BackSystem;
use Mmb\Support\Behavior\Exceptions\BackActionNotDefinedException;
use Mmb\Support\Caller\Caller;

class FixedBackSystem implements BackSystem
{

    public function back(array $args, array $dynamicArgs) : void
    {
        if ($class = Behavior::getCurrentClass())
        {
            if ($back = app(AreaRegister::class)->getAttribute($class, 'back'))
            {
                Caller::invokeAction($back, $args, $dynamicArgs);
                return;
            }
        }

        throw new BackActionNotDefinedException("Back action is not defined");
    }

}