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
