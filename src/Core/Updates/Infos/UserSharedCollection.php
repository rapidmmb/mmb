<?php

namespace Mmb\Core\Updates\Infos;

use Countable;
use IteratorAggregate;
use Mmb\Core\Data;
use Mmb\Core\DataCollection;

/**
 * @implements IteratorAggregate<int, UserShared>
 * @implements DataCollection<UsersShared>
 */
class UserSharedCollection extends Data implements Countable, IteratorAggregate
{
    use DataCollection;

    protected function getCollectionClassType()
    {
        return UserShared::class;
    }
}
