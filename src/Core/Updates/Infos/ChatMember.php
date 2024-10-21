<?php

namespace Mmb\Core\Updates\Infos;

use Carbon\Carbon;
use Mmb\Core\Data;

/**
 * @property string   $status      The member's status in the chat,
 * @property UserInfo $user        Information about the user
 * @property ?bool    $isAnonymous True, if the user's presence in the chat is hidden
 * @property ?string  $customTitle Optional. Custom title for this user
 * @property ?bool    $canBeEdited True, if the bot is allowed to edit administrator privileges of that user
 * @property ?Carbon  $untilDate
 * @property ?bool    $isMember    True, if the user is a member of the chat at the moment of the request
 *
 * @property bool     $isJoined    True, if the user is in the chat as member, admin or creator
 * @property bool     $isNotJoined True, if the user is not in the chat as member, admin or creator
 * @property bool     $isAdmin     True, if the user is an admin (not a creator)
 * @property bool     $isCreator   True, if the user is a creator
 * @property bool     $isManager   True, if the user is a creator or an admin
 */
class ChatMember extends Data
{

    public const STATUS_CREATOR       = 'creator';
    public const STATUS_ADMINISTRATOR = 'administrator';
    public const STATUS_MEMBER        = 'member';
    public const STATUS_RESTRICTED    = 'restricted';
    public const STATUS_LEFT          = 'left';
    public const STATUS_BANNED        = 'banned';

    protected function dataCasts() : array
    {
        return [
            'status'        => 'string',
            'user'          => UserInfo::class,
            'is_anonymous'  => 'bool',
            'custom_title'  => 'string',
            'can_be_edited' => 'bool',
            'until_date'    => 'date',
            'is_member'     => 'bool',
        ];
    }

    protected function getIsJoinedAttribute()
    {
        return in_array($this->status, [self::STATUS_CREATOR, self::STATUS_ADMINISTRATOR, self::STATUS_MEMBER]);
    }

    protected function getIsNotJoinedAttribute()
    {
        return !$this->isJoined;
    }

    protected function getIsAdminAttribute()
    {
        return $this->status == self::STATUS_ADMINISTRATOR;
    }

    protected function getIsCreatorAttribute()
    {
        return $this->status == self::STATUS_CREATOR;
    }

    protected function getIsManagerAttribute()
    {
        return $this->status == self::STATUS_ADMINISTRATOR || $this->status == self::STATUS_CREATOR;
    }

}
