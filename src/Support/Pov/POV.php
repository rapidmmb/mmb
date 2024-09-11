<?php

namespace Mmb\Support\Pov;

use Closure;
use Illuminate\Support\Facades\Facade;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\Stepping;

/**
 * @method static void as(Update $update, ?Stepping $stepping, Closure|array $callback, bool $save = false)
 * @method static void chat(ChatInfo $chat, ?Stepping $stepping, Closure|array $callback, bool $save = false)
 * @method static void user(Stepping $user, Closure|array $callback, bool $save = true)
 * @method static POVBuilder make()
 */
class POV extends Facade
{

    protected static function getFacadeAccessor()
    {
        return POVFactory::class;
    }

}
