<?php

namespace Mmb\Core\Updates\Data;

use Mmb\Core\Data;
use Mmb\Core\Updates\Files\Animation;
use Mmb\Core\Updates\Files\PhotoCollection;

class Game extends Data
{

    protected function castData(array $data, bool $trustedData = false)
    {
        return [
            'title' => 'string',
            'description' => 'string',
            'photo' => PhotoCollection::class,
            'text' => 'string',
            // 'text_entities' => MessageEntityCollection::class,
            'animation' => Animation::class,
        ];
    }

}
