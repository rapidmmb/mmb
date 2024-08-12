<?php

namespace Mmb;

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

    public function as(
        Update $update,
        ?Stepping $stepping,
        Closure|array $callback,
        bool $save = false,
    )
    {
        $oldUpdate = app(Update::class);
        $oldStepping = Step::getModel();

        if ($stepping)
        {
            $oldCurrentModel = ModelFinder::current($stepping::class);
        }

        if ($stepping instanceof Authenticatable)
        {
            $oldUser = app(Bot::class)->guard()->user();
        }

        app()->bind(Update::class, fn() => $update);
        Step::setModel($stepping);
        app(Bot::class)->guard()->setUser($stepping instanceof Authenticatable ? $stepping : null);
        if ($stepping) ModelFinder::storeCurrent($stepping);

        if (is_array($callback) && count($callback) == 2)
        {
            if (is_string($callback[0]) && is_a($callback[0], Action::class, true))
            {
                $callback[0]::invokes($callback[1]);
            }
            else
            {
                $callback();
            }
        }
        else
        {
            $callback();
        }

        if ($save && $stepping)
        {
            $stepping->save();
        }

        app()->bind(Update::class, fn() => $oldUpdate);
        Step::setModel($oldStepping);
        app(Bot::class)->guard()->setUser($oldUser ?? null);
        if (isset($oldCurrentModel)) ModelFinder::storeCurrent($oldCurrentModel);
    }

    public function chat(
        ChatInfo $chat,
        ?Stepping $stepping,
        Closure|array $callback,
        bool $save = false,
    )
    {
        $update = Update::make([
            'message' => [
                'chat' => $chat->getFullData(),
            ],
        ], $chat->bot());

        return $this->as(
            $update,
            $stepping,
            $callback,
            $save,
        );
    }

    public function user(
        Stepping $user,
        Closure|array $callback,
        bool $save = true,
    )
    {
        $update = Update::make([
            'message' => [
                'chat' => [
                    'id' => $user->getKey(),
                ],
            ],
        ]);

        return $this->as(
            $update,
            $user,
            $callback,
            $save,
        );
    }

}
