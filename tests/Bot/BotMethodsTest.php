<?php

namespace Mmb\Tests\Bot;

use Mmb\Action\Form\FormStepHandler;
use Mmb\Action\Memory\StepMemory;
use Mmb\Core\Bot;
use Mmb\Core\Updates\Messages\Message;
use Mmb\Support\Caller\Caller;
use Mmb\Support\Caller\CallerFactory;
use Mmb\Tests\TestCase;

class BotMethodsTest extends TestCase
{

    // public function test_get_me_method()
    // {
    //     $message = Message::make([
    //         'message_id' => 12,
    //         'chat' => [
    //             'id' => 370924007,
    //         ]
    //     ]);
    //
    //     dd(
    //         $this->bot()
    //             ->newMessage()
    //             ->reply($message)
    //             ->html('<b>Hello World</b>')
    //             ->send()
    //     );
    // }
    //
    // public function test2()
    // {
    //     $handler = new FormStepHandler();
    //     $handler->class = static::class;
    //     $handler->currentInput = 'demo';
    //     $handler->type = 'Fixed';
    //
    //     $handler->save($memory = new StepMemory);
    //     // dd($memory);
    //
    //     $handler = new FormStepHandler($memory);
    //     dd($handler);
    // }

}