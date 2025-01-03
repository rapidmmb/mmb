<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Support\Arr;
use Mmb\Action\Action;
use Mmb\Action\Memory\StepHandlerPipe;
use Mmb\Action\Middle\MiddleActionHandledUpdateHandling;
use Mmb\Action\Section\Controllers\CallbackControlGroupHandler;
use Mmb\Action\Section\Controllers\CallbackControlHandler;
use Mmb\Action\Section\Controllers\InlineControlGroupHandler;
use Mmb\Action\Section\Controllers\InlineControlHandler;
use Mmb\Context;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\HasEvents;
use Mmb\Support\Step\Contracts\ConvertableToStepper;
use Mmb\Support\Step\Contracts\Stepper;

class HandlerFactory
{
    use HasEvents;

    public function __construct(
        public Context $context,
    )
    {
    }

    protected array $dynamicArgs = [];

    public function getEventDynamicArgs(string $event): array
    {
        return [
            'update' => $this->context->update,
            'bot' => $this->context->bot,
            ...$this->dynamicArgs,
            ...$this->getEventDefaultDynamicArgs($event)
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
        $dynamicArgs += $this->getEventDynamicArgs('*');

        return Caller::invoke($this->context, $callback, $args, $dynamicArgs);
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

    /**
     * @param string $on
     * @param array $events
     * @return void
     */
    public function collectionEvent(string $on, array $events)
    {
        $this->listen($on, function () use ($events) {
            foreach ($events as $event) {
                $this->call($event, $this);
            }
        });
    }

    /**
     * Fire all events in anywhere.
     *
     * This value can extend.
     *
     * @param string $event
     * @return $this
     */
    public function extends(string $event)
    {
        $this->fire($event);

        return $this;
    }


    protected array $inherits = [];

    /**
     * Add inherited handlers
     *
     * @param string $name
     * @param Closure $handlerResolver
     * @return $this
     */
    public function addInheritedHandlers(string $name, Closure $handlerResolver)
    {
        @$this->inherits[$name][] = $handlerResolver;
        return $this;
    }

    /**
     * Get inherited handlers
     *
     * @param string $name
     * @return array
     */
    public function inherit(string $name = 'default')
    {
        $items = [];

        foreach ($this->inherits[$name] ?? [] as $inherit) {
            array_push($items, ...$this->call($inherit, $this));
        }

        return $items;
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
        if (!$this->value($condition)) {
            $this->fallback();
        }

        return $this;
    }

    protected array $finallySaves = [];

    /**
     * Use a record
     *
     * @param string $model Model type
     * @param mixed|Closure $by Get value to search
     * @param Closure|null $create Create when record not found
     * @param Closure|null $validate Validate the record
     * @param string $key Get key to find the model
     * @param bool $useCache Use cache to search model
     * @param string $name Record name that passed in each callback
     * @param bool $asCurrent Set record as current record
     * @param bool $setUser Set as guard user
     * @param bool $autoSave Auto save after update handled
     * @return $this
     */
    public function record(
        string   $model,
        mixed    $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string   $key = '',
        bool     $useCache = true,
        string   $name = 'record',
        bool     $asCurrent = true,
        bool     $setUser = false,
        bool     $autoSave = false,
    )
    {
        $by = $this->value($by);

        if ($by === null) {
            $this->fallback();
        }

        if ($useCache) {
            $record = $this->context->finder->findBy($model, $key, $by);
        } else {
            $record = $model::query()->where($key ?: app($model)->getKey())->first();
        }

        if (!$record && $create) {
            $record = $this->call($create);
            if (is_array($record)) {
                $record = $model::create($record);
            }

            if ($record && $useCache) {
                $this->context->finder->store($record);
            }
        }

        if (!$record) {
            $this->fallback();
        }

        if ($validate && !$this->call($validate, $record)) {
            $this->fallback();
        }

        if ($asCurrent) {
            $this->context->finder->storeCurrent($record);
        }

        if ($setUser) {
            $this->context->bot->guard()->setUser($record);
        }

        if ($autoSave) {
            $this->finallySaves[] = $record;
        }

        $this->dynamicArgs[$name] = $record;

        return $this;
    }

    /**
     * Use a record as not-current record
     *
     * @param string $model Model type
     * @param mixed|Closure $by Get value to search
     * @param Closure|null $create Create when record not found
     * @param Closure|null $validate Validate the record
     * @param string $key Get key to find the model
     * @param bool $useCache Use cache to search model
     * @param string $name Record name that passed in each callback
     * @param bool $setUser Set as guard user
     * @param bool $autoSave Auto save after update handled
     * @return $this
     */
    public function recordOther(
        string   $model,
        mixed    $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string   $key = '',
        bool     $useCache = true,
        string   $name = 'record',
        bool     $setUser = false,
        bool     $autoSave = false,
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

    protected ?Stepper $stepperRecord = null;

    /**
     * Use a step record
     *
     * @param string $model Model type
     * @param mixed|Closure $by Get value to search
     * @param Closure|null $create Create when record not found
     * @param Closure|null $validate Validate the record
     * @param string $key Get key to find the model
     * @param bool $useCache Use cache to search model
     * @param string $name Record name that passed in each callback
     * @param bool $asCurrent Set record as current record
     * @param bool $setUser Set as guard user
     * @param bool $autoSave Auto save after update handled
     * @return $this
     */
    public function recordStep(
        string   $model,
        mixed    $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string   $key = '',
        bool     $useCache = true,
        string   $name = 'record',
        bool     $asCurrent = true,
        bool     $setUser = false,
        bool     $autoSave = false,
    )
    {
        $this->record(...func_get_args());
        $this->stepAs($this->dynamicArgs[$name]);

        return $this;
    }

    /**
     * Use the user model
     *
     * @param string $model Model type
     * @param mixed|Closure $by Get value to search
     * @param Closure|null $create Create when record not found
     * @param Closure|null $validate Validate the record
     * @param string $key Get key to find the model
     * @param bool $useCache Use cache to search model
     * @param string $name Record name that passed in each callback
     * @param bool $autoSave Auto save after update handled
     * @return $this
     */
    public function recordUser(
        string   $model,
        mixed    $by,
        ?Closure $create = null,
        ?Closure $validate = null,
        string   $key = '',
        bool     $useCache = true,
        string   $name = 'record',
        bool     $autoSave = false,
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

        if ($this->dynamicArgs[$name] instanceof Stepper) {
            $this->stepAs($this->dynamicArgs[$name]);
        }

        return $this;
    }

    /**
     * Set step record as
     *
     * @param string|Stepper|ConvertableToStepper|Closure $value
     * @return $this
     */
    public function stepAs(string|Stepper|ConvertableToStepper|Closure $value)
    {
        if ($value instanceof ConvertableToStepper) {
            $value = $value->toStepper();
        }

        $value = $this->value($value);

        if (is_string($value)) {
            $value = $this->dynamicArgs[$value];
        }

        $this->stepperRecord = $value;

        return $this;
    }

    protected bool $isHandled = false;

    /**
     * Handle the update
     *
     * @param array $handlers
     * @param Closure|null $final
     * @return $this
     */
    public function handle(
        array    $handlers,
        ?Closure $final = null,
    )
    {
        if ($this->isHandled) {
            throw new \InvalidArgumentException("This handler has been handled before");
        }

        $this->isHandled = true;

        $this->fire('last');

        $handlers = Arr::flatten($handlers);

        try {
            $this->context->update->isHandled = false;

            if ($this->stepperRecord) {
                $this->context->stepFactory->setModel($this->stepperRecord);
                $this->context->stepFactory->fire('begin', $this->context, $this->context->update);
            }

            if (!$this->context->update->isHandled) {
                while (true) {
                    $this->context->update->isHandled = false;

                    try {
                        foreach ($handlers as $handler) {
                            if ($handler instanceof Closure) {
                                $handler = $handler();
                            }

                            if ($handler === null) {
                                continue;
                            }

                            if (!is_a($handler, UpdateHandling::class, true)) {
                                throw new \TypeError(
                                    "Expected [" . UpdateHandling::class . "], given [" . (is_string(
                                        $handler
                                    ) ? $handler : get_class($handler)) . "]"
                                );
                            }

                            if (is_string($handler)) {
                                if (is_a($handler, Action::class, true)) {
                                    $handler = $handler::makeByContext($this->context);
                                }
                                else {
                                    $handler = new $handler;
                                }
                            }

                            $this->context->update->isHandled = true;
                            $handler->handleUpdate($this->context, $this->context->update);

                            if ($this->context->update->isHandled) {
                                break 2;
                            }
                        }

                        break;
                    } catch (RepeatHandlingException $e) {
                        // Continue loop
                        continue;
                    } catch (StopHandlingException $e) {
                        break;
                    } catch (CancelHandlingException $e) {
                        return $this;
                    }
                }
            }

            $this->context->stepFactory?->fire('end', $this->context, $this->context->update);

            if ($final) {
                $this->call($final);
            }

            $this->fire('final');

            foreach ($this->finallySaves as $record) {
                $record->save();
            }
        } finally {
            if ($this->stepperRecord) {
                $this->context->stepFactory->setModel(null);
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
        if ($this->stepperRecord) {
            return new StepHandlerPipe($this->stepperRecord);
        }

        throw new \InvalidArgumentException("Step model is not set");
    }

    /**
     * Get callback query control handler
     *
     * @param string $class
     * @param string ...$classes
     * @return CallbackControlHandler|CallbackControlGroupHandler
     */
    public function callback(string $class, string ...$classes)
    {
        if ($classes) {
            return new CallbackControlGroupHandler([$class, ...$classes]);
        }

        return new CallbackControlHandler($class);
    }

    /**
     * Get inline query control handler
     *
     * @param string $class
     * @param string ...$classes
     * @return InlineControlHandler|InlineControlGroupHandler
     */
    public function inline(string $class, string ...$classes)
    {
        if ($classes) {
            return new InlineControlGroupHandler([$class, ...$classes]);
        }

        return new InlineControlHandler($class);
    }

    /**
     * Get update handler for handling after a middle actions handled
     *
     * @param string $class
     * @param string $method
     * @param mixed ...$args
     * @return MiddleActionHandledUpdateHandling
     */
    public function afterMiddles(string $class, string $method, ...$args)
    {
        return new MiddleActionHandledUpdateHandling(null, null, $class, $method, ...$args);
    }

}
