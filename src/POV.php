<?php

namespace Mmb;

use App\Models\Chat;
use Closure;
use Illuminate\Support\Facades\Facade;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\Stepping;

/**
 * @method static void as(Update $update, ?Stepping $stepping, Closure|array $callback, bool $save = false)
 * @method static void chat(Chat $chat, ?Stepping $stepping, Closure|array $callback, bool $save = false)
 * @method static void user(Stepping $user, Closure|array $callback, bool $save = true)
 */
class POV extends Facade
{

    protected static function getFacadeAccessor()
    {
        return POVFactory::class;
    }

}
