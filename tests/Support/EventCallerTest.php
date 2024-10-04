<?php

namespace Mmb\Tests\Support;

use Mmb\Support\Caller\EventCaller;
use Mmb\Tests\TestCase;

class EventCallerTest extends TestCase
{

    public function test_linear_event()
    {
        $called = 0;

        EventCaller::fire(
            ['call' => EventCaller::CALL_LINEAR],
            [
                function () use (&$called)
                {
                    $called |= 1;
                },
                function () use (&$called)
                {
                    $called |= 2;
                },
            ],
            [],
            [],
            function () use (&$called)
            {
                $called |= 4;
            },
        );

        $this->assertSame(1|2|4, $called);
    }

    public function test_call_until_true()
    {
        $called = 0;

        EventCaller::fire(
            ['call' => EventCaller::CALL_UNTIL_TRUE],
            [
                function () use (&$called)
                {
                    $called |= 1;
                    return 0;
                },
                function () use (&$called)
                {
                    $called |= 2;
                    return 1;
                },
                function () use (&$called)
                {
                    $called |= 4;
                },
            ],
            [],
            [],
            function () use (&$called)
            {
                $called |= 8;
            },
        );

        $this->assertSame(1|2, $called);
    }

    public function test_call_until_not_null()
    {
        $called = 0;

        EventCaller::fire(
            ['call' => EventCaller::CALL_UNTIL_NOT_NULL],
            [
                function () use (&$called)
                {
                    $called |= 1;
                    return null;
                },
                function () use (&$called)
                {
                    $called |= 2;
                    return [];
                },
                function () use (&$called)
                {
                    $called |= 4;
                },
            ],
            [],
            [],
            function () use (&$called)
            {
                $called |= 8;
            },
        );

        $this->assertSame(1|2, $called);
    }

    public function test_call_until_false()
    {
        $called = 0;

        EventCaller::fire(
            ['call' => EventCaller::CALL_UNTIL_FALSE],
            [
                function () use (&$called)
                {
                    $called |= 1;
                    return 1;
                },
                function () use (&$called)
                {
                    $called |= 2;
                    return false;
                },
                function () use (&$called)
                {
                    $called |= 4;
                },
            ],
            [],
            [],
            function () use (&$called)
            {
                $called |= 8;
            },
        );

        $this->assertSame(1|2, $called);
    }

    public function test_call_until_actual_false()
    {
        $called = 0;

        EventCaller::fire(
            ['call' => EventCaller::CALL_UNTIL_ACTUAL_FALSE],
            [
                function () use (&$called)
                {
                    $called |= 1;
                    return 0;
                },
                function () use (&$called)
                {
                    $called |= 2;
                    return false;
                },
                function () use (&$called)
                {
                    $called |= 4;
                },
            ],
            [],
            [],
            function () use (&$called)
            {
                $called |= 8;
            },
        );

        $this->assertSame(1|2, $called);
    }

    public function test_call_builder()
    {
        $result = EventCaller::fire(
            ['call' => EventCaller::CALL_BUILDER],
            [
                function ($i)
                {
                    return $i | 1;
                },
                function ($i)
                {
                    return $i | 2;
                },
                function ($i)
                {
                    return $i | 4;
                },
            ],
            [0]
        );

        $this->assertSame(1|2|4, $result);
    }

    public function test_call_builder_without_argument()
    {
        $this->expectException(\InvalidArgumentException::class);

        EventCaller::fire(
            ['call' => EventCaller::CALL_BUILDER],
            [
                function ()
                {
                    return 'Foo';
                },
            ],
            []
        );
    }

    public function test_call_builder_with_many_arguments()
    {
        $result = EventCaller::fire(
            ['call' => EventCaller::CALL_BUILDER],
            [
                function ($i, $bool)
                {
                    return $bool ? $i | 1 : 0;
                },
                function ($i, $bool)
                {
                    return $bool ? $i | 2 : 0;
                },
                function ($i, $bool)
                {
                    return $bool ? $i | 4 : 0;
                },
            ],
            [0, true]
        );

        $this->assertSame(1|2|4, $result);
    }

    public function test_call_multiple_builders()
    {
        $result = EventCaller::fire(
            ['call' => EventCaller::CALL_MULTIPLE_BUILDERS],
            [
                function ($i, $j)
                {
                    return [$i | 1, $j * 2];
                },
                function ($i, $j)
                {
                    return [$i | 2, $j * 3];
                },
                function ($i, $j)
                {
                    return [$i | 4, $j * 4];
                },
            ],
            [0, 1]
        );

        $this->assertSame([1|2|4, 2*3*4], $result);
    }

    public function test_call_pipeline()
    {
        $result = EventCaller::fire(
            ['call' => EventCaller::CALL_PIPELINE],
            [
                function ($i, $next)
                {
                    return $next($i | 1);
                },
                function ($i, $next)
                {
                    return $i | 2; // Stop the chain
                },
                function ($i, $next)
                {
                    return $next($i | 4);
                },
            ],
            [0]
        );

        $this->assertSame(1|2, $result);
    }

    public function test_call_pipeline_with_zero_argument()
    {
        $this->expectException(\InvalidArgumentException::class);

        EventCaller::fire(
            ['call' => EventCaller::CALL_PIPELINE],
            [],
            []
        );
    }

    public function test_call_pipeline_with_many_argument()
    {
        $this->expectException(\InvalidArgumentException::class);

        EventCaller::fire(
            ['call' => EventCaller::CALL_PIPELINE],
            [],
            [1, 2, 3]
        );
    }




    public function test_return_last()
    {
        $result = EventCaller::fire(
            [
                'call' => EventCaller::CALL_LINEAR,
                'return' => EventCaller::RETURN_LAST,
            ],
            [
                function ()
                {
                    return 1;
                },
                function ()
                {
                    return 2;
                },
            ],
            [],
        );

        $this->assertSame(2, $result);
    }

    public function test_return_void()
    {
        $result = EventCaller::fire(
            [
                'call' => EventCaller::CALL_LINEAR,
                'return' => EventCaller::RETURN_VOID,
            ],
            [
                function ()
                {
                    return 1;
                },
                function ()
                {
                    return 2;
                },
            ],
            [],
        );

        $this->assertSame(null, $result);
    }

    public function test_return_first_true()
    {
        $result = EventCaller::fire(
            [
                'call' => EventCaller::CALL_LINEAR,
                'return' => EventCaller::RETURN_FIRST_TRUE,
            ],
            [
                function ()
                {
                    return false;
                },
                function ()
                {
                    return 'Foo';
                },
                function ()
                {
                    return 'Bar';
                },
            ],
            [],
        );

        $this->assertSame('Foo', $result);
    }

    public function test_return_all()
    {
        $result = EventCaller::fire(
            [
                'call' => EventCaller::CALL_LINEAR,
                'return' => EventCaller::RETURN_ALL,
            ],
            [
                function ()
                {
                    return 1;
                },
                function ()
                {
                    return 2;
                },
                function ()
                {
                    return 3;
                },
            ],
            [],
        );

        $this->assertSame([1, 2, 3], $result);
    }

}