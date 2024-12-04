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
use Mmb\Support\Step\ConvertableToStepper;
use Mmb\Support\Step\Stepper;
use Throwable;

// todo : this can be better now ! using context
class POVBuilder
{
    use Conditionable;

    public function __construct(
        public readonly POVFactory $factory,
    )
    {
    }

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

        return $this;
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
        $this->start();

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
            $this->end();

            // Catch the error
            if (isset($this->catch))
            {
                if ($this->catch === false)
                {
                    return null;
                }

                return ($this->catch)($e);
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
            $this->end();
        }

        return $return;
    }

    protected bool $isStarting = false;

    /**
     * Start the POV
     *
     * You should call end() to end the POV, otherwise it will automatically end (not recommended).
     *
     * @return void
     */
    public function start()
    {
        if ($this->isStarting)
        {
            throw new \RuntimeException("The POV already started");
        }

        $this->applyPOV();

        $this->isStarting = true;
    }

    /**
     * End the POV
     *
     * @return void
     */
    public function end()
    {
        if (!$this->isStarting)
        {
            throw new \RuntimeException("The POV is not started");
        }

        $this->revertPOV();

        $this->isStarting = false;
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
     * @param Stepper|ConvertableToStepper $user
     * @param bool                           $save
     * @param ?bool                          $changeUpdate
     * @return $this
     */
    public function user(Stepper|ConvertableToStepper $user, bool $save = true, ?bool $changeUpdate = null)
    {
        $oldCurrentModel = null;
        $oldGuardUser = null;
        $oldStep = null;
        $isSame = false;
        $store = [];

        if ($user instanceof ConvertableToStepper)
        {
            $user = $user->toStepper();
        }

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
            function() use ($user, &$oldStep, &$oldGuardUser, &$oldCurrentModel, &$isSame, &$store)
            {
                $oldStep = Step::getModel();
                $oldCurrentModel = ModelFinder::current($user::class);
                $oldGuardUser = $user instanceof Authenticatable ? app(Bot::class)->guard()->user() : null;
                $isSame = $oldStep && $user && $oldStep->is($user);

                if (!$isSame)
                {
                    Step::setModel($user);
                    ModelFinder::storeCurrent($user);
                    if ($user instanceof Authenticatable)
                    {
                        app(Bot::class)->guard()->setUser($user);
                    }
                }

                $store = $this->factory->fireApplyingUser($user, $oldStep, $isSame);
            },
            function() use ($user, &$oldStep, &$oldGuardUser, &$oldCurrentModel, &$isSame, $save, &$store)
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
                        app(Bot::class)->guard()->setUser($oldGuardUser);
                    }
                    if (isset($oldCurrentModel))
                    {
                        ModelFinder::storeCurrent($oldCurrentModel);
                    }
                }

                $this->factory->fireRevertingUser($user, $oldStep, $isSame, $store);
            },
        );
    }

    public function __destruct()
    {
        if ($this->isStarting)
        {
            $this->end();
        }
    }

}