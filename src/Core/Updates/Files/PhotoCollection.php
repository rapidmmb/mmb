<?php

namespace Mmb\Core\Updates\Files;

use Countable;
use IteratorAggregate;
use Mmb\Core\DataCollection;

/**
 * @implements IteratorAggregate<int, Photo>
 */
class PhotoCollection extends Photo implements Countable, IteratorAggregate
{
    use DataCollection;

    protected function getCollectionClassType()
    {
        return Photo::class;
    }

    public function getDefault()
    {
        return $this->last();
    }


    public function send(array $args = [], ...$namedArgs)
    {
        return $this->bot()->send([
            'type' => 'photo',
            'photo' => $this->id,
        ], $args, ...$namedArgs);
    }

}
