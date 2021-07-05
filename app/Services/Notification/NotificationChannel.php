<?php

namespace App\Services\Notification;

interface NotificationChannel
{
    /**
     * Send notification to notifiables.
     *
     * @param $notifiable
     * @param Notification $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification);

    /**
     * Instantiate concrete object of channel.
     *
     * @param null $config
     * @return NotificationChannel
     */
    public static function factory($config = null);
}