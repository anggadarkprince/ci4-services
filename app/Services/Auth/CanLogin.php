<?php


namespace App\Services\Auth;


trait CanLogin
{
    /**
     * Get key of username.
     *
     * @return string
     */
    public function keyAuthUsername()
    {
        return $this->username ?? $this->email ?? '';
    }

    /**
     * Get key of password.
     *
     * @return string
     */
    public function keyAuthPassword()
    {
        return $this->password ?? '';
    }
}