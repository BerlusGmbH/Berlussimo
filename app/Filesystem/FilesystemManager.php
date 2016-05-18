<?php

namespace App\Filesystem;

use \Illuminate\Filesystem\FilesystemManager as BaseFilesystemManager;
use League\Flysystem\FilesystemInterface;

class FilesystemManager extends BaseFilesystemManager
{
    /**
     * Adapt the filesystem implementation.
     *
     * @param  \League\Flysystem\FilesystemInterface $filesystem
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function adapt(FilesystemInterface $filesystem)
    {
        return new FilesystemAdapter($filesystem);
    }

}