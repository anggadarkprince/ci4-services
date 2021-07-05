<?php

namespace App\Services\Notification\Messages;

use InvalidArgumentException;

class DatabaseNotificationBuilder
{
    private static $instance;

    protected $data = [];

    private function __construct()
    {
        // create object via newData()
    }

    /**
     * Build new database config message.
     *
     * @return DatabaseNotificationBuilder
     */
    public static function newData()
    {
        if (is_null(self::$instance)) {
            self::$instance = new DatabaseNotificationBuilder();
        }
        self::$instance->reset();

        return self::$instance;
    }

    /**
     * Reset built data.
     *
     * @return $this
     */
    public function reset()
    {
        $this->data = [];

        return $this;
    }

    /**
     * Set user id.
     *
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->data['id_user'] = $userId;

        return $this;
    }

    /**
     * Set related id.
     *
     * @param $relatedId
     * @return $this
     */
    public function setRelatedId($relatedId)
    {
        $this->data['id_related'] = $relatedId;

        return $this;
    }

    /**
     * Set channel.
     *
     * @param $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->data['channel'] = $channel;

        return $this;
    }

    /**
     * Set event.
     *
     * @param $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->data['event'] = $event;

        return $this;
    }

    /**
     * Set data.
     *
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data['data'] = json_encode($data);

        return $this;
    }

    /**
     * Build and return database config message.
     *
     * @return array
     */
    public function build()
    {
        if (!isset($this->data['data'])) {
            throw new InvalidArgumentException("Data is required, try to call setPayload()");
        }

        return $this->data;
    }

}