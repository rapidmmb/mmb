<?php

namespace Mmb\Action\Memory;

class StepEvent
{

    public static function fire(string $event, ...$args)
    {
        return Step::get()?->fire($event, ...$args);
    }

}