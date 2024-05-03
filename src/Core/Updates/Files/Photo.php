<?php

namespace Mmb\Core\Updates\Files;

/**
 * @property int $width
 * @property int $height
 */
class Photo extends DataWithFile
{

    protected function dataCasts() : array
    {
        return [
                'width'  => 'int',
                'height' => 'int',
            ] + parent::dataCasts();
    }

}