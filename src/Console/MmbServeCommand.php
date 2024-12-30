<?php

namespace Mmb\Console;

use Illuminate\Console\Command;
use Mmb\Core\BotChanneling;
use Mmb\Core\UpdateLoopHandler;
use Mmb\Core\Updates\Update;

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

        (new UpdateLoopHandler(
            bot: $bot,
            received: function (Update $update) {
                $this->log("New update received", $update);
            },
            idle: function () {
                $this->log("No updates found :)");
            },
            handling: function (Update $update) {
                $this->log("Handling update", $update);
            },
            handled: function (Update $update) {
                $this->log("Update handled", $update);
            },
            timeout: 120,
            delay: +$this->option('delay') ?? 0,
        ))->run()->wait();
    }

    protected function log(string $message, ?Update $update = null)
    {
        $updateInfo = $update ? (
            "#" . $update->id . (
                $update->getChat() ?
                    " Chat { " . $update->getChat()->id . " }" :
                    ""
            )
        ) : null;

        $this->info("[" . date('H:i:s') . "] " . $message . ($updateInfo ? ": " . $updateInfo : null));
    }
}
