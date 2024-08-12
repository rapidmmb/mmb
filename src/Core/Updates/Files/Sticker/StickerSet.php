<?php

namespace Mmb\Core\Updates\Files\Sticker;

use Mmb\Core\Data;
use Mmb\Core\Updates\Files\Photo;

class StickerSet extends Data
{

    protected function dataCasts() : array
    {
        return [
            'name' => 'string',
            'title' => 'string',
            'sticker_type' => 'string',
            'stickers' => StickerCollection::class,
            'thumbnail' => Photo::class,
        ];
    }

}
