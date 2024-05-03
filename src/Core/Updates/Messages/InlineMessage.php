<?php

namespace Mmb\Core\Updates\Messages;

use Mmb\Core\Bot;

class InlineMessage extends Message
{

    public static function make($data, Bot $bot = null, bool $trustedData = false)
    {
        return new static([ 'message_id' => $data ], $bot, $trustedData);
    }

}