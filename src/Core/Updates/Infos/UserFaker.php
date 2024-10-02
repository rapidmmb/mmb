<?php

namespace Mmb\Core\Updates\Infos;

class UserFaker
{

    /**
     * Create new fake user info
     *
     * @param mixed       $id
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $username
     * @param string|null $languageCode
     * @param bool|null   $isPremium
     * @return UserInfo
     */
    public static function make(
        mixed   $id,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $username = null,
        ?string $languageCode = null,
        ?bool   $isPremium = null,
    )
    {
        return UserInfo::make(
            [
                'id'            => $id,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'username'      => $username,
                'language_code' => $languageCode,
                'is_premium'    => $isPremium,
            ]
        );
    }

}