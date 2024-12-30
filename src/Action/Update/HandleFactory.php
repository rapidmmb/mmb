<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Mmb\Action\Memory\StepFactory;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Exceptions\CallableException;

class HandleFactory
{

    protected array $handlers = [];

    /**
     * @template T of UpdateHandler
     *
     * @param class-string<T> $class
     * @return void
     */
    public function add(string $class)
    {
        $this->handlers[] = $class;
    }

    /**
     * @template T of UpdateHandler
     *
     * @param class-string<T>[] $classes
     * @return void
     */
    public function merge(array $classes)
    {
        array_push($this->handlers, ...$classes);
    }


    /**
     * @var HandlerExtends[][]
     */
    protected array $extends = [];

    /**
     * @template T of UpdateHandler
     *
     * @param class-string<T> $class
     * @param Closure(HandlerExtends $extends): void $callback
     * @return void
     */
    public function extend(string $class, Closure $callback)
    {
        $callback($extends = new HandlerExtends());

        @$this->extends[$class][] = $extends;
    }


    /**
     * Handle the update
     *
     * @param Context $context
     * @param Update $update
     * @param array $mergedHandlers
     * @return void
     */
    public function handle(Context $context, Update $update, array $mergedHandlers = [])
    {
        try {
//            Container::getInstance()->instance(Update::class, $update); todo remove
            $context->update = $update;

            if (!$this->handlers && !$mergedHandlers) {
                throw new \Exception("Handlers is not set");
            }

            foreach (array_merge($mergedHandlers, $this->handlers) as $updateHandler) {
                /** @var UpdateHandler $updateHandler */
                $updateHandler = $updateHandler::makeByContext($context);

                try {
                    $this->handleBy($context, $updateHandler);
                    break;
                } catch (HandlerNotMatchedException $e) {
                    // Continue handling
                    continue;
                }
            }
        } catch (\Throwable $e) {
            while (true) {
                try {
                    if ($e instanceof HttpResponseException) {
                        ; // TODO
                    }

                    if ($e instanceof CallableException) {
                        $e->invoke($context);
                        return;
                    }

                    report($e); // TODO
                    // app(ExceptionHandler::class)->report($e);
                    // app(ExceptionHandler::class)->render(request(), $e);
                    break;
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }
    }

    public function handleBy(Context $context, UpdateHandler $updateHandler)
    {
        $class = get_class($updateHandler);

        $handler = new HandlerFactory($context);

        $handler->collectionEvent('first', Arr::flatten(Arr::pluck($this->extends[$class] ?? [], 'firsts')));
        $handler->collectionEvent('last', Arr::flatten(Arr::pluck($this->extends[$class] ?? [], 'lasts')));

        foreach ($this->extends[$class] ?? [] as $extends) {
            foreach ($extends->handles as $name => $handles) {
                foreach ($handles as $handle) {
                    $handler->addInheritedHandlers($name, $handle);
                }
            }

            foreach ($extends->events as $name => $events) {
                $handler->collectionEvent($name, $events);
            }
        }

        $handler->fire('first');
        $updateHandler->handle($handler);
    }

}