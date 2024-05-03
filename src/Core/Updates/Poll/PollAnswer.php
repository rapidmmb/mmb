<?php

namespace Mmb\Core\Updates\Poll;

use Illuminate\Support\Collection;
use Mmb\Core\Data;
use Mmb\Core\Updates\Infos\ChatInfo;
use Mmb\Core\Updates\Infos\UserInfo;

/**
 * @property string           $pollId
 * @property ?ChatInfo        $chat
 * @property ?UserInfo        $user
 * @property ?Collection<int> optionIds
 */
class PollAnswer extends Data
{

    protected function dataCasts() : array
    {
        return [
            'poll_id'    => 'string',
            'voter_chat' => ChatInfo::class,
            'user'       => UserInfo::class,
            'option_ids' => ['int'],
        ];
    }

    protected function dataShortAccess() : array
    {
        return [
            'chat' => 'voter_chat',
        ];
    }

}