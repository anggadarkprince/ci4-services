<?php

namespace App\Config;

use App\Services\Auth\Drivers\AuthJWT;
use App\Services\Auth\Drivers\AuthSession;
use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Auth Handler
     * --------------------------------------------------------------------------
     *
     * The name of the preferred handler that should be used. If for some reason
     * it is not available, the default will be used in its place.
     *
     * @var string
     */
    public $defaultGroup = 'web';

    public $authGroup = [
        'web' => [
            'name' => 'web',
            'driver' => AuthSession::class,
            'userTable' => 'users',
            'tokenTable' => 'users',
            'dbGroup' => 'default',
        ],
        'customer' => [
            'name' => 'customer',
            'driver' => AuthSession::class,
            'userTable' => 'customers',
            'tokenTable' => 'user_tokens',
            'dbGroup' => 'default',
        ],
        'api' => [
            'name' => 'api',
            'driver' => AuthJWT::class,
            'userTable' => 'users',
            'dbGroup' => 'default',
        ]
    ];

    public $session = [
        'driver' => 'session',
    ];

    public $jwt = [
        'driver' => 'jwt',
        'cookie' => 'jwt-token',
        'cookieRefresh' => 'jwt-token-refresh',
        'secret' => 'secret',
        'signMethod' => 'HS256',
        'expired' => 3600,
        'domain' => 'localhost',
        'path' => '/',
    ];

}