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
        return new POVBuilder($this);
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

    public array $bindingUserCallbacks = [];

    public function bindingUser(Closure $apply, Closure $revert)
    {
        $this->bindingUserCallbacks[] = [$apply, $revert];
    }

    public function fireApplyingUser(Stepping $user, ?Stepping $old, bool $isSame)
    {
        $store = [];
        foreach ($this->bindingUserCallbacks as $callback)
        {
            $store[] = $callback[0]($user, $old, $isSame);
        }

        return $store;
    }

    public function fireRevertingUser(Stepping $user, ?Stepping $old, bool $isSame, array $store)
    {
        foreach ($this->bindingUserCallbacks as $i => $callback)
        {
            $callback[1]($user, $old, $isSame, $store[$i] ?? null);
        }
    }

}
