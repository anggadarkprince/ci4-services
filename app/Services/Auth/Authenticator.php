<?php

namespace App\Services\Auth;

use App\Entities\User;

/**
 * Contract to manage user authentication.
 *
 * Interface Authenticator
 */
interface Authenticator
{
    /**
     * Setup authenticator group.
     *
     * @param $group
     * @return mixed
     */
    public function group($group);

	/**
	 * Attempt to login credential.
	 *
	 * @param $credential
	 * @param $remember
	 * @return mixed
	 */
	public function login($credential, $remember);

    /**
     * Attempt to login by instance of user entity.
     *
     * @param User $user
     * @return mixed
     */
	public function loginByUser(User $user);

    /**
     * Attempt to login by user id.
     *
     * @param $id
     * @return mixed
     */
	public function loginById($id);

	/**
	 * Logout auth data.
	 *
	 * @return mixed
	 */
	public function logout();

	/**
	 * Check if authentication data exist and not empty.
	 *
	 * @return mixed
	 */
	public function isLoggedIn();

	/**
	 * Get auth data.
	 *
	 * @param $key
	 * @return mixed
	 */
	public function authData($key);

	/**
	 * Get user data.
	 *
	 * @param $key
	 * @return mixed
	 */
	public function user($key = null);
}
