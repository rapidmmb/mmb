<?php

namespace Mmb\Providers;

use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Mmb\Action\Filter\Filter;
use Mmb\Auth\AreaRegister;
use Mmb\Console\AutoMigrateCommand;
use Mmb\Console\SectionMakeCommand;
use Mmb\Core\BotChanneling;
use Mmb\Http\Controllers\WebhookController;
use Mmb\Core\Requests\Parser\ArgsParserFactory;
use Mmb\Core\Requests\Parser\DefaultArgsParser;
use Mmb\Core\Bot;
use PhpParser\Node\Expr\Closure;

class MmbServiceProvider extends ServiceProvider
{

    /**
     * Area classes
     *
     * @var array
     */
    protected $areas = [];

    /**
     * Args parser class
     *
     * @var string
     */
    protected $argsParser = DefaultArgsParser::class;

    public final function register()
    {
        $this->app->singleton(AreaRegister::class);
        $this->app->singleton(ArgsParserFactory::class, fn() => new ($this->argsParser)());

        $this->booted(function()
        {
            $this->app->singleton(BotChanneling::class, function()
            {
                $driver = $this->getConfig('channeling');
                return new $driver($this->getConfig('channels'));
            });

            $this->app->singleton(Bot::class, fn() => app(BotChanneling::class)->getDefaultBot());

            if(!($this->app instanceof CachesRoutes && $this->app->routesAreCached()))
            {
                app(BotChanneling::class)->defineRoutes();
            }

            $this->registerAreas();
            $this->registerCommands();
        });
    }

    /**
     * Register configs
     *
     * @param string $path
     * @return void
     */
    public function registerConfigs(string $path)
    {
        $this->mergeConfigFrom($path, 'mmb');
    }

    /**
     * Get config value
     *
     * @param string $key
     * @return mixed
     */
    public function getConfig(string $key)
    {
        return config()->get('mmb.' . $key);
    }

    /**
     * Register areas
     *
     * @return void
     */
    public function registerAreas()
    {
        foreach($this->areas as $area)
        {
            $this->registerArea($area);
        }
    }

    /**
     * Register an area
     *
     * @param string $class
     * @return void
     */
    public function registerArea(string $class)
    {
        app($class)->boot();
    }

    /**
     * Register routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        Route::post($this->route, [WebhookController::class, 'update']);
    }

    /**
     * Register filter fail handler
     *
     * @param string|Closure $handler
     * @return void
     */
    public function registerFailHandler(string|Closure $handler)
    {
        Filter::registerFailHandler($handler);
    }

    protected array $commands = [
        SectionMakeCommand::class,
        AutoMigrateCommand::class,
    ];

    /**
     * Register commands
     *
     * @return void
     */
    public function registerCommands()
    {
        Artisan::starting(function($artisan)
        {
            foreach($this->commands as $command)
            {
                $artisan->resolveCommands(app($command));
            }
        });
    }

}
