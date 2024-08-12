<?php

namespace Mmb\Core\Traits;

use Mmb\Core\Updates\Files\Sticker\StickerSet;

trait ApiBotStickers
{

    public function getStickerSet(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            StickerSet::class,
            $this->request('getStickerSet', $args + $namedArgs)
        );
    }

}
