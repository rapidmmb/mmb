<?php

namespace Mmb\Core\Updates\Data;

use Mmb\Core\Data;

/**
 * @property string $emoji
 * @property string $value
 */
class Dice extends Data
{

    protected function dataCasts() : array
    {
        return [
            'emoji' => 'string',
            'value' => 'string',
        ];
    }

}