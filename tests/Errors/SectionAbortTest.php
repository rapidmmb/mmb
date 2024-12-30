<?php

namespace Mmb\Tests\Errors;

use Mmb\Action\Section\Section;
use Mmb\Context;
use Mmb\Core\Updates\Update;
use Mmb\Support\Exceptions\CallableException;
use Mmb\Tests\TestCase;

class SectionAbortTest extends TestCase
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

    public function test_abort()
    {
        $section = new class($this->context) extends Section {
            public function main()
            {
                abort(404);
            }

            public function denied404()
            {
                SectionAbortTest::$isCalled = true;
            }
        };

        $this->startTest(fn() => $section->invoke('main'));
    }

    public function test_denied_with_number()
    {
        $section = new class($this->context) extends Section {
            public function main()
            {
                denied(404);
            }

            public function denied404()
            {
                SectionAbortTest::$isCalled = true;
            }
        };

        $this->startTest(fn() => $section->invoke('main'));
    }

    public function test_denied_with_string_code()
    {
        $section = new class($this->context) extends Section {
            public function main()
            {
                denied('notFound');
            }

            public function deniedNotFound()
            {
                SectionAbortTest::$isCalled = true;
            }
        };

        $this->startTest(fn() => $section->invoke('main'));
    }

}