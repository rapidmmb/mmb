<?php

namespace Mmb\Support\Pov;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Traits\Conditionable;
use Mmb\Action\Memory\Step;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Db\ModelFinder;
use Mmb\Support\Step\Stepping;
use Throwable;

class POVBuilder
{
    use Conditionable;

    private array $applies = [];

    public function add(Closure $apply, Closure $revert)
    {
        $this->applies[] = [$apply, $revert];

        return $this;
    }

    /**
     * Change the value binding
     *
     * @template T
     * @param class-string<T> $class
     * @param T               $value
     * @return $this
     */
    public function bindSingleton(string $class, $value)
    {
        $old = null;

        $this->add(
            function() use (&$old, $class, $value)
            {
                $old = app($class);
                app()->bind($class, fn() => $value);
            },
            function() use (&$old, $class)
            {
                app()->bind($class, fn() => $old);
            },
        );
    }

    protected Update $update;

    /**
     * Set the current update (required)
     *
     * @param Update $update
     * @return $this
     */
    public function update(Update $update)
    {
        $this->update = $update;

        return $this;
    }

    public function updateUser(UserInfo|int $user)
    {
        return $this->update(
            Update::make([
                'message' => [
                    'chat' => [
                        'id' => is_int($user) ? $user : $user->id,
                    ],
                ],
            ])
        );
    }

    public function updateChat(ChatInfo|int $chat)
    {
        return $this->update(
            Update::make([
                'message' => [
                    'chat' => is_int($chat) ? ['id' => $chat] : $chat->getFullData(),
                ],
            ])
        );
    }


    protected Closure|false $catch;

    /**
     * Run the callback when catching an error
     *
     * @param null|Closure(Throwable $e): mixed $callback
     * @return $this
     */
    public function catch(?Closure $callback = null)
    {
        $this->catch = $callback ?? false;

        return $this;
    }

    protected array $then = [];

    /**
     * Run the callback after running
     *
     * @param Closure(mixed $value): mixed $callback
     * @return $this
     */
    public function then(Closure $callback)
    {
        $this->then[] = $callback;

        return $this;
    }


    /**
     * Run the callback with this POV
     *
     * @param $callback
     * @return mixed
     * @throws Throwable
     */
    public function run($callback)
    {
        $this->applyPOV();

        try
        {
            // Run the callback
            if ($callback instanceof Closure)
            {
                $return = Caller::invoke($callback, []);
            }
            else
            {
                $return = Caller::invokeAction($callback, []);
            }
        }
        catch (Throwable $e)
        {
            $this->revertPOV();

            // Catch the error
            if (isset($this->catch))
            {
                if ($this->catch === false)
                {
                    return null;
                }

                return ($this->catch)();
            }

            throw $e;
        }

        try
        {
            // Run $then callbacks
            foreach ($this->then as $callback)
            {
                $return = $callback($return);
            }
        }
        finally
        {
            $this->revertPOV();
        }

        return $return;
    }

    protected ?Update $oldUpdate;

    protected function applyPOV()
    {
        // Apply the POV
        foreach ($this->applies as [$apply, ])
        {
            $apply();
        }

        $this->oldUpdate = app(Update::class);
        app()->bind(Update::class, fn() => $this->update);
    }

    protected function revertPOV()
    {
        // Revert the POV
        foreach (array_reverse($this->applies) as [, $revert])
        {
            $revert();
        }

        app()->bind(Update::class, fn() => $this->oldUpdate);
    }


    /**
     * Set the current user
     *
     * @param Stepping $user
     * @param bool     $save
     * @param ?bool    $changeUpdate
     * @return $this
     */
    public function user(Stepping $user, bool $save = true, ?bool $changeUpdate = null)
    {
        $oldCurrentModel = null;
        $oldGuardUser = null;
        $oldStep = null;
        $isSame = false;

        if ($changeUpdate ?? !isset($this->update))
        {
            $this->update(
                Update::make(
                    [
                        'message' => [
                            'chat' => [
                                'id' => $user->getKey(),
                            ],
                        ],
                    ]
                )
            );
        }

        return $this->add(
            function() use ($user, &$oldStep, &$oldGuardUser, &$oldCurrentModel, &$isSame)
            {
                $oldStep = Step::getModel();
                $oldCurrentModel = ModelFinder::current($user::class);
                $oldGuardUser = $user instanceof Authenticatable ? app(Bot::class)->guard()->user() : null;

                if (!($isSame = $oldStep && $user ? $oldStep->is($user) : false))
                {
                    Step::setModel($user);
                    ModelFinder::storeCurrent($user);
                    if ($user instanceof Authenticatable)
                    {
                        app(Bot::class)->guard()->setUser($user);
                    }
                }
            },
            function() use ($user, &$oldStep, &$oldGuardUser, &$oldCurrentModel, &$isSame, $save)
            {
                if ($save)
                {
                    $user->save();
                }

                if (!$isSame)
                {
                    Step::setModel($oldStep);
                    if ($oldGuardUser)
                    {
                        app(Bot::class)->guard()->setUser($oldGuardUser ?? null);
                    }
                    if (isset($oldCurrentModel))
                    {
                        ModelFinder::storeCurrent($oldCurrentModel);
                    }
                }
            },
        );
    }

}