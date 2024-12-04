<?php

namespace Mmb\Console;

use Illuminate\Console\Command;
use Mmb\Core\BotChanneling;

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
            },
            delay: +$this->option('delay') ?? 0,
        );
    }
}
