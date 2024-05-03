<?php

namespace Mmb\Core\Updates\Infos;

/**
 * @property int $chatId
 */
class ChatShared extends Shared
{

    protected function dataCasts() : array
    {
        return [
                'chat_id' => 'int',
            ] + parent::dataCasts();
    }

}