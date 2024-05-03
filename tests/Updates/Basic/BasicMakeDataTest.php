<?php

namespace Mmb\Tests\Updates\Basic;

use Mmb\Core\Updates\Update;
use Mmb\Tests\TestCase;

class BasicMakeDataTest extends TestCase
{

    public function test_access_to_data()
    {
        $update = Update::make([
            'update_id' => 10,
            'message' => [
                'message_id' => 20,
                'caption' => "Hello World",
            ],
        ]);

        $this->assertNotNull($update);
        $this->assertSame($update->update_id, 10);
        $this->assertSame($update->updateId, 10);
        $this->assertSame($update->id, 10);

        $this->assertNotNull($update->message);
        $this->assertSame($update->message->id, 20);
        $this->assertSame($update->message->text, "Hello World");
    }

}