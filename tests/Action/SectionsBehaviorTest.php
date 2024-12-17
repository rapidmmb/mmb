<?php

namespace Mmb\Tests\Action;

use Mmb\Action\Section\Menu;
use Mmb\Action\Section\Section;
use Mmb\Context;
use Mmb\Support\Exceptions\CallableException;
use Mmb\Tests\Errors\SectionAbortTest;
use Mmb\Tests\TestCase;

class SectionsBehaviorTest extends TestCase
{

    public static bool $isCalled;

    public function startTest($callback)
    {
        static::$isCalled = false;
        try {
            $callback();
        } catch (\Throwable $e) {
            if ($e instanceof CallableException) {
                $e->invoke($this->context);
            } else {
                throw $e;
            }
        }
        $this->assertTrue(static::$isCalled);
    }

    public function test_safe_calling()
    {
        $section = new class($this->context) extends Section {
            public function main()
            {
                denied(404);
            }

            public function denied404()
            {
                SectionsBehaviorTest::$isCalled = true;
            }
        };

        $this->startTest(fn() => $section->safe->main());
    }

    public function test_shorter_magic_inline_objects()
    {
        $section = new class($this->context) extends Section {
            public int $x;

            public function first(Menu $menu)
            {
                $this->x = 100;
            }

            public function second(Menu $menu, int $i)
            {
                $this->x = $i;
            }
        };

        $menu = $section->first->make();
        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertSame(100, $section->x);

        $menu = $section->second->make(i: 500);
        $this->assertInstanceOf(Menu::class, $menu);
        $this->assertSame(500, $section->x);
    }

}