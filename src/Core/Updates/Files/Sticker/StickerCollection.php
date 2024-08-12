<?php

namespace Mmb\Core\Updates\Files\Sticker;

use Mmb\Core\DataCollection;
use Mmb\Core\Updates\Files\Sticker;

class StickerCollection extends Sticker implements \Countable, \IteratorAggregate
{
    use DataCollection;

    protected function getCollectionClassType()
    {
        return Sticker::class;
    }

    public function getDefault()
    {
        return $this->first();
    }
}
