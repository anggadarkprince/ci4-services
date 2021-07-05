<?php

namespace App\Services\Notification;

use App\Config\Pusher;
use App\Config\Whatsapp;
use App\Services\Notification\Channels\DatabaseChannel;
use App\Services\Notification\Channels\MailChannel;
use App\Services\Notification\Channels\PusherChannel;
use App\Services\Notification\Channels\WhatsappChannel;
use App\Services\Notification\Exceptions\NotificationException;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Model;
use Config\Database;
use Config\Email;

class NotificationManager
{
    private $viaChannel = null;

    /**
     * Fluent method to set channel.
     *
     * @param $viaChannels
     * @return $this
     */
    public function via($viaChannels)
    {
        $this->viaChannel = $viaChannels;

        return $this;
    }

    /**
     * Send notification to notifiables.
     *
     * @param $notifiables
     * @param $notification
     * @param array|null $channels
     */
    public function send($notifiables, $notification, array $channels = null)
    {
        $notifiables = $this->formatReceivers($notifiables);

        foreach ($notifiables as $notifiable) {
            if (empty($viaChannels = $channels ?: $this->viaChannel ?: $notification->via($notifiable))) {
                continue;
            }

            foreach ((array)$viaChannels as $channel) {
                $this->sendToReceiver($channel, $notifiable, $notification);
            }
        }
    }

    /**
     * Check receiver if wrapped in array.
     *
     * @param $notifiables
     * @return array|array[]|mixed
     */
    protected function formatReceivers($notifiables)
    {
        $instanceOfModel = $notifiables instanceof Model;
        $instanceOfEntity = $notifiables instanceof Entity;
        $isNotArrayOrSingleArray = !is_array($notifiables) || !key_exists(0, $notifiables);

        if ($instanceOfModel || $instanceOfEntity || $isNotArrayOrSingleArray) {
            return [$notifiables];
        }

        return $notifiables;
    }

    /**
     * Send notification via proper drivers.
     *
     * @param $channel
     * @param $notifiable
     * @param $notification
     * @return bool|void
     */
    protected function sendToReceiver($channel, $notifiable, $notification)
    {
        return $this->resolveDriver($channel)->send($notifiable, $notification);
    }

    /**
     * Resolve proper channel for notification.
     *
     * @param $channel
     * @return NotificationChannel
     */
    protected function resolveDriver($channel)
    {
        // Return stocked driver by some alias channel
        switch ($channel) {
            case 'mail':
            case 'email':
                return new MailChannel(new Email());
            case 'wa':
            case 'whatsapp':
            case 'chat':
                return new WhatsappChannel(new Whatsapp());
            case 'array':
            case 'database':
                return new DatabaseChannel(new Database());
            case 'web':
            case 'pusher':
            case 'broadcast':
                return new PusherChannel(new Pusher());
        }

        // Resolve driver by convenience naming style
        $guessedClass = ucfirst($channel);
        $channelNamespace = "App\\Services\\Notification\\Channels\\{$guessedClass}Channel";
        $configNamespace = "App\\Config\\{$guessedClass}";

        if (class_exists($channelNamespace) && class_exists($configNamespace)) {
            return new $channelNamespace(new $configNamespace());
        }

        throw NotificationException::channelNotFound();
    }

    /**
     * Read notification.
     *
     * @param $id
     */
    public function read(int $id)
    {
        (new DatabaseChannel())->read($id);
    }

    /**
     * Read all notification.
     *
     * @param $notifiables
     */
    public function readAll($notifiables = null)
    {
        $databaseChannel = new DatabaseChannel();
        $isNotArrayOrSingleArray = !is_array($notifiables) || !key_exists(0, $notifiables);

        if ($isNotArrayOrSingleArray) {
            $notifiables = [$notifiables];
        }

        foreach ($notifiables as $notifiable) {
            $databaseChannel->readAll($notifiable);
        }
    }
}