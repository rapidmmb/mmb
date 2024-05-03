<?php

namespace Mmb\Core\Requests;

use Illuminate\Support\Str;

class TestingRequest extends RequestApi
{

    protected function execute()
    {
        return match(strtolower($this->method))
        {
            'sendmessage' => [
                'ok' => true,
                'result' => [
                    'message_id' => rand(1, 1000),
                    'text' => $this->args['text'],
                    'chat' => [
                        'id' => $this->args['chat_id'],
                        'username' => 'username',
                        'title' => 'Title',
                    ],
                    'from' => [
                        'id' => Str::before($this->token, ':'),
                    ],
                ],
            ],

            default => [
                'ok' => true,
            ],
        };
    }

}