<?php

namespace Mmb\Tests\Action\OperatorService;

use Mmb\Action\Operator\OperatorFailed;
use Mmb\Action\Operator\OperatorService;
use Mmb\Action\Section\Section;
use Mmb\Context;
use Mmb\Tests\TestCase;

class OperatorServiceTest extends TestCase
{

    public function test_service()
    {
        $this->assertTrue(_TestOperator::make($this->context)->test());
    }

    public function test_service_error_service()
    {
        _TestOperator::make($this->context)
            ->fail(function (OperatorFailed $failed) {
                $this->assertSame(100, $failed->tag);
                $this->assertSame("Failed", $failed->failMessage);
            })
            ->failing();
    }

    public function test_service_then_service()
    {
        _TestOperator::make($this->context)
            ->then(function ($result) {
                $this->assertSame(true, $result);
            })
            ->test();

        $this->assertSame('Error Occurred',
            _TestOperator::make($this->context)
                ->fail(function () {
                    return 'Error Occurred';
                })
                ->then(function () {
                    $this->assertSame(true, false);
                })
                ->failing()
        );
    }

    public function test_service_not_catching_error()
    {
        $this->expectException(OperatorFailed::class);

        _TestOperator::make($this->context)->failing();
    }

    public function test_notify()
    {
        $this->assertSame(true, _TestOperator::make($this->context)->testNotify());
    }

    public function test_advanced_notify()
    {
        $this->assertNotSame($this->context, _TestOperator::make($this->context)->testAdvancedNotify());
    }

    public function test_handle_event_errors()
    {
        $this->expectException(OperatorFailed::class);

        _TestOperator::make($this->context)->testHandleEventError();
    }

}

class _TestOperator extends OperatorService
{
    public function test()
    {
        return true;
    }

    public function failing()
    {
        $this->fail(100, "Failed");
        return null;
    }

    public function testNotify()
    {
        return $this->event(_TestSection::class)->notifyTest();
    }

    public function testAdvancedNotify()
    {
        return $this->event(_TestSection::class)->withContext(new Context())->getContext();
    }

    public function testHandleEventError()
    {
        $this->event(_TestSection::class)
            ->catch(function () {
                $this->fail('Custom', 'Custom');
            })
            ->notifyError();
    }
}

class _TestSection extends Section
{
    public function notifyTest()
    {
        return true;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function notifyError()
    {
        throw new \Exception();
    }
}
