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


    public function send(array $args = [], ...$namedArgs)
    {
        return $this->bot()->send([
            'type' => 'photo',
            'photo' => $this->id,
        ], $args, ...$namedArgs);
    }

}
