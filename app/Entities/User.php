<?php


namespace App\Entities;

use App\Services\Auth\CanLogin;
use App\Services\Notification\Channels\WhatsappChannel;
use App\Services\Notification\ReceiveNotification;
use CodeIgniter\Entity\Entity;

class User extends Entity
{
    use CanLogin, ReceiveNotification;

    const STATUS_ACTIVATED = 'ACTIVATED';
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUSPENDED = 'SUSPENDED';

    public function keyNotificationMail($notification)
    {
        return $this->email ?? $this->email_address ?? '';
    }

    public function keyNotificationWhatsapp($notification)
    {
        return WhatsappChannel::detectChatId($this->mobile_phone ?? '');
    }
}