<?php

namespace Mmb\Core\Updates\Infos;

/**
 * @property int $userId
 */
class UserShared extends Shared
{

    protected function dataCasts() : array
    {
        return [
                'user_id' => 'int',
            ] + parent::dataCasts();
    }

}