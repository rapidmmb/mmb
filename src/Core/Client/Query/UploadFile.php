<?php

namespace Mmb\Core\Client\Query;

readonly class UploadFile
{

    public string $fileName;

    public function __construct(
        public string $path,
        ?string $fileName = null,
    )
    {
        $this->fileName = $fileName ?? basename($this->path);
    }

    public function readContents(): string
    {
        return file_get_contents($this->path);
    }

}