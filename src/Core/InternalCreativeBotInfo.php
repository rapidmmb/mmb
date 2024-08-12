<?php

namespace Mmb\Core;

use Illuminate\Database\Eloquent\Model;

readonly class InternalCreativeBotInfo extends InternalBotInfo
{

    public function __construct(
        string $token,
        ?string $username,
        ?string $guardName,
        public ?Model $record,
    )
    {
        parent::__construct($token, $username, $guardName, null);
    }

}
