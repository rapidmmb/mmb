<?php

namespace Mmb\Support\Providers;

use Closure;
use Illuminate\Support\ServiceProvider;
use Mmb\Action\Update\HandleFactory;
use Mmb\Action\Update\UpdateHandling;
use Mmb\Auth\AreaRegister;

class HandleServiceProvider extends ServiceProvider
{

    /**
     * Register default values
     *
     * @return void
     */
    public function register()
    {
        $this->registerHandlers();
        $this->registerExtend();
        $this->registerAreas();
    }


    /**
     * List of the handlers
     *
     * @var (class-string<UpdateHandling>|UpdateHandling)[]
     */
    protected array $handlers = [];

    /**
     * Map of extended handler and callback method name
     *
     * @var array<string, string>
     */
    protected array $extend = [];

    /**
     * List of areas
     *
     * @var string[]
     */
    protected array $areas = [];


    /**
     * Register the defined handlers
     *
     * @return void
     */
    public function registerHandlers()
    {
        $this->mergeHandlers($this->handlers);
    }

    /**
     * Register the defined extend
     *
     * @return void
     */
    public function registerExtend()
    {
        foreach ($this->extend as $class => $callback)
        {
            $this->extend($class, $this->$callback(...));
        }
    }

    /**
     * Register the defined areas
     *
     * @return void
     */
    public function registerAreas()
    {
        $this->mergeAreas($this->areas);
    }


    /**
     * Extend a handler
     *
     * @param string  $class
     * @param Closure $callback
     * @return void
     */
    public function extend(string $class, Closure $callback)
    {
        app()->resolving(HandleFactory::class, function (HandleFactory $factory) use ($class, $callback)
        {
            $factory->extend($class, $callback);
        });
    }

    /**
     * Extend a handler
     *
     * @param array  $handlers
     * @return void
     */
    public function mergeHandlers(array $handlers)
    {
        if (!$handlers)
        {
            return;
        }

        app()->resolving(HandleFactory::class, function (HandleFactory $factory) use ($handlers)
        {
            $factory->merge($handlers);
        });
    }

    /**
     * Merge areas
     *
     * @param array $areas
     * @return void
     */
    public function mergeAreas(array $areas)
    {
        if (!$areas)
        {
            return;
        }

        app()->resolving(AreaRegister::class, function () use ($areas)
        {
            foreach ($areas as $area)
            {
                app($area)->boot();
            }
        });
    }

}