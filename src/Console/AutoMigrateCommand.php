<?php

namespace Mmb\Console;

use Illuminate\Console\Command;

class AutoMigrateCommand extends Command
{

    protected $name = 'migrate:auto';

    protected $description = 'Run auto migrations';

    public function handle()
    {
        $this->output->info("Running migrations...");

        $migrations = config('mmb.auto-migration');

        if(!$migrations)
        {
            $this->output->error("No migrations found! config 'mmb.auto-migration' is not defined");
            return;
        }

        foreach($migrations as $migration)
        {
            $this->output->text("Running $migration...");
            $migration = new $migration();
            $migration->run();
        }

        $this->output->success("Auto migration ran successfully!");
    }

}
