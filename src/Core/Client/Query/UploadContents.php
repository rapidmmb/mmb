<?php

namespace Mmb\Core\Client\Query;

readonly class UploadContents
{

    public function __construct(
        public string $contents,
        public string $fileName,
    )
    {
    }

}