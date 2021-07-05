<?php

namespace App\Services\Notification\Messages;

use InvalidArgumentException;

class PusherNotificationBuilder
{
    private static $instance;

    protected $data = [];

    private function __construct()
    {
        // create object via newData()
    }

    /**
     * Build new push config message.
     *
     * @return PusherNotificationBuilder
     */
    public static function newData()
    {
        if (is_null(self::$instance)) {
            self::$instance = new PusherNotificationBuilder();
        }
        self::$instance->reset();

        return self::$instance;
    }

    /**
     * Reset built push data.
     *
     * @return $this
     */
    public function reset()
    {
        $this->data = [];

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
     * Set payload data.
     *
     * @param $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        $this->data['payload'] = $payload;

        return $this;
    }

    /**
     * Build and return push config message.
     *
     * @return array
     */
    public function build()
    {
        if (!isset($this->data['payload'])) {
            throw new InvalidArgumentException("Payload is required, try to call setPayload()");
        }

        return $this->data;
    }

}