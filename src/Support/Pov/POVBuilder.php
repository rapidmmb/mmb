<?php

namespace Mmb\Support\Pov;

use Closure;
use Illuminate\Support\Traits\Conditionable;
use Mmb\Context;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Step\Contracts\ConvertableToStepper;
use Mmb\Support\Step\Contracts\Stepper;
use Mmb\Support\Telegram\Contracts\TelegramIdentifier;
use Throwable;
use function Amp\async;

// todo : this can be better now ! using context
class POVBuilder
{
    use Conditionable;

    protected Context $context;

    public function __construct(
        public readonly POVFactory $factory,
        ?Context                   $baseContext = null,
    )
    {
        $this->context = isset($baseContext) ? $baseContext->copy() : new Context();
    }

//    private array $applies = [];
//
//    public function add(Closure $apply, Closure $revert)
//    {
//        $this->applies[] = [$apply, $revert];
//
//        return $this;
//    }

    /**
     * Add a value
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function put(string $key, mixed $value): static
    {
        $this->context->put($key, $value);
        return $this;
    }

    /**
     * Forget a key
     *
     * @param string $key
     * @return $this
     */
    public function forget(string $key): static
    {
        $this->context->forget($key);
        return $this;
    }

    /**
     * Set the current update
     *
     * @param Update $update
     * @return $this
     */
    public function update(Update $update): static
    {
        $this->context->update = $update;
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
        try {
            // Run the callback
            if ($callback instanceof Closure) {
                $return = Caller::invoke($callback, [$this->context]);
            } else {
                $return = Caller::invokeAction($this->context, $callback, []);
            }
        } catch (Throwable $e) {
            // Catch the error
            if (isset($this->catch)) {
                if ($this->catch === false) {
                    return null;
                }

                return ($this->catch)($e);
            }

            throw $e;
        }

        // Run $then callbacks
        foreach ($this->then as $callback) {
            $return = $callback($return);
        }

        return $return;
    }

    public function runAsync($callback)
    {
        return async(fn() => $this->run($callback));
    }


    /**
     * Set the current user
     *
     * @param Stepper|ConvertableToStepper $user
     * @param bool $save
     * @param ?bool $changeUpdate
     * @return $this
     */
    public function user(Stepper|ConvertableToStepper $user, bool $save = true, ?bool $changeUpdate = null)
    {
        if ($user instanceof ConvertableToStepper) {
            $user = $user->toStepper();
        }

        if ($changeUpdate ?? !$this->context->update) {
            $this->updateUser(
                $user->getAttribute($user instanceof TelegramIdentifier ? $user->getTelegramIdKeyName() : $user->getKeyName()),
            );
        }

        $this->context->stepFactory->setModel($user);
        $this->context->instance($user);

        if ($save) {
            $this->then(function ($value) use ($user) {
                $user->save();
                return $value;
            });
        }

        return $this;
    }

    public function toContext(): Context
    {
        return $this->context;
    }

}