<?php

namespace Mmb\Core\Traits;

use Mmb\Core\Updates\Files\FileInfo;

trait ApiBotFiles
{

    public function getFile(array $args = [], ...$namedArgs)
    {
        return $this->makeData(
            FileInfo::class,
            $this->request('getFile', $args + $namedArgs)
        );
    }

    // public function getFileDownloadUrl(string $filePath)
    // {
    //     return "https://api.telegram.org/file/bot" . $this->info->token . "/" . $filePath; todo : remove
    // }

}
