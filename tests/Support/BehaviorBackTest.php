<?php

namespace Mmb\Tests\Support;

use Mmb\Action\Action;
use Mmb\Auth\AreaRegister;
use Mmb\Context;
use Mmb\Support\Behavior\BehaviorFactory;
use Mmb\Support\Behavior\Contracts\BackSystem;
use Mmb\Support\Behavior\Exceptions\BackActionNotDefinedException;
use Mmb\Tests\TestCase;

class BehaviorBackTest extends TestCase
{

    public function test_back_define_exception()
    {
        app()->singleton(AreaRegister::class);
        app()->singleton(BehaviorFactory::class);

        $this->expectException(BackActionNotDefinedException::class);
        app(BehaviorFactory::class)->back();
    }

    public function test_fixed_back()
    {
        app()->singleton(AreaRegister::class);
        app()->singleton(BehaviorFactory::class);

        app(AreaRegister::class)->putForClass('Test', 'back', _BehaviorBackTestAction::class);

        _BehaviorBackTestAction::$isCalled = false;
        app(BehaviorFactory::class)->back('Test');
        $this->assertTrue(_BehaviorBackTestAction::$isCalled);
    }

    public function test_custom_back_system()
    {
        app()->singleton(AreaRegister::class);
        app()->singleton(BehaviorFactory::class);

        app(AreaRegister::class)->putForClass('Test', 'back-system', new _BehaviorBackTestCustomBackSystem());

        _BehaviorBackTestCustomBackSystem::$isCalled = false;
        app(BehaviorFactory::class)->back('Test');
        $this->assertTrue(_BehaviorBackTestCustomBackSystem::$isCalled);
    }

}

class _BehaviorBackTestAction extends Action
{
    public static bool $isCalled;

    public function main()
    {
        static::$isCalled = true;
    }
}

class _BehaviorBackTestCustomBackSystem implements BackSystem
{
    public static bool $isCalled;

    public function back(Context $context, array $args, array $dynamicArgs) : void
    {
        static::$isCalled = true;
    }
}
