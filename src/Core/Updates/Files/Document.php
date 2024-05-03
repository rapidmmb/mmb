<?php

namespace Mmb\Core\Updates\Files;

/**
 * @property ?Photo $thumbnail
 * @property ?string $fileName
 * @property ?string $mimeType
 */
class Document extends DataWithFile
{

    protected function dataCasts() : array
    {
        return [
                'thumbnail' => Photo::class,
                'file_name' => 'string',
                'mime_type' => 'string',
            ] + parent::dataCasts();
    }

}