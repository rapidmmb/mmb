<?php

namespace Mmb\Core\Updates\Messages;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserInfo;

class MessageFaker
{

    /**
     * Create new fake text message
     *
     * @param mixed    $id
     * @param UserInfo $from
     * @param ChatInfo $chat
     * @param string   $text
     * @param          ...$data
     * @return Message
     */
    public static function text(
        mixed    $id,
        UserInfo $from,
        ChatInfo $chat,
        string   $text,
                 ...$data,
    )
    {
        $data += [
            'messageId' => $id,
            'from'      => $from->getRealData(),
            'chat'      => $chat->getRealData(),
            'text'      => $text,
        ];

        $data = Arr::mapWithKeys($data, fn($value, $key) => [Str::snake($key) => $value]);

        return Message::make($data);
    }

    /**
     * Create new simple fake text message
     *
     * @param string $text
     * @param mixed  ...$data
     * @return Message
     */
    public static function simpleText(string $text, ...$data)
    {
        return Message::make(['text' => $text, ...$data]);
    }

    /**
     * Create new fake media message
     *
     * @param mixed       $id
     * @param UserInfo    $from
     * @param ChatInfo    $chat
     * @param string      $type
     * @param string      $fileId
     * @param string|null $fileName
     * @param int|null    $fileSize
     * @param array       $fileData
     * @param             ...$data
     * @return Message
     */
    public static function media(
        mixed    $id,
        UserInfo $from,
        ChatInfo $chat,
        string $type,
        string $fileId,
        ?string $fileName = null,
        ?int $fileSize = null,
        array $fileData = [],
        ...$data,
    )
    {
        $data += [
            'messageId' => $id,
            'from'      => $from->getRealData(),
            'chat'      => $chat->getRealData(),
            $type => [
                'file_id' => $fileId,
                'file_name'      => $fileName,
                'file_size'      => $fileSize,
                ...Arr::mapWithKeys($fileData, fn($value, $key) => [Str::snake($key) => $value])
            ],
        ];

        $data = Arr::mapWithKeys($data, fn($value, $key) => [Str::snake($key) => $value]);

        return Message::make($data);
    }

    /**
     * Create new simple fake media message
     *
     * @param string      $type
     * @param string      $fileId
     * @param string|null $fileName
     * @param int|null    $fileSize
     * @param array       $fileData
     * @param             ...$data
     * @return Message
     */
    public static function simpleMedia(
        string $type,
        string $fileId = '99',
        ?string $fileName = null,
        ?int $fileSize = null,
        array $fileData = [],
                 ...$data,
    )
    {
        $data += [
            $type => [
                'file_id' => $fileId,
                'file_name'      => $fileName,
                'file_size'      => $fileSize,
                ...Arr::mapWithKeys($fileData, fn($value, $key) => [Str::snake($key) => $value])
            ],
        ];

        $data = Arr::mapWithKeys($data, fn($value, $key) => [Str::snake($key) => $value]);

        return Message::make($data);
    }

}