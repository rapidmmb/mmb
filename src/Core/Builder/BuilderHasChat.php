<?php

namespace Mmb\Core\Builder;

use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserInfo;

trait BuilderHasChat
{

    public function chat($chat)
    {
        $chat = $this->expect(
            $chat,
            [
                ChatInfo::class => 'id',
                UserInfo::class => 'id',
            ],
            'chat id',
        );

        return $this->put('chatId', $chat);
    }

    public function to($chat)
    {
        return $this->chat($chat);
    }

}