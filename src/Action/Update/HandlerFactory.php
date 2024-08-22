<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Mmb\Action\Memory\Step;
use Mmb\Action\Memory\StepHandlerPipe;
use Mmb\Action\Middle\MiddleAction;
use Mmb\Action\Middle\MiddleActionHandledUpdateHandling;
use Mmb\Action\Section\Controllers\CallbackControlHandler;
use Mmb\Action\Section\Controllers\InlineControlHandler;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Update;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\HasSimpleEvents;
use Mmb\Support\Db\ModelFinder;
use Mmb\Support\Step\Stepping;

class HandlerFactory
{
    use HasSimpleEvents;

    public function __construct(
        public readonly Bot $bot,
        public readonly Update $update,
    )
    {
    }

    protected array $dynamicArgs = [];

    public function getEventDynamicArgs()
    {
        return $this->dynamicArgs +
            [
                'update' => $this->update,
                'bot' => $this->bot,
            ];
    }

    /**
     * Invoke method using dynamic arguments
     *
     * @param Closure $callback
     * @param         ...$args
     * @return mixed
     */
    public function call(Closure $callback, ...$args)
    {
        [$args, $dynamicArgs] = Caller::splitArguments($args);
        $dynamicArgs += $this->getEventDynamicArgs();

        return Caller::invoke($callback, $args, $dynamicArgs);
    }

    /**
     * Invoke method using dynamic arguments, and return $this object.
     *
     * @param Closure $callback
     * @param         ...$args
     * @return $this
     */
    public function invoke(Closure $callback, ...$args)
    {
        $this->call($callback, ...$args);
        return $this;
    }

    /**
     * Get or invoke value
     *
     * @param $value
     * @param ...$args
     * @return mixed
     */
    protected function value($value, ...$args)
    {
        return $value instanceof Closure ? $this->call($value, ...$args) : $value;
    }


    protected function fallback()
    {
        throw new HandlerNotMatchedException();
    }

    /**
     * Match the condition
     *
     * @param mixed|Closure $condition
     * @return $this
     */
    public function match($condition)
    {
        if (!$this->value($condition))
        {
            $this->fallback();
        }

        return $this;
    }

    protected array $finallySaves = [];

    /**
     * Use a record
     *
     * @param string       $model     Model type
     * @param mixed|Closure $by        Get value to search
     * @param Closure|null $create    Create when record not found
     * @param Closure|null $validate  Validate the record
     * @param string       $key       Get key to find the model
     * @param bool         $useCache  Use cache to search model
     * @param string       $name      Record name that passed in each callback
     * @param bool         $asCurrent Set record as current record
     * @param bool         $setUser   Set as guard user
     * @param bool         $autoSave  Auto save after update handled
     * @return $this
     */
    public function record(
        string $model,
        mixed $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string $key = '',
        bool $useCache = true,
        string $name = 'record',
        bool $asCurrent = true,
        bool $setUser = false,
        bool $autoSave = false,
    )
    {
        $by = $this->value($by);

        if ($by === null)
        {
            $this->fallback();
        }

        if ($useCache)
        {
            $record = ModelFinder::findBy($model, $key, $by);
        }
        else
        {
            $record = $model::query()->where($key ?: app($model)->getKey())->first();
        }

        if (!$record && $create)
        {
            $record = $this->call($create);
            if (is_array($record))
            {
                $record = $model::create($record);
            }

            if ($record && $useCache)
            {
                ModelFinder::store($record);
            }
        }

        if (!$record)
        {
            $this->fallback();
        }

        if ($validate && !$this->call($validate, $record))
        {
            $this->fallback();
        }

        if ($asCurrent)
        {
            ModelFinder::storeCurrent($record);
        }

        if ($setUser)
        {
            $this->bot->guard()->setUser($record);
        }

        if ($autoSave)
        {
            $this->finallySaves[] = $record;
        }

        $this->dynamicArgs[$name] = $record;

        return $this;
    }

    /**
     * Use a record as not-current record
     *
     * @param string        $model     Model type
     * @param mixed|Closure $by        Get value to search
     * @param Closure|null  $create    Create when record not found
     * @param Closure|null  $validate  Validate the record
     * @param string        $key       Get key to find the model
     * @param bool          $useCache  Use cache to search model
     * @param string        $name      Record name that passed in each callback
     * @param bool          $setUser   Set as guard user
     * @param bool          $autoSave  Auto save after update handled
     * @return $this
     */
    public function recordOther(
        string $model,
        mixed $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string $key = '',
        bool $useCache = true,
        string $name = 'record',
        bool $setUser = false,
        bool $autoSave = false,
    )
    {
        return $this->record(
            $model,
            $by,
            create: $create,
            validate: $validate,
            key: $key,
            useCache: $useCache,
            name: $name,
            asCurrent: false,
            setUser: $setUser,
            autoSave: $autoSave,
        );
    }

    protected ?Stepping $stepRecord = null;

    /**
     * Use a step record
     *
     * @param string        $model     Model type
     * @param mixed|Closure $by        Get value to search
     * @param Closure|null  $create    Create when record not found
     * @param Closure|null  $validate  Validate the record
     * @param string        $key       Get key to find the model
     * @param bool          $useCache  Use cache to search model
     * @param string        $name      Record name that passed in each callback
     * @param bool          $asCurrent Set record as current record
     * @param bool          $setUser   Set as guard user
     * @param bool          $autoSave  Auto save after update handled
     * @return $this
     */
    public function recordStep(
        string $model,
        mixed $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string $key = '',
        bool $useCache = true,
        string $name = 'record',
        bool $asCurrent = true,
        bool $setUser = false,
        bool $autoSave = false,
    )
    {
        $this->record(...func_get_args());
        $this->stepAs($this->dynamicArgs[$name]);

        return $this;
    }

    /**
     * Use the user model
     *
     * @param string        $model     Model type
     * @param mixed|Closure $by        Get value to search
     * @param Closure|null  $create    Create when record not found
     * @param Closure|null  $validate  Validate the record
     * @param string        $key       Get key to find the model
     * @param bool          $useCache  Use cache to search model
     * @param string        $name      Record name that passed in each callback
     * @param bool          $autoSave  Auto save after update handled
     * @return $this
     */
    public function recordUser(
        string $model,
        mixed $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string $key = '',
        bool $useCache = true,
        string $name = 'record',
        bool $autoSave = false,
    )
    {
        $this->record(
            $model,
            $by,
            create: $create,
            validate: $validate,
            key: $key,
            useCache: $useCache,
            name: $name,
            asCurrent: true,
            setUser: true,
            autoSave: $autoSave,
        );

        if ($this->dynamicArgs[$name] instanceof Stepping)
        {
            $this->stepAs($this->dynamicArgs[$name]);
        }

        return $this;
    }

    /**
     * Set step record as
     *
     * @param string|Stepping|Closure $value
     * @return $this
     */
    public function stepAs(string|Stepping|Closure $value)
    {
        $value = $this->value($value);

        if (is_string($value))
        {
            $value = $this->dynamicArgs[$value];
        }

        $this->stepRecord = $value;

        return $this;
    }

    protected bool $isHandled = false;

    /**
     * Handle the update
     *
     * @param array        $handlers
     * @param Closure|null $final
     * @return $this
     */
    public function handle(
        array $handlers,
        ?Closure $final = null,
    )
    {
        if ($this->isHandled)
        {
            throw new \InvalidArgumentException("This handler has been handled before");
        }

        $this->isHandled = true;

        try
        {
            if ($this->stepRecord)
            {
                Step::setModel($this->stepRecord);
            }

            while (true)
            {
                $this->update->isHandled = false;

                try
                {
                    foreach ($handlers as $handler)
                    {
                        if ($handler instanceof Closure)
                        {
                            $handler = $handler();
                        }

                        if ($handler === null)
                        {
                            continue;
                        }

                        if (!is_a($handler, UpdateHandling::class, true))
                        {
                            throw new \TypeError(
                                "Expected [" . UpdateHandling::class . "], given [" . (is_string(
                                    $handler
                                ) ? $handler : get_class($handler)) . "]"
                            );
                        }

                        if (is_string($handler))
                        {
                            if (is_a($handler, MiddleAction::class, true))
                            {
                                $handler = $handler::make();
                            }
                            $handler = new $handler;
                        }

                        $this->update->isHandled = true;
                        $handler->handleUpdate($this->update);

                        if ($this->update->isHandled)
                        {
                            break 2;
                        }
                    }

                    break;
                }
                catch (RepeatHandlingException $e)
                {
                    // Continue loop
                    continue;
                }
                catch (StopHandlingException $e)
                {
                    break;
                }
                catch (CancelHandlingException $e)
                {
                    return $this;
                }
            }

            if ($final)
            {
                $this->call($final);
            }

            $this->fire('final');

            foreach ($this->finallySaves as $record)
            {
                $record->save();
            }
        }
        finally
        {
            if ($this->stepRecord)
            {
                Step::setModel(null);
            }
        }

        return $this;
    }



    /**
     * Get current step handler
     *
     * @return StepHandlerPipe
     */
    public function step()
    {
        if ($this->stepRecord)
        {
            return new StepHandlerPipe($this->stepRecord);
        }

        throw new \InvalidArgumentException("Step model is not set");
    }

    /**
     * Get callback query control handler
     *
     * @param string $class
     * @return CallbackControlHandler
     */
    public function callback(string $class)
    {
        return new CallbackControlHandler($class);
    }

    /**
     * Get inline query control handler
     *
     * @param string $class
     * @return InlineControlHandler
     */
    public function inline(string $class)
    {
        return new InlineControlHandler($class);
    }

    /**
     * Get update handler for handling after a middle actions handled
     *
     * @param string $class
     * @param string $method
     * @param mixed  ...$args
     * @return MiddleActionHandledUpdateHandling
     */
    public function afterMiddles(string $class, string $method, ...$args)
    {
        return new MiddleActionHandledUpdateHandling(null, null, $class, $method, ...$args);
    }

}
