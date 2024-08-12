<?php

namespace Mmb\Core\Updates\Files;

/**
 * @property int     $duration
 * @property ?string $performer
 * @property ?string $title
 * @property ?string $fileName
 * @property ?string $mimeType
 * @property ?Photo  $thumbnail
 */
class Audio extends DataWithFile
{

    protected function dataCasts() : array
    {
        return [
                'duration'  => 'int',
                'performer' => 'string',
                'title'     => 'string',
                'file_name' => 'string',
                'mime_type' => 'string',
                'thumbnail' => Photo::class,
            ] + parent::dataCasts();
    }


    public function send(array $args = [], ...$namedArgs)
    {
        return $this->bot()->send([
            'type' => 'audio',
            'audio' => $this->id,
        ], $args, ...$namedArgs);
    }

}
