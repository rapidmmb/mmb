<?php

namespace Mmb\Core\Updates\Files;

use Mmb\Core\Data;

/**
 * @property string  $id
 * @property string  $uniqueId
 * @property ?int    $size
 * @property ?string $filePath
 */
class FileInfo extends Data
{

    protected function dataCasts() : array
    {
        return [
            'file_id'        => 'string',
            'file_unique_id' => 'string',
            'file_size'      => 'int',
            'file_path'      => 'string',
        ];
    }

    protected function dataShortAccess() : array
    {
        return [
            'id'        => 'file_id',
            'unique_id' => 'file_unique_id',
            'size'      => 'file_size',
        ];
    }

    // public function getDownloadUrl()
    // {
    //     // return $this->bot()->getFileDownloadUrl($this->filePath); todo : remove
    // }

    public function download(string $path)
    {
        // return copy($this->getDownloadUrl(), $path);
        return $this->bot()->request('download', [
            'file' => $this->filePath,
            'path' => $path,
        ]);
    }

    public function downloadToStorage(string $dir, string $name = null)
    {
        return $this->download(storage_path($dir . ($name === null ? '' : '/' . $name)));
    }

    public function downloadToPublic(string $dir, string $name = null)
    {
        return $this->download(public_path($dir . ($name === null ? '' : '/' . $name)));
    }

}
