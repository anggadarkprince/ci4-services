<?php


namespace App\Services\Storage\Exceptions;


use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class StorageException extends RuntimeException implements ExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * Thrown when invalid content is trying to be stored.
     *
     * @return StorageException
     */
    public static function contentIsInvalid()
    {
        return new static(lang('Storage.contentIsInvalid') ?? 'Content is invalid to be stored');
    }

    /**
     * Thrown when driver has no permission to write to disk.
     *
     * @param string $path
     *
     * @return StorageException
     */
    public static function unableToWrite(string $path)
    {
        return new static(lang('Storage.unableToWrite', [$path]) ?? 'Unable to write to disk');
    }

    /**
     * Thrown when an unrecognized driver is used.
     *
     * @return StorageException
     */
    public static function invalidDrivers()
    {
        return new static(lang('Storage.invalidDrivers') ?? 'Invalid driver, check disk in the config file');
    }

    /**
     * Thrown when specified driver was not found.
     *
     * @return StorageException
     */
    public static function driverNotFound()
    {
        return new static(lang('Storage.driverNotFound') ?? 'Disk not found, try local or public disk');
    }
}