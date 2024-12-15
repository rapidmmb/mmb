<?php

namespace Mmb\Core\Client\Parser\Keyboard;

use Mmb\Core\Client\Parser\ArrayParser;

class KeyboardRequestPollArrayParser extends ArrayParser
{

    public function __construct()
    {
        parent::__construct(
            [
                'type' => 'type',
            ],
            errorOnFail: true,
        );
    }

}
