<?php

namespace Mmb\Core\Requests\Parser\Keyboard;

use Mmb\Core\Requests\Parser\ArrayParser;

class KeyboardRequestChatArrayParser extends ArrayParser
{

    public function __construct()
    {
        parent::__construct(
            [
                'id'              => 'requestId',
                'requestId'       => 'requestId',
                'chatIsChannel'   => 'chatIsChannel',
                'isChannel'       => 'isChannel',
                'chatIsForum'     => 'chatIsForum',
                'isForum'         => 'chatIsForum',
                'chatHasUsername' => 'chatHasUsername',
                'hasUsername'     => 'chatHasUsername',
                'chatIsCreated'   => 'chatIsCreated',
                'isCreated'       => 'chatIsCreated',
                'botIsMember'     => 'botIsMember',
            ],
            errorOnFail: true,
        );
    }

}
