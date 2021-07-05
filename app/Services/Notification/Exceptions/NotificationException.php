<?php


namespace App\Services\Notification\Exceptions;


use CodeIgniter\Exceptions\DebugTraceableTrait;
use CodeIgniter\Exceptions\ExceptionInterface;
use RuntimeException;

class NotificationException extends RuntimeException implements ExceptionInterface
{
    use DebugTraceableTrait;

    /**
     * Thrown when specified driver was not found.
     *
     * @return NotificationException
     */
    public static function channelNotFound()
    {
        return new static(lang('Notification.channelNotFound') ?? 'Channel not found, try email or database');
    }

}