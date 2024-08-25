<?php

namespace Action;

use Mmb\Action\Action;
use Mmb\Tests\TestCase;

class ActionTest extends TestCase
{

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

}