<?php

namespace Mmb\Core\Updates\Infos;

use Mmb\Core\Data;
use Mmb\Core\Updates\Files\PhotoCollection;

/**
 * @property int             $totalCount
 * @property PhotoCollection $photos
 */
class UserProfilePhotos extends Data
{

    protected function dataCasts() : array
    {
        return [
            'total_count' => 'int',
            'photos'      => PhotoCollection::class,
        ];
    }

}