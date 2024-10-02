<?php

namespace Mmb\Core\Updates;

use Mmb\Core\Updates\Infos\ChatFaker;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserInfo;
use Mmb\Core\Updates\Messages\Message;

class UpdateFaker
{

    /**
     * Create new empty fake update with user info
     *
     * @param UserInfo      $user
     * @param ChatInfo|null $chat
     * @return Update
     */
    public static function user(
        UserInfo  $user,
        ?ChatInfo $chat = null
    )
    {
        $chat ??= ChatFaker::private($user->id);

        return Update::make(
            [
                'message' => [
                    'from' => $user->getRealData(),
                    'chat' => $chat->getRealData(),
                ],
            ]
        );
    }

    /**
     * Create new empty fake update with chat info
     *
     * @param ChatInfo $chat
     * @return Update
     */
    public static function chat(
        ChatInfo $chat
    )
    {
        return Update::make(
            [
                'message' => [
                    'chat' => $chat->getRealData(),
                ],
            ]
        );
    }

    /**
     * Create new fake message update
     *
     * @param Message $message
     * @return Update
     */
    public static function message(Message $message)
    {
        return Update::make(
            [
                'message' => $message->getRealData(),
            ]
        );
    }

    /**
     * Create new fake edited message update
     *
     * @param Message $message
     * @return Update
     */
    public static function editedMessage(Message $message)
    {
        return Update::make(
            [
                'edited_message' => $message->getRealData(),
            ]
        );
    }

    /**
     * Create new fake channel post update
     *
     * @param Message $message
     * @return Update
     */
    public static function channelPost(Message $message)
    {
        return Update::make(
            [
                'channel_post' => $message->getRealData(),
            ]
        );
    }

    /**
     * Create new fake edited channel post update
     *
     * @param Message $message
     * @return Update
     */
    public static function editedChannelPost(Message $message)
    {
        return Update::make(
            [
                'edited_channel_post' => $message->getRealData(),
            ]
        );
    }

}