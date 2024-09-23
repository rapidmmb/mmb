<?php

namespace Mmb\Tests\Action;

use Mmb\Action\Action;
use Mmb\Testing\Concerns\UpdateTesting;
use Mmb\Tests\TestCase;

class ActionTest extends TestCase
{
    use UpdateTesting;

    public function test_invoke()
    {
        $action = new class extends Action
        {
            public function main()
            {
                return 'Foo';
            }
        };

        $this->assertSame('Foo', $action->invoke('main'));
    }

    public function test_response()
    {

    }

}