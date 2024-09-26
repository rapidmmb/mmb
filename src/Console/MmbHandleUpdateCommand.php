<?php

namespace Mmb\Console;

use Illuminate\Console\Command;
use Mmb\Core\BotChanneling;
use Mmb\Core\Updates\Update;
use function Laravel\Prompts\error;

class MmbHandleUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mmb:handle-update {update : The json serialized real update} {--bot= : Channel name of the which bot you want to serve}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle the update';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // TODO
        $bot = app(BotChanneling::class)->getBot($this->option('bot') ?? 'default', null);

        $updateData = @json_decode($this->argument('update'), true);

        if (!$updateData)
        {
            error("Update data is not valid json format");
            return 1;
        }

        $update = Update::make($updateData, $bot, true);

        $update->handle();

        return 0;
    }
}