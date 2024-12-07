<?php

namespace Mmb\Support\Pov;

use Closure;
use Mmb\Context;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Update;
use Mmb\Support\Step\Contracts\ConvertableToStepper;
use Mmb\Support\Step\Contracts\Stepper;

class POVFactory
{

    public function make(?Context $base = null)
    {
        return new POVBuilder($this, $base);
    }


    /**
     * @deprecated
     */
    public function as(
        Update                            $update,
        Stepper|ConvertableToStepper|null $user,
        Closure|array                     $callback,
        bool                              $save = false,
        ?Context                          $base = null,
    )
    {
        return $this->make($base)
            ->update($update)
            ->when($user)
            ->user($user, $save)
            ->run($callback);
    }

    public function chat(
        ChatInfo|int                      $chat,
        Stepper|ConvertableToStepper|null $user,
        Closure|array                     $callback,
        bool                              $save = false,
        ?Context $base = null,
    )
    {
        return $this->make($base)
            ->updateChat($chat)
            ->when($user)
            ->user($user, $save)
            ->run($callback);
    }

    public function user(
        Stepper|ConvertableToStepper $user,
        Closure|array                $callback,
        bool                         $save = true,
        ?Context $base = null,
    )
    {
        return $this->make($base)
            ->user($user, $save)
            ->run($callback);
    }

}
