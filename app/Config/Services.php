<?php

namespace Config;

use App\Config\Auth;
use App\Config\Storage;
use App\Services\Auth\AuthService;
use App\Services\Notification\NotificationChannel;
use App\Services\Notification\NotificationManager;
use App\Services\Storage\FileSystem;
use App\Services\Storage\StorageFactory;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    // public static function example($getShared = true)
    // {
    //     if ($getShared)
    //     {
    //         return static::getSharedInstance('example');
    //     }
    //
    //     return new \CodeIgniter\Example();
    // }

    /**
     * Auth service based on group user credential living.
     *
     * @param null $group
     * @param Auth|null $config
     * @param bool $getShared
     * @return AuthService|mixed
     */
    public static function auth($group = null, Auth $config = null, $getShared = true)
    {
        $config = $config ?? new Auth();
        $group = $group ?? $config->defaultGroup;

        if ($getShared) {
            return static::getSharedInstance('auth', $group, $config);
        }

        return new AuthService($group, $config);
    }

    /**
     * The storage class provides a simple way to store and retrieve
     * complex file to local or external service.
     *
     * @param null $disk
     * @param Storage|null $config
     * @param boolean $getShared
     *
     * @return FileSystem
     */
    public static function storage($disk = null, Storage $config = null, bool $getShared = true)
    {
        $config = $config ?? new Storage();

        if ($getShared) {
            return static::getSharedInstance('storage', $disk, $config);
        }

        return StorageFactory::getDisk($config, $disk);
    }

    /**
     * The notification class provides a simple way notify user
     * via various channel.
     *
     * @param boolean $getShared
     *
     * @return NotificationManager
     */
    public static function notification(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('notification');
        }

        return new NotificationManager();
    }
}
