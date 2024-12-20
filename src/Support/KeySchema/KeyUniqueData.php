<?php

namespace Mmb\Support\KeySchema;

use Mmb\Core\Updates\Update;

class KeyUniqueData
{

    public const TAG_TEXT = '.';
    public const TAG_CALLBACK_DATA = 'Z';
    public const TAG_CONTACT = 'c';
    public const TAG_LOCATION = 'l';
    public const TAG_REQUEST_USER = 'u';
    public const TAG_REQUEST_USERS = 'U';
    public const TAG_REQUEST_CHAT = 'C';
    public const TAG_POLL = 'p';

    public static function makeText(string $text): string
    {
        return self::TAG_TEXT . $text;
    }

    public static function makeCallbackData(string $data): string
    {
        return self::TAG_CALLBACK_DATA . $data;
    }

    public static function makeContact(): string
    {
        return self::TAG_CONTACT;
    }

    public static function makeLocation(): string
    {
        return self::TAG_LOCATION;
    }

    public static function makeRequestUser(string $id): string
    {
        return self::TAG_REQUEST_USER . $id;
    }

    public static function makeRequestUsers(string $id): string
    {
        return self::TAG_REQUEST_USERS . $id;
    }

    public static function makeRequestChat(string $id): string
    {
        return self::TAG_REQUEST_CHAT . $id;
    }

    public static function makePoll(): string
    {
        return self::TAG_POLL;
    }


    public static function fromUpdate(Update $update): ?string
    {
        if ($message = $update->message) {
            return match ($message->type) {
                'text'        => self::TAG_TEXT . $message->text,
                'contact'     => self::TAG_CONTACT,
                'location'    => self::TAG_LOCATION,
                'userShared'  => self::TAG_REQUEST_USER . $message->userShared->requestId,
                'usersShared' => self::TAG_REQUEST_USERS . $message->usersShared->requestId,
                'chatShared'  => self::TAG_REQUEST_CHAT . $message->chatShared->requestId,
                'poll'        => self::TAG_POLL, // todo
                default       => null,
            };
        } elseif ($callbackQuery = $update->callbackQuery) {
            if (str_starts_with($update->callbackQuery->data, '~dialog:')) {
                @[$target, $id, $action] = explode(':', substr($update->callbackQuery->data, 8), 3);
                if ($target && $id) {
                    return 'D' . $action;
                }
            } elseif (str_starts_with($update->callbackQuery->data, '~df:')) {
                @[$class, $method, $_, $action] = explode(':', substr($update->callbackQuery->data, 4), 3);
                if ($class && $method) {
                    return 'D' . $action;
                }
            }

            return self::TAG_CALLBACK_DATA . $callbackQuery->data;
        }

        return null;
    }

}