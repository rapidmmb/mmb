<?php

namespace Mmb\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Mmb\Action\Memory\Step;
use Mmb\Core\Bot;
use Mmb\Core\BotChanneling;
use Mmb\Core\Updates\Update;
use Mmb\Support\Db\ModelFinder;
use Symfony\Component\Console\Input\InputOption;

class MmbServeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mmb:serve {--bot= : Channel name of the which bot you want to serve} {--delay= : Delay value in milliseconds, between update receiving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve mmb robot by listening to telegram';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // TODO
        $bot = app(BotChanneling::class)->getBot($this->option('bot') ?? 'default', null);

        \Laravel\Prompts\info("Mmb is listening to updates now...");

        $bot->loopUpdates(
            received: function ()
            {
                \Laravel\Prompts\info(sprintf("New update received at %s", date('H:i:s')));

                ModelFinder::clear();
                Step::setModel(null);
            },
            delay: +$this->option('delay') ?? 100,
        );
    }
}
