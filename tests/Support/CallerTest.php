<?php

namespace Mmb\Tests\Support;

use Illuminate\Support\Str;
use Mmb\Action\Action;
use Mmb\Support\Caller\Caller;
use Mmb\Tests\TestCase;

class CallerTest extends TestCase
{

    public function test_simple_invoke()
    {
        $this->assertSame('Foo', Caller::invoke($this->context, fn() => 'Foo', []));
        $this->assertSame('FOO', Caller::invoke($this->context, 'strtoupper', ['foo']));
        $this->assertSame('FOO', Caller::invoke($this->context, [Str::class, 'upper'], ['foo']));
    }

    public function test_arguments()
    {
        $fn = fn($c, $b, $a) => $this->assertSame(['A', 'B', 'C'], [$a, $b, $c]);
        Caller::invoke($this->context, $fn, ['C', 'B', 'A']);

        $fn = fn($value) => $this->assertIsCallable($value);
        $arg = fn() => 'The Function';
        Caller::invoke($this->context, $fn, [$arg]);

        $fn = fn($a, $b) => $this->assertSame(['A', 'B'], [$a, $b]);
        Caller::invoke($this->context, $fn, ['b' => 'B', 'a' => 'A']);
    }

    public function test_dynamic_arguments()
    {
        $fn = fn() => $this->assertTrue(true);
        Caller::invoke($this->context, $fn, [], ['a' => 'Foo', 'b' => 'Bar']);

        $fn = fn($bar) => $this->assertSame('Bar', $bar);
        Caller::invoke($this->context, $fn, [], ['foo' => 'Foo', 'bar' => 'Bar']);

        $fn = fn($foo, $bar) => $this->assertSame(['Foo', 'Bar'], [$foo, $bar]);
        Caller::invoke($this->context, $fn, ['Foo'], ['foo' => 'Other Foo', 'bar' => 'Bar']);

        $fn = fn($value) => $this->assertSame('Value', $value);
        Caller::invoke($this->context, $fn, [], ['value' => fn() => 'Value']);
    }

    public function test_invoke_actions()
    {
        $action = new class($this->context) extends Action {
            public function main()
            {
                return 'Main';
            }

            public function foo()
            {
                return 'Foo';
            }

            public function bar($value)
            {
                return $value;
            }
        };

        $this->assertSame('Main', Caller::invokeAction($this->context, $action, []));
        $this->assertSame('Main', Caller::invokeAction($this->context, get_class($action), []));
        $this->assertSame('Main', Caller::invokeAction($this->context, [$action], []));
        $this->assertSame('Main', Caller::invokeAction($this->context, [$action, 'main'], []));
        $this->assertSame('Foo', Caller::invokeAction($this->context, [$action, 'foo'], []));
        $this->assertSame('Bar', Caller::invokeAction($this->context, [$action, 'bar'], ['Bar']));
        $this->assertSame('Bar', Caller::invokeAction($this->context, [$action, 'bar', 'Bar'], []));
    }

}