<?php

namespace Mmb\Core;

readonly class InternalBotInfo
{

    public function __construct(
        public string $token,
        public ?string $username,
        public ?string $guardName,
        public ?string $configName,
    )
    {
    }

    public function getWebhookUrl()
    {
        return app(BotChanneling::class)->getWebhookUrl($this);
    }

}
