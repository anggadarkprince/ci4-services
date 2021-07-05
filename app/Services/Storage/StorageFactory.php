<?php


namespace App\Services\Storage;


use App\Config\Storage;
use App\Services\Storage\Exceptions\StorageException;

class StorageFactory
{
    /**
     * Get disk of storage.
     *
     * @param Storage $config
     * @param string|null $disk
     * @return mixed
     */
    public static function getDisk(Storage $config, string $disk = null)
    {
        if (!isset($config->validDrivers) || !is_array($config->validDrivers)) {
            throw StorageException::invalidDrivers();
        }

        $disk = !empty($disk) ? $disk : $config->disk;

        if (!array_key_exists($disk, $config->validDrivers)) {
            throw StorageException::driverNotFound();
        }

        // Get an instance of the disk.
        return new $config->validDrivers[$disk]($config);
    }
}