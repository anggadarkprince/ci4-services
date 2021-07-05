<?php

namespace App\Services\Notification;

trait ReceiveNotification
{
    /**
     * Send the given notification.
     *
     * @param $notification
     * @param array|null $channels
     */
    public function notify($notification, array $channels = null)
    {
        (new NotificationManager())->send($this, $notification, $channels);
    }

    /**
     * Get the notification routing information for the given channel.
     *
     * @param string $channel
     * @param null $notification
     * @return mixed
     */
    public function keyNotification(string $channel, $notification = null)
    {
        /**
         * Find method that return key of notifier:
         * keyNotificationWhatsapp() or keyNotificationMail() where class use this traits
         */
        if (method_exists($this, $method = 'keyNotification' . ucfirst($channel))) {
            return $this->{$method}($notification);
        }

        /**
         * Get from stocked channel and default field of the object.
         */
        switch ($channel) {
            case 'array':
            case 'database':
                return 'notifications';
            case 'mail':
            case 'email':
                return $this->email ?? $this->email_address ?? '';
            case 'wa':
            case 'whatsapp':
            case 'chat':
                return $this->mobile_phone ?? $this->mobile_contact ?? '';
            case 'web':
            case 'pusher':
            case 'broadcast':
                return $this->id ?? '';
        }
    }
}