<?php

namespace Mmb\Tests\Action\Service;

use Mmb\Action\Section\Section;
use Mmb\Action\Service\Service;
use Mmb\Action\Service\ServiceEvent;
use Mmb\Action\Service\ServiceFailed;
use Mmb\Tests\TestCase;

class ServiceTest extends TestCase
{

    public function test_service()
    {
        $this->assertTrue(_TestService::make()->test());
    }

    public function test_service_error_service()
    {
        _TestService::make()
            ->error(function (ServiceFailed $failed)
            {
                $this->assertSame("Failed", $failed->failMessage);
                $this->assertSame(100, $failed->failCode);
            })
            ->failing();
    }

    public function test_service_then_service()
    {
        _TestService::make()
            ->then(function ($result)
            {
                $this->assertSame(true, $result);
            })
            ->test();
    }

    public function test_service_not_catching_error()
    {
        $this->expectException(ServiceFailed::class);

        _TestService::make()->failing();
    }

    public function test_notify()
    {
        $this->assertSame(true, _TestService::make()->testNotify());
    }

    public function test_advanced_notify()
    {
        $this->assertSame('Foo', _TestService::make()->testAdvancedNotify());
    }

}

class _TestService extends Service
{
    public function test()
    {
        return true;
    }

    public function failing()
    {
        $this->error("Failed", 100);
    }

    public function testNotify()
    {
        return $this->notifyMe(_TestSection::class, 'test')->result;
    }

    public function testAdvancedNotify()
    {
        return $this->notifyMe(_TestSection::class, 'advanced')->result;
    }
}

class _TestSection extends Section
{
    public function notifyTest()
    {
        return true;
    }

    public function notifyAdvanced(ServiceEvent $event)
    {
        $event->result = 'Foo';
    }
}
