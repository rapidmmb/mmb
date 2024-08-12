<?php

namespace Mmb\Core\Updates\Data;

use Mmb\Core\Data;
use Mmb\Core\Updates\Infos\ChatInfo;

class Story extends Data
{

    protected function dataCasts() : array
    {
        return [
            'id' => 'int',
            'chat' => ChatInfo::class,
        ];
    }

}
