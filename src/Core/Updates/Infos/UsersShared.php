<?php

namespace Mmb\Core\Updates\Infos;

/**
 * @property UserSharedCollection $users
 */
class UsersShared extends Shared
{

    protected function dataCasts() : array
    {
        return parent::dataCasts() + [
            'users' => UserSharedCollection::class,
            ];
    }

}
