<?php

namespace Mmb\Tests;

use Mmb\Core\Bot;
use Mmb\Core\InternalBotInfo;
use Mmb\Core\Requests\TelegramRequest;
use Mmb\Core\Updates\Update;
use Mmb\Providers\MmbServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        app()->singleton(Bot::class, fn() => new Bot(new InternalBotInfo('12345', 'test', null, null)));
        app()->singleton(Update::class, fn() => new Update([]));
    }

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            MmbServiceProvider::class,
        ];
    }

    public function bot() : Bot
    {
        return app(Bot::class);
    }
}
