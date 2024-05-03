<?php

namespace Mmb\Core\Updates\Infos;

use Mmb\Core\Data;
use Mmb\Core\Updates\Messages\Message;

/**
 * @property int $id
 * @property string $type
 * @property ?string $title
 * @property ?string $username
 * @property ?string $firstName
 * @property ?string $lastName
 * @property ?bool $isForum
 * @property ?string $bio
 */
class ChatInfo extends Data
{

    protected function dataCasts() : array
    {
        return [
            'id'                                      => 'int',
            'type'                                    => 'string',
            'title'                                   => 'string',
            'username'                                => 'string',
            'first_name'                              => 'string',
            'last_name'                               => 'string',
            'is_forum'                                => 'bool',
            // 'photo' => ChatPhoto::class,
            'emoji_status_custom_emoji_id'            => 'string',
            'emoji_status_expiration_date'            => 'date',
            'bio'                                     => 'string',
            'has_private_forwards'                    => 'bool',
            'has_restricted_voice_and_video_messages' => 'bool',
            'join_to_send_messages'                   => 'bool',
            'join_by_request'                         => 'bool',
            'description'                             => 'string',
            'invite_link'                             => 'string',
            'pinned_message'                          => Message::class,
            // 'permissions' => CharPermissions::class,
            'slow_mode_delay'                         => 'int',
            'message_auto_delete_time'                => 'int',
            'has_aggressive_anti_spam_enabled'        => 'bool',
            'has_hidden_members'                      => 'bool',
            'has_protected_content'                   => 'bool',
            'sticker_set_name'                        => 'string',
            'can_set_sticker_set'                     => 'bool',
            'linked_chat_id'                          => 'int',
            // 'location'                                => Location::class,
        ];
    }

    public static function fakePrivate($id = null, ...$args)
    {
        return static::make($args + [
            'id' => $id ?? rand(1, PHP_INT_MAX),
            'type' => 'private',
        ]);
    }

    public function sendMessage($message = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'chat' => $this->id,
                'text' => $message,
            ],
            $args + $namedArgs
        );

        return $this->bot()->sendMessage($args);
    }

    public function send($type = null, $message = null, array $args = [], ...$namedArgs)
    {
        $args = $this->mergeMultiple(
            [
                'chat' => $this->id,
                'type' => $type,
                'text' => $message,
            ],
            $args + $namedArgs
        );

        return $this->bot()->send($args);
    }

}
