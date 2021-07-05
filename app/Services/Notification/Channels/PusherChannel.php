<?php

namespace App\Services\Notification\Channels;

use App\Config\Pusher as PusherConfig;
use App\Services\Notification\Notification;
use App\Services\Notification\NotificationChannel;
use GuzzleHttp\Exception\GuzzleException;
use Pusher\Pusher;
use Pusher\PusherException;

class PusherChannel implements NotificationChannel
{
    protected $client;

    protected $appKey;

    protected $appSecret;

    protected $appId;

    protected $cluster;

    protected $encrypted;

    /**
     * PusherChannel constructor.
     *
     * @param PusherConfig $config
     */
    public function __construct(PusherConfig $config)
    {
        $config = $config ?? new PusherConfig();

        $this->appKey = $config->appKey;

        $this->appSecret = $config->appSecret;

        $this->appId = $config->appId;

        $this->cluster = $config->cluster;

        $this->cluster = $config->cluster;

        $this->encrypted = $config->encrypted;

        try {
            $this->client = new Pusher(
                $this->appKey,
                $this->appSecret,
                $this->appId,
                [
                    'cluster' => $this->cluster,
                    'encrypted' => $this->encrypted
                ]
            );
        } catch (PusherException $e) {
            log_message('error', PusherChannel::class . ' : ' . $e->getMessage());
        }
    }

    /**
     * Get instantiate of channel.
     *
     * @param $config
     * @return PusherChannel
     */
    public static function factory($config = null)
    {
        return new PusherChannel($config = $config ?? new PusherConfig());
    }

    /**
     * Send notification to notifiables.
     *
     * @param $receiver
     * @param Notification $notification
     * @return mixed
     */
    public function send($receiver, Notification $notification)
    {
        $message = $notification->toPusher($receiver);

        $channel = $message['channel'];
        $event = $message['event'];
        $payload = $message['payload'];

        try {
            return $this->client->trigger($channel, $event, $payload);
        } catch (PusherException $e) {
            log_message('error', PusherChannel::class . ' : ' . $e->getMessage());
        } catch (GuzzleException $e) {
            log_message('error', PusherChannel::class . ' : http request error - ' . $e->getMessage());
        }
    }
}