<?php

namespace App\Services\Storage\Drivers;

use App\Config\Storage;
use App\Services\Storage\Exceptions\StorageException;
use Exception;

class LocalDisk extends PublicDisk
{
    protected $disk;
    protected $basePath;
    protected $baseUrl;
    protected $mode;

    /**
     * LocalDisk constructor.
     *
     * @param Storage $config
     * @throws StorageException|Exception
     */
    public function __construct(Storage $config)
    {
        parent::__construct($config);

        if (!property_exists($config, 'local')) {
            $config->public = [
                'basePath' => WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'local',
            ];
        }

        $this->basePath = rtrim($config->local['basePath'], '/') . '/';
        $this->baseUrl = '/';

        if (!is_really_writable($this->basePath)) {
            throw StorageException::unableToWrite($this->basePath);
        }

        $this->mode = $config->file['mode'] ?? 0640;
    }

    /**
     * Create a directory.
     *
     * @param $path
     * @param array $options
     * @return bool
     */
    public function makeDirectory($path, $options = [])
    {
        $baseSource = rtrim($options['base_source'] ?? $this->basePath, '/') . '/';
        $mode = $options['mode'] ?? 0700;

        if (!file_exists($baseSource . $path) && is_writable($baseSource . $path)) {
            return mkdir($baseSource . $path, $mode, true);
        }
        return false;
    }

}