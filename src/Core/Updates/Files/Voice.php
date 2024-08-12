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


    public function send(array $args = [], ...$namedArgs)
    {
        return $this->bot()->send([
            'type' => 'voice',
            'voice' => $this->id,
        ], $args, ...$namedArgs);
    }

}
