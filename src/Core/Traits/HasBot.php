<?php

namespace Mmb\Core\Traits;

use Mmb\Core\Bot;

trait HasBot
{

    private ?Bot $targetBot = null;

    public function setTargetBot(?Bot $bot)
    {
        $this->targetBot = $bot;
    }

    public function bot() : Bot
    {
        return $this->targetBot ?? app(Bot::class);
    }

}