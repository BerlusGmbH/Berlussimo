<?php

namespace App\Providers;

use App\Filesystem\FilesystemManager;
use Illuminate\Filesystem\FilesystemServiceProvider as BaseFileSystemServiceProvider;

class FileSystemServiceProvider extends BaseFilesystemServiceProvider
{
    /**
     * Register the filesystem manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton('filesystem', function () {
            return new FilesystemManager($this->app);
        });
    }

}