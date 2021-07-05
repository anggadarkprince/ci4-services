<?php

namespace App\Services\Auth\Drivers;

use App\Config\Auth;
use App\Entities\User;
use App\Services\Auth\Authenticator;
use App\Services\Auth\Exceptions\AuthException;
use Config\Database;
use Config\Services;

class AuthSession implements Authenticator
{
    protected $config;

    protected $groupPrefix = '';

    protected $table = 'users';

    protected $tableToken = 'users';

    protected $dbGroup = 'default';

    protected $db;

    /**
     * AuthSession constructor.
     *
     * @param Auth|null $config
     * @param string $group
     */
    public function __construct(Auth $config = null, $group = 'web')
    {
        helper('text');
        helper('cookie');

        $this->config = $config;

        $this->group($group);

        $authGroupConfig = $config->authGroup[$group];

        $this->table = $authGroupConfig['userTable'] ?? 'users';

        $this->tableToken = $authGroupConfig['tokenTable'] ?? 'users';

        $this->dbGroup = $authGroupConfig['dbGroup'] ?? 'default';

        $this->db = Database::connect($this->dbGroup);
    }

    /**
     * Setup authenticator group.
     *
     * @param $group
     * @return mixed
     */
    public function group($group)
    {
        if ($group != $this->config->defaultGroup) {
            $this->groupPrefix = $group . '-';
        } else {
            $this->groupPrefix = '';
        }

        return $this;
    }

    /**
     * Attempt to login credential.
     *
     * @param $credential
     * @param $remember
     * @return bool|mixed
     */
    public function login($credential, $remember)
    {
        $session = Services::session();

        $userCondition = $credential;
        unset($userCondition['password']);

        $user = $this->db->table($this->table)->where($userCondition)->get()->getRow();

        if (!empty($user)) {
            $hashedPassword = $user->password;
            if (password_verify($credential['password'], $hashedPassword)) {
                if (password_needs_rehash($hashedPassword, PASSWORD_BCRYPT)) {
                    $newHash = password_hash($credential['password'], PASSWORD_BCRYPT);
                    $this->db->table($this->table)->update(['password' => $newHash], ['id' => $user['id']]);
                }

                $session->set([
                    $this->groupPrefix . 'auth.id' => $user->id,
                    $this->groupPrefix . 'auth.is_logged_in' => true
                ]);

                if ($remember || $remember == 'on') {
                    $rememberToken = random_string('alnum', 32);

                    $hasRememberTokenField = $this->db->fieldExists('remember_token', $this->table);
                    $differentTokenTableWithUser = $this->db->tableExists($this->tableToken) && $this->table != $this->tableToken;

                    if ($hasRememberTokenField) {
                        $this->db->table($this->table)->update(['remember_token', $rememberToken], ['id' => $user->id]);
                    } elseif ($differentTokenTableWithUser) {
                        $createToken = $this->db->table($this->tableToken)->insert([
                            'email' => $user->email,
                            'type' => 'REMEMBER',
                            'token' => $rememberToken,
                            'max_activation' => 1,
                            'expired_at' => null
                        ]);

                        if ($createToken) {
                            set_cookie('remember_token', $rememberToken, 3600 * 24 * 30);
                            $session->set([
                                $this->groupPrefix . 'auth.remember_me' => true,
                                $this->groupPrefix . 'auth.remember_token' => $rememberToken
                            ]);
                        }
                    }
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Attempt to login by instance of user entity.
     *
     * @param User $user
     * @return mixed
     */
    public function loginByUser(User $user)
    {
        if (is_null($user) || empty($user)) {
            throw AuthException::userNotFound();
        }

        $session = Services::session();

        $session->set([
            $this->groupPrefix . 'auth.id' => $user->id,
            $this->groupPrefix . 'auth.is_logged_in' => true
        ]);

        return true;
    }

    /**
     * Attempt to login by user id.
     *
     * @param $id
     * @return mixed
     */
    public function loginById($id)
    {
        $user = $this->db->table($this->table)->where('id', $id)->get()->getRow();

        return $this->loginByUser($user);
    }

    /**
     * Logout auth data.
     *
     * @return bool
     */
    public function logout()
    {
        $session = Services::session();

        if ($session->has($this->groupPrefix . 'auth.id')) {
            $session->remove([
                $this->groupPrefix . 'auth.id',
                $this->groupPrefix . 'auth.is_logged_in',
                $this->groupPrefix . 'auth.remember_me',
                $this->groupPrefix . 'auth.remember_token',
                $this->groupPrefix . 'auth.throttle',
                $this->groupPrefix . 'auth.throttle_expired'
            ]);
            return true;
        }
        return false;
    }

    /**
     * Check if authentication data exist and not empty.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        $session = Services::session();

        $sessionUserId = $session->get($this->groupPrefix . 'auth.id');

        return !empty($sessionUserId);
    }

    /**
     * Get auth data.
     *
     * @param $key
     * @return string|null
     */
    public function authData($key)
    {
        $session = Services::session();

        if ($session->has($key)) {
            return $session->get($key);
        }
        return null;
    }

    /**
     * Get user data.
     *
     * @param null $key
     * @return mixed|void
     */
    public function user($key = null)
    {
        $user = $this->db->table($this->table)
            ->where(['id' => $this->authData($this->groupPrefix . 'auth.id')])
            ->get()
            ->getRow();

        if (!empty($key)) {
            if ($user instanceof User) {
                return $user->$key ?? null;
            }
            if (is_array($user)) {
                return $user[$key] ?? null;
            }
            return null;
        }

        return $user;
    }
}
