<?php

namespace Mmb\Core\Client;

use Illuminate\Support\Traits\Conditionable;

class Request
{
    use Conditionable;

    public function __construct(
        public string $uri,
        public string $method,
        public array  $parameters,
        public readonly bool $isUploadRequest = false,
        public readonly bool $isDownloadRequest = false,
    )
    {
    }


}