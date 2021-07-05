<?php

namespace App\Config;

use App\Services\Notification\Channels\DatabaseChannel;
use App\Services\Notification\Channels\MailChannel;
use App\Services\Notification\Channels\PusherChannel;
use App\Services\Notification\Channels\WhatsappChannel;
use CodeIgniter\Config\BaseConfig;
use Config\Database;
use Config\Email;

class Notification extends BaseConfig
{
    const DATABASE_CHANNEL = 'database';
    const MAIL_CHANNEL = 'mail';
    const WHATSAPP_CHANNEL = 'whatsapp';
    const PUSHER_CHANNEL = 'pusher';

    public $configs = [
        'database' => Database::class,
        'mail' => Email::class,
        'whatsapp' => Whatsapp::class,
        'pusher' => Pusher::class,
    ];

    public $channels = [
        'database' => DatabaseChannel::class,
        'mail' => MailChannel::class,
        'whatsapp' => WhatsappChannel::class,
        'pusher' => PusherChannel::class,
    ];
}