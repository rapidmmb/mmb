<?php

namespace Mmb\Support\Pov;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Mmb\Action\Action;
use Mmb\Action\Memory\Step;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Update;
use Mmb\Support\Db\ModelFinder;
use Mmb\Support\Step\Stepping;

class POVFactory
{

    public function make()
    {
        return app(POVBuilder::class);
    }

    /**
     * @deprecated
     */
    public function as(
        Update $update,
        ?Stepping $user,
        Closure|array $callback,
        bool $save = false,
    )
    {
        return $this->make()
            ->update($update)
            ->when($user)
            ->user($user, $save)
            ->run($callback);
    }

    public function chat(
        ChatInfo|int $chat,
        ?Stepping $user,
        Closure|array $callback,
        bool $save = false,
    )
    {
        return $this->make()
            ->updateChat($chat)
            ->when($user)
            ->user($user, $save)
            ->run($callback);
    }

    public function user(
        Stepping $user,
        Closure|array $callback,
        bool $save = true,
    )
    {
        return $this->make()
            ->user($user, $save)
            ->run($callback);
    }

}
