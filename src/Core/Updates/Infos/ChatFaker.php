<?php

namespace Mmb\Core\Updates\Infos;

class ChatFaker
{

    /**
     * Create fake private chat info
     *
     * @param mixed       $id
     * @param string|null $username
     * @param string|null $firstName
     * @param string|null $lastName
     * @return ChatInfo
     */
    public static function private(
        mixed   $id,
        ?string $username = null,
        ?string $firstName = null,
        ?string $lastName = null,
    )
    {
        return ChatInfo::make(
            [
                'id'         => $id,
                'type'       => 'private',
                'username'   => $username,
                'first_name' => $firstName,
                'last_name'  => $lastName,
            ]
        );
    }

    /**
     * Create fake group chat info
     *
     * @param             $id
     * @param string|null $title
     * @param string|null $username
     * @return ChatInfo
     */
    public static function group(
        $id,
        ?string $title = null,
        ?string $username = null,
    )
    {
        return ChatInfo::make(
            [
                'id'         => $id,
                'type'       => 'group',
                'title'      => $title,
                'username'   => $username,
            ]
        );
    }

    /**
     * Create fake super group chat info
     *
     * @param             $id
     * @param string|null $title
     * @param string|null $username
     * @return ChatInfo
     */
    public static function superGroup(
        $id,
        ?string $title = null,
        ?string $username = null,
    )
    {
        return ChatInfo::make(
            [
                'id'         => $id,
                'type'       => 'supergroup',
                'title'      => $title,
                'username'   => $username,
            ]
        );
    }

    /**
     * Create fake channel chat info
     *
     * @param             $id
     * @param string|null $title
     * @param string|null $username
     * @return ChatInfo
     */
    public static function channel(
        $id,
        ?string $title = null,
        ?string $username = null,
    )
    {
        return ChatInfo::make(
            [
                'id'         => $id,
                'type'       => 'channel',
                'title'      => $title,
                'username'   => $username,
            ]
        );
    }

}