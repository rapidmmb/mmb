<?php

namespace Mmb\Support\Pov;

use Closure;
use Illuminate\Support\Facades\Facade;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\ConvertableToStepping;
use Mmb\Support\Step\Stepping;

/**
 * @method static void as(Update $update, Stepping|ConvertableToStepping|null $user, Closure|array $callback, bool $save = false)
 * @method static void chat(ChatInfo $chat, Stepping|ConvertableToStepping|null $user, Closure|array $callback, bool $save = false)
 * @method static void user(Stepping|ConvertableToStepping $user, Closure|array $callback, bool $save = true)
 * @method static POVBuilder make()
 * @method static void bindingUser(Closure $apply, Closure $revert)
 */
class POV extends Facade
{

    protected static function getFacadeAccessor()
    {
        return POVFactory::class;
    }

}
