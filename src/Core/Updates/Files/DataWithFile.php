<?php

namespace Mmb\Core\Updates\Files;

use Mmb\Core\Data;

/**
 * @property string $id
 * @property string $uniqueId
 * @property ?int   $size
 */
abstract class DataWithFile extends Data
{

    protected function dataCasts() : array
    {
        return [
            'file_id'        => 'string',
            'file_unique_id' => 'string',
            'file_size'      => 'int',
        ];
    }

    protected function dataShortAccess() : array
    {
        return [
            'id'        => 'file_id',
            'unique_id' => 'file_id',
            'size'      => 'file_size',
        ];
    }

    public function getFile()
    {
        return $this->bot()->getFile(fileId: $this->id);
    }

    public function download(string $path)
    {
        return $this->getFile()?->download($path);
    }

    public function downloadToStorage(string $dir, string $name = null)
    {
        return $this->getFile()?->downloadToStorage($dir, $name);
    }

    public function downloadToPublic(string $dir, string $name = null)
    {
        return $this->getFile()?->downloadToPublic($dir, $name);
    }

}
