<?php

namespace App\Services\Auth\Drivers;

use App\Config\Auth;
use App\Entities\User;
use App\Services\Auth\Authenticator;
use App\Services\Auth\Exceptions\AuthException;
use Config\Database;
use DateTime;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

class AuthJWT implements Authenticator
{
    protected $config;

    protected $groupPrefix = '';

    protected $table = 'users';

    protected $tableToken = 'users';

    protected $dbGroup = 'default';

    protected $db;

    protected $jwtCookie;
    protected $jwtCookieRefresh;
    protected $jwtSecret;
    protected $jwtSignMethod;
    protected $jwtExpired;
    protected $jwtRefreshExpired;
    protected $jwtDomain;
    protected $jwtPath;

    protected $currentAccessToken;
    protected $currentRefreshToken;

    /**
     * AuthJWT constructor.
     *
     * @param Auth $config
     * @param string $group
     */
    public function __construct(Auth $config, $group = 'web')
    {
        helper('text');
        helper('cookie');

        $this->config = $config;

        $authGroupConfig = $config->authGroup[$group];

        $this->table = $authGroupConfig['userTable'] ?? 'users';

        $this->tableToken = $authGroupConfig['tokenTable'] ?? 'users';

        $this->dbGroup = $authGroupConfig['dbGroup'] ?? 'default';

        $this->db = Database::connect($this->dbGroup);

        $this->jwtCookie = $config->jwt['cookie'];
        $this->jwtCookieRefresh = $config->jwt['cookieRefresh'];
        $this->jwtSecret = $config->jwt['secret'] ?? 'jwt-secret';
        $this->jwtSignMethod = $config->jwt['signMethod'] ?? 'HS256';
        $this->jwtExpired = (int)($config->jwt['expired'] ?? 3600);
        $this->jwtRefreshExpired = $this->jwtExpired * 10;
        $this->jwtDomain = $config->jwt['domain'] ?? 'localhost';
        $this->jwtPath = '/';

        $this->group($group);
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

        $this->jwtCookie = $this->groupPrefix . $this->config->jwt['cookie'];
        $this->jwtCookieRefresh = $this->groupPrefix . $this->config->jwt['cookieRefresh'];

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

                return $this->issueToken($user, $remember);
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

        return $this->issueToken($user, false);
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
     * Issue new pair access and refresh tokens.
     *
     * @param $user
     * @param false $remember
     * @return array
     */
    public function issueToken($user, $remember = false)
    {
        // create access token
        $this->currentAccessToken = JWT::encode([
            'auth.id' => $user->id,
            'auth.is_logged_in' => true
        ], $this->jwtSecret, $this->jwtSignMethod);

        if ($remember || $remember == 'on') {
            $this->jwtExpired = ($this->jwtExpired * 24 * 7);
        }

        $cookie = cookie($this->jwtCookie)
            ->withValue($this->currentAccessToken)
            ->withExpires(time() + $this->jwtExpired)
            ->withSecure(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
            ->withHTTPOnly(true);
        cookies()->put($cookie)->dispatch();

        // create refresh token
        if ($this->db->tableExists($this->tableToken)) {
            $this->currentRefreshToken = random_string('alnum', 48);
            try {
                $expiredDate = new DateTime('@' . (time() + $this->jwtRefreshExpired));
                $refreshTokenExpiredAt = $expiredDate->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $refreshTokenExpiredAt = date('Y-m-d H:i:s');
            }
            $createRefreshToken = $this->db->table($this->tableToken)->insert([
                'email' => $user->email,
                'type' => 'REFRESH TOKEN',
                'token' => $this->currentRefreshToken,
                'max_activation' => 1,
                'expired_at' => $refreshTokenExpiredAt
            ]);

            if ($createRefreshToken) {
                $refreshCookie = cookie($this->jwtCookieRefresh)
                    ->withValue($this->currentRefreshToken)
                    ->withExpires(time() + $this->jwtRefreshExpired)
                    ->withSecure(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on")
                    ->withHTTPOnly(true);
                cookies()->put($refreshCookie)->dispatch();
            }
        }

        return [
            'access_token' => $this->currentAccessToken,
            'refresh_token' => $this->currentRefreshToken
        ];
    }

    /**
     * Issue access token from refresh token.
     *
     * @param $refreshToken
     * @return array|false|null
     */
    public function issueFromRefreshToken($refreshToken)
    {
        if (!empty($refreshToken)) {
            $userToken = $this->db->table($this->tableToken)->where([
                'token' => $refreshToken,
                'type' => 'REFRESH TOKEN',
                'max_activation >' => 0,
                'expired_at >= DATE(NOW())' => null
            ])
                ->get()
                ->getRow();

            $user = $this->db->table($this->table)
                ->where('email', $userToken->email ?? '')
                ->get()
                ->getRow();

            if (!empty($user)) {
                $this->db->table($this->tableToken)->delete(['id' => $userToken->id]);
                return $this->issueToken($user);
            }
        }
        return null;
    }

    /**
     * Logout auth data.
     *
     * @return bool
     */
    public function logout()
    {
        delete_cookie($this->jwtCookie);
        delete_cookie($this->jwtCookieRefresh);
        cookies()->dispatch();

        return true;
    }

    /**
     * Check if authentication data exist and not empty.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return !empty($this->authData('auth.id'));
    }

    /**
     * Get auth data.
     *
     * @param $key
     * @return string|null
     */
    public function authData($key)
    {
        $jwtToken = self::getBearerToken() ?? get_cookie($this->jwtCookie) ?? $this->getCookie($this->jwtCookie) ?? '';
        $jwtRefreshToken = get_cookie($this->jwtCookieRefresh) ?? $this->getCookie($this->jwtCookieRefresh) ?? '';

        if (!empty($jwtToken)) {
            try {
                $payload = JWT::decode($jwtToken, $this->jwtSecret, [$this->jwtSignMethod]);

                return $payload->$key ?? null;
            } catch (ExpiredException $exception) {
                // expired by token setting signature
                log_message('warning', $exception->getMessage());
                $tokens = $this->issueFromRefreshToken($jwtRefreshToken);
                if (!empty($tokens)) {
                    return $this->authData($key);
                }
            } catch (SignatureInvalidException $exception) {
                log_message('notice', $exception->getMessage());
            } catch (Exception $exception) {
                log_message('error', $exception->getMessage());
            }
        } else {
            // maybe expired by cookie (token empty)
            $tokens = $this->issueFromRefreshToken($jwtRefreshToken);
            if (!empty($tokens)) {
                return $this->authData($key);
            }
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
            ->where(['id' => $this->authData('auth.id')])
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

    /**
     * Get cookie immediately before sent to browser.
     * caution: not working on CLI
     *
     * see http://tools.ietf.org/html/rfc6265#section-4.1.1
     * @param $name
     * @return mixed
     */
    protected function getCookie($name)
    {
        $cookies = [];
        $headers = headers_list();
        foreach ($headers as $header) {
            if (strpos($header, 'Set-Cookie: ') === 0) {
                $value = str_replace('&', urlencode('&'), substr($header, 12));
                parse_str(current(explode(';', $value, 1)), $pair);
                $cookies = array_merge_recursive($cookies, $pair);
            }
        }

        if (isset($cookies[$name]) && is_string($cookies[$name])) {
            $result = explode(';', $cookies[$name]);
            return $result[0];
        }
        return '';
    }

    /**
     * get authorization header.
     *
     * @return string|null
     */
    protected static function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) { // Apache
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    /**
     * Get bearer token.
     *
     * @return mixed|null
     */
    protected static function getBearerToken()
    {
        $headers = self::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
