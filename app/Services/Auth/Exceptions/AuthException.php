<?php


namespace App\Services\Auth\Exceptions;


use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class AuthException extends RuntimeException implements ExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * Thrown when specified driver was not found.
     *
     * @return AuthException
     */
    public static function userNotFound()
    {
        return new static(lang('Auth.userNotFound') ?? 'User not found');
    }

}