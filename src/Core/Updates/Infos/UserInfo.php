<?php

namespace Mmb\Core\Updates\Infos;

use Mmb\Core\Data;

/**
 * @property int $id
 * @property ?bool $isBot
 * @property ?string $username
 * @property ?string $firstName
 * @property ?string $lastName
 * @property ?string $languageCode
 * @property ?bool $isPremium
 * @property ?bool $addedToAttachmentMenu
 * @property ?bool $canJoinGroups
 * @property ?bool $canReadAllGroupMessages
 * @property ?bool $supportsInlineQueries
 *
 * @property string $name
 */
class UserInfo extends Data
{

    protected function dataCasts() : array
    {
        return [
            'id'                         => 'int',
            'is_bot'                     => 'bool',
            'username'                   => 'string',
            'first_name'                 => 'string',
            'last_name'                  => 'string',
            'language_code'              => 'string',
            'is_premium'                 => 'bool',
            'added_to_attachment_menu'   => 'bool',
            'can_join_groups'            => 'bool',
            'can_read_all_group_messages' => 'bool',
            'supports_inline_queries'    => 'bool',
        ];
    }

    public function getNameAttribute()
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

}