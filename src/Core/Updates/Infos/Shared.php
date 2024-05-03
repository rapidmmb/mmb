<?php

namespace Mmb\Core\Updates\Infos;

use Mmb\Core\Data;

/**
 * @property int $requestId
 */
abstract class Shared extends Data
{

    protected function dataCasts() : array
    {
        return [
            'request_id' => 'int',
        ];
    }

}