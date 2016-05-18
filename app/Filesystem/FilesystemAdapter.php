<?php
/**
 * Created by PhpStorm.
 * User: mueller
 * Date: 13.07.16
 * Time: 08:47
 */

namespace App\Filesystem;

use \Illuminate\Filesystem\FilesystemAdapter as BaseFilesystemAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use RuntimeException;

class FilesystemAdapter extends BaseFilesystemAdapter
{
    /**
     * Get the URL for the file at the given path.
     *
     * @param  string $path
     * @return string
     */
    public function url($path)
    {
        $adapter = $this->driver->getAdapter();

        if ($adapter instanceof LocalAdapter) {
            return asset('storage' . $this->path($path));
        }
        return parent::url($path);
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string $path
     * @return string
     */
    public function path($path)
    {
        $adapter = $this->driver->getAdapter();

        if ($adapter instanceof LocalAdapter) {
            $fullPath = $adapter->getPathPrefix() . $path;
            $default = config('filesystems.default');
            $prefix = config("filesystems.disks.{$default}.root");
            if (substr($fullPath, 0, strlen($prefix)) == $prefix) {
                $path = substr($fullPath, strlen($prefix));
            }
            return $path;
        }
        throw new RuntimeException('This driver does not support retrieving paths.');
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @param  string $path
     * @return string
     * @throws \RuntimeException
     */
    public function fullPath($path)
    {
        $adapter = $this->driver->getAdapter();

        if ($adapter instanceof LocalAdapter) {
            return $adapter->getPathPrefix() . $path;
        }
        throw new RuntimeException('This driver does not support retrieving paths.');
    }

    /**
     * Get the URL for the file at the given path.
     *
     * @return string
     * @throws \RuntimeException
     */
    public function basePath()
    {
        $adapter = $this->driver->getAdapter();

        if ($adapter instanceof LocalAdapter) {
            return $adapter->getPathPrefix();
        }
        throw new RuntimeException('This driver does not support retrieving paths.');
    }
}