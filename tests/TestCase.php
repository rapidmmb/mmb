<?php

namespace Mmb\Tests;

use Mmb\Core\Bot;
use Mmb\Core\Requests\TelegramRequest;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $this->createApplication();
    }

    public function createApplication()
    {
        app()->singleton(Bot::class, fn() => new Bot('1307165749:AAELc518VsivWkwMBOu2I6PHoEm1R0hF-Io'));
        TelegramRequest::appendOptions([
            'proxy' => '192.168.96.216:10809',
        ]);
    }

    public function bot() : Bot
    {
        return app(Bot::class);
    }
}
