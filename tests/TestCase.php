<?php

namespace Mmb\Tests;

use Mmb\Context;
use Mmb\Core\Bot;
use Mmb\Core\InternalBotInfo;
use Mmb\Core\Requests\TelegramRequest;
use Mmb\Core\Updates\Update;
use Mmb\Providers\MmbServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    public Context $context;

    protected function setUp() : void
    {
        parent::setUp();

        app()->singleton(Bot::class, fn() => new Bot(new InternalBotInfo('12345', 'test', null, null)));

        $this->context = new Context();
        $this->context->bot = bot();
        $this->context->update = new Update([]);
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
        return $this->context->bot;
    }
}
