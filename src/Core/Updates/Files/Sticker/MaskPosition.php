<?php

namespace Mmb\Core\Updates\Files\Sticker;

use Mmb\Core\Data;

class MaskPosition extends Data
{

    protected function dataCasts() : array
    {
        return [
            'point' => 'string',
            'x_shift' => 'float',
            'y_shift' => 'float',
            'scale' => 'float',
        ];
    }

}
