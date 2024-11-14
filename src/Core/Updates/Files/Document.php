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


    public function send(array $args = [], ...$namedArgs)
    {
        return $this->bot()->send($args + $namedArgs + [
            'type' => 'document',
            'value' => $this->id,
        ]);
    }

}
