<?php

namespace Mmb\Tests\Supports;

use Illuminate\Support\Str;
use Mmb\Action\Action;
use Mmb\Support\Caller\Caller;
use Mmb\Tests\TestCase;

class CallerTest extends TestCase
{

    public function test_simple_invoke()
    {
        $this->assertSame('Foo', Caller::invoke(fn() => 'Foo', []));
        $this->assertSame('FOO', Caller::invoke('strtoupper', ['foo']));
        $this->assertSame('FOO', Caller::invoke([Str::class, 'upper'], ['foo']));
    }

    public function test_arguments()
    {
        $fn = fn ($c, $b, $a) => $this->assertSame(['A', 'B', 'C'], [$a, $b, $c]);
        Caller::invoke($fn, ['C', 'B', 'A']);

        $fn = fn ($value) => $this->assertIsCallable($value);
        $arg = fn () => 'The Function';
        Caller::invoke($fn, [$arg]);

        $fn = fn ($a, $b) => $this->assertSame(['A', 'B'], [$a, $b]);
        Caller::invoke($fn, ['b' => 'B', 'a' => 'A']);
    }

    public function test_dynamic_arguments()
    {
        $fn = fn () => $this->assertTrue(true);
        Caller::invoke($fn, [], ['a' => 'Foo', 'b' => 'Bar']);

        $fn = fn ($bar) => $this->assertSame('Bar', $bar);
        Caller::invoke($fn, [], ['foo' => 'Foo', 'bar' => 'Bar']);

        $fn = fn ($foo, $bar) => $this->assertSame(['Foo', 'Bar'], [$foo, $bar]);
        Caller::invoke($fn, ['Foo'], ['foo' => 'Other Foo', 'bar' => 'Bar']);

        $fn = fn ($value) => $this->assertSame('Value', $value);
        Caller::invoke($fn, [], ['value' => fn () => 'Value']);
    }

    public function test_invoke_actions()
    {
        $action = new class extends Action
        {
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

        $this->assertSame('Main', Caller::invokeAction($action, []));
        $this->assertSame('Main', Caller::invokeAction(get_class($action), []));
        $this->assertSame('Main', Caller::invokeAction([$action], []));
        $this->assertSame('Main', Caller::invokeAction([$action, 'main'], []));
        $this->assertSame('Foo', Caller::invokeAction([$action, 'foo'], []));
        $this->assertSame('Bar', Caller::invokeAction([$action, 'bar'], ['Bar']));
        $this->assertSame('Bar', Caller::invokeAction([$action, 'bar', 'Bar'], []));
    }

}