<?php

namespace Mmb\Action\Update;

use Mmb\Core\Bot;
use Mmb\Core\Updates\Update;

abstract class HandleCondition
{

    public Update $update;

    public Bot $bot;

    public function __construct(
        public $action,
    )
    {
    }

    public function check()
    {
        return false;
    }

    public function handle()
    {
        return false;
    }

}
