<?php

namespace Mmb\Core\Updates\Poll;

use Mmb\Core\Data;

/**
 * @property string $text
 * @property int    $voterCount
 */
class PollOption extends Data
{

    protected function dataCasts() : array
    {
        return [
            'text'        => 'string',
            'voter_count' => 'int',
        ];
    }

}