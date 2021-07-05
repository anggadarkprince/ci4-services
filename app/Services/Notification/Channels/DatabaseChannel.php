<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\Notification;
use App\Services\Notification\NotificationChannel;
use Config\Database;

class DatabaseChannel implements NotificationChannel
{
    protected $defaultGroup;

    protected $notificationTable = 'notifications';

    protected $db;

    /**
     * DatabaseChannel constructor.
     *
     * @param Database|null $config
     */
    public function __construct(Database $config = null)
    {
        $config = $config ?? new Database();

        $this->defaultGroup = $config->notification ?? $config->defaultGroup;

        $this->db = Database::connect($this->defaultGroup, true);
    }

    /**
     * Get instantiate of channel.
     *
     * @param $config
     * @return DatabaseChannel
     */
    public static function factory($config = null)
    {
        return new DatabaseChannel($config = $config ?? new Database());
    }

    /**
     * Send notification to notifiables.
     *
     * @param $notifiable
     * @param Notification $notification
     * @return mixed
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toDatabase($notifiable);

        if (!isset($data['id_user'])) {
            $data['id_user'] = $notifiable->id ?? $notifiable;
        }

        return $this->db->table($this->notificationTable)->insert($data);
    }

    /**
     * Read notification.
     *
     * @param $id
     * @return bool
     */
    public function read($id)
    {
        return $this->db->table($this->notificationTable)->update(['is_read' => true], ['id' => $id]);
    }

    /**
     * Read all notification.
     *
     * @param null $notifiable
     * @return bool
     */
    public function readAll($notifiable = null)
    {
        $condition = ['is_read' => false];

        if (!empty($notifiable)) {
            $condition['id_user'] = $notifiable->id ?? $notifiable;
        }

        return $this->db->table($this->notificationTable)->update(['is_read' => true], $condition);
    }
}