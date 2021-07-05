<?php

namespace App\Services\Notification;

class Notification
{
    /**
     * Get type of notification should be sent.
     * 
     * @param $channels
     * @return array
     */
    public function via($channels)
    {
        return [];
    }
    
    /**
     * Mail notification.
     *
     * @param $receiver
     * @return mixed
     */
    public function toMail($receiver)
    {
        return null;
    }

    /**
     * Whatsapp notification.
     *
     * @param $receiver
     * @return mixed
     */
    public function toWhatsapp($receiver)
    {
        return null;
    }

    /**
     * Push notification.
     *
     * @param $receiver
     * @return mixed
     */
    public function toPusher($receiver)
    {
        return null;
    }

    /**
     * Database notification.
     *
     * @param $receiver
     * @return mixed
     */
    public function toDatabase($receiver)
    {
        return null;
    }
}