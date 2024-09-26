<?php

namespace Mmb\Action\Update;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
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
     * @param class-string<T>  $class
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
     * @param Update $update
     * @param array  $mergedHandlers
     * @return void
     */
    public function handle(Update $update, array $mergedHandlers = [])
    {
        try
        {
            Container::getInstance()->instance(Update::class, $update);

            if (!$this->handlers && !$mergedHandlers)
            {
                throw new \Exception("Handlers is not set");
            }

            foreach(array_merge($mergedHandlers, $this->handlers) as $updateHandler)
            {
                /** @var UpdateHandler $updateHandler */
                $updateHandler = new $updateHandler($update);

                try
                {
                    $this->handleBy($update, $updateHandler);
                    break;
                }
                catch (HandlerNotMatchedException $e)
                {
                    // Continue handling
                    continue;
                }
            }
        }
        catch (\Throwable $e)
        {
            while (true)
            {
                try
                {
                    if ($e instanceof HttpResponseException)
                    {
                        ; // TODO
                    }

                    if ($e instanceof CallableException)
                    {
                        $e->invoke($update);
                        return;
                    }

                    report($e); // TODO
                    // app(ExceptionHandler::class)->report($e);
                    // app(ExceptionHandler::class)->render(request(), $e);
                    break;
                }
                catch (\Throwable $e)
                {
                    continue;
                }
            }
        }
    }

    public function handleBy(Update $update, UpdateHandler $updateHandler)
    {
        $class = get_class($updateHandler);

        $handler = new HandlerFactory($update->bot(), $update);

        $handler->collectionEvent('first', Arr::pluck($this->extends[$class] ?? [], 'firsts'));
        $handler->collectionEvent('last', Arr::pluck($this->extends[$class] ?? [], 'lasts'));

        foreach ($this->extends[$class] ?? [] as $extends)
        {
            foreach ($extends->handles as $name => $handles)
            {
                $handler->addInheritedHandlers($name, $handles);
            }
        }

        $handler->fire('first');
        $updateHandler->handle($handler);
    }

}