<?php

namespace Mmb\Core\Requests\Parser\Keyboard;

use Mmb\Core\Requests\Parser\ArrayParser;

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
