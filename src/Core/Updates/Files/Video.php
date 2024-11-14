<?php

namespace Mmb\Core\Updates\Files;

/**
 * @property int     $width
 * @property int     $height
 * @property int     $duration
 * @property ?Photo  $thumbnail
 * @property ?string $fileName
 * @property ?string $mimeType
 */
class Video extends DataWithFile
{

    protected function dataCasts() : array
    {
        return [
                'width'     => 'int',
                'height'    => 'int',
                'duration'  => 'int',
                'thumbnail' => Photo::class,
                'file_name' => 'string',
                'mime_type' => 'string',
            ] + parent::dataCasts();
    }


    public function send(array $args = [], ...$namedArgs)
    {
        return $this->bot()->send($args + $namedArgs + [
            'type' => 'video',
            'value' => $this->id,
        ]);
    }

}
