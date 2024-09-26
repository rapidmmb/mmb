<?php

namespace Mmb\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Mmb\Auth\AreaRegister;
use Mmb\Console;
use Mmb\Core\Bot;
use Mmb\Core\BotChanneling;
use Mmb\Core\Requests\Parser\ArgsParserFactory;
use Mmb\Core\Requests\Parser\DefaultArgsParser;
use Mmb\Core\Updates\Update;

class MmbServiceProvider extends ServiceProvider
{

    /**
     * Register app
     *
     * @return void
     */
    public function register()
    {
        $config = __DIR__ . '/../../config/mmb.php';
        $this->publishes([$config => base_path('config/mmb.php')], ['mmb']);
        $this->mergeConfigFrom($config, 'mmb');

        $this->app->singleton(AreaRegister::class);
        $this->app->singleton(ArgsParserFactory::class, fn() => new DefaultArgsParser());

        $this->registerBot();
        $this->registerDefaultUpdate();
        $this->registerAreas();
        $this->registerCommands();
    }

    /**
     * Register bot channeling and bot object using config
     *
     * @return void
     */
    public function registerBot()
    {
        $this->app->singleton(BotChanneling::class, function()
        {
            $driver = config('mmb.channeling');
            return new $driver(config('mmb.channels'));
        });

        $this->app->singleton(Bot::class, fn() => app(BotChanneling::class)->getDefaultBot());

        if(!($this->app instanceof CachesRoutes && $this->app->routesAreCached()))
        {
            app(BotChanneling::class)->defineRoutes();
        }
    }

    /**
     * Register default update
     *
     * @return void
     */
    public function registerDefaultUpdate()
    {
        $this->app->singleton(Update::class, fn() => new Update([]));
    }

    /**
     * Register area classes using config
     *
     * @return void
     */
    public function registerAreas()
    {
        foreach (config('mmb.areas', []) as $area)
        {
            app()->resolving(AreaRegister::class, function () use ($area)
            {
                app($area)->boot();
            });
        }
    }


    protected array $commands = [
        Console\SectionMakeCommand::class,
        Console\AreaMakeCommand::class,
        Console\MmbServeCommand::class,
        Console\MmbHandleUpdateCommand::class,
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
