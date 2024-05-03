<?php

namespace Mmb\Core\Updates\Files;

/**
 * @property int     $duration
 * @property ?string $mimeType
 */
class Voice extends DataWithFile
{

    protected function dataCasts() : array
    {
        return [
                'duration'  => 'int',
                'mime_type' => 'string',
            ] + parent::dataCasts();
    }

}