<?php

namespace App\Services\Auth;

use App\Config\Auth;

class AuthService
{
    /**
     * Group collection.
     *
     * @var array
     */
    protected $authGroups = [];

    /**
     * Current active group.
     *
     * @var string
     */
    protected $group;

    /**
     * Current active driver.
     *
     * @var Authenticator
     */
    protected $driver;

    /**
     * AuthService constructor.
     *
     * @param null $group
     * @param Auth|null $config
     */
    public function __construct($group = null, Auth $config = null)
    {
        $config = $config ?? new Auth();

        $this->group = $group ?? $config->defaultGroup;

        $authDriver = $config->authGroup[$this->group]['driver'];

        $this->driver = new $authDriver($config, $this->group);

        $this->authGroups[$group] = $this->driver;
    }

    /**
     * Setup authenticator group.
     *
     * @param $group
     * @param Auth|null $config
     * @return AuthService
     */
    public function group($group, Auth $config = null)
    {
        if (!isset($this->authGroups[$group])) {
            $config = $config ?? new Auth();
            $authDriver = $config->authGroup[$group]['driver'];

            $this->group = $group;
            $this->driver = new $authDriver($config, $group);
            $this->authGroups[$group] = $this->driver;
        }

        $this->authGroups[$group]->group($group);

        return $this;
    }

    /**
     * Login user by credential.
     *
     * @param $credentials
     * @param false $remember
     * @return mixed
     */
    public function login($credentials, $remember = false)
    {
        return ($this->authGroups[$this->group] ?? $this->driver)->login($credentials, $remember);
    }

    /**
     * Logout user.
     *
     * @return mixed
     */
    public function logout()
    {
        return ($this->authGroups[$this->group] ?? $this->driver)->logout();
    }

    /**
     * Check is user logged in.
     *
     * @return mixed
     */
    public function isLoggedIn()
    {
        return ($this->authGroups[$this->group] ?? $this->driver)->isLoggedIn();
    }

    /**
     * Get login data and fallback if empty.
     *
     * @param $value
     * @param string $default
     * @return mixed|string
     */
    public function authData($value, $default = '')
    {
        return ($this->authGroups[$this->group] ?? $this->driver)->authData($value) ?? $default;
    }

    /**
     * Get user instance or by key.
     *
     * @param null $key
     * @param null $default
     * @return mixed|null
     */
    public function user($key = null, $default = null)
    {
        return ($this->authGroups[$this->group] ?? $this->driver)->user($key) ?? $default;
    }

    /**
     * Get user id.
     *
     * @return mixed|null
     */
    public function userId()
    {
        return $this->user('id');
    }
}