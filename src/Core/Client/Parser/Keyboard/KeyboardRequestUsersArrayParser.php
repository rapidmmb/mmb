<?php

namespace Mmb\Core\Client\Parser\Keyboard;

use Mmb\Core\Client\Parser\ArrayParser;

class KeyboardRequestUsersArrayParser extends ArrayParser
{

    public function __construct()
    {
        parent::__construct(
            [
                'id'            => 'requestId',
                'requestId'     => 'requestId',
                'userIsBot'     => 'userIsBot',
                'isBot'         => 'userIsBot',
                'userIsPremium' => 'userIsPremium',
                'isPremium'     => 'userIsPremium',
                'maxQuantity' => 'maxQuantity',
                'max' => 'maxQuantity',
                'requestName' => 'requestName',
                'requestUsername' => 'requestUsername',
                'requestPhoto' => 'requestPhoto',
            ],
            errorOnFail: true,
        );
    }

}
