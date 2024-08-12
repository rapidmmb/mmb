<?php

namespace Mmb\Core\Updates\Files;

class Sticker extends DataWithFile
{

    public const TYPE_REGULAR = 'regular';
    public const TYPE_MASK = 'mask';
    public const TYPE_CUSTOM_EMOJI = 'custom_emoji';

    protected function dataCasts() : array
    {
        return [
                'type'     => 'string',
                'width'    => 'int',
                'height'    => 'int',
                'duration'  => 'int',
                'is_animated' => 'bool',
                'is_video' => 'bool',
                'thumbnail' => Photo::class,
                'set_name' => 'string',
                'premium_animation' => FileInfo::class,
                // 'mask_position' => MaskPosition::class,
                'custom_emoji_id' => 'string',
                'needs_repainting' => 'bool',
                'file_size' => 'int',
            ] + parent::dataCasts();
    }


    public function send(array $args = [], ...$namedArgs)
    {
        return $this->bot()->send([
            'type' => 'sticker',
            'sticker' => $this->id,
        ], $args, ...$namedArgs);
    }

    public function getStickerSet(array $args = [], ...$namedArgs)
    {
        return $this->bot()->getStickerSet($args, ...$namedArgs);
    }

}
