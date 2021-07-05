<?php

namespace App\Services\Notification\Channels;

use App\Config\Whatsapp;
use App\Services\Notification\Notification;
use App\Services\Notification\NotificationChannel;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WhatsappChannel implements NotificationChannel
{
    protected $client;

    protected $baseUri;

    protected $token;

    protected $secure;

    protected $sandboxNumber;

    /**
     * WhatsappChannel constructor.
     *
     * @param Whatsapp|null $config
     */
    public function __construct(Whatsapp $config = null)
    {
        $config = $config ?? new Whatsapp();

        $this->baseUri = $config->apiUrl;

        $this->token = $config->apiToken;

        $this->secure = $config->secure;

        $this->sandboxNumber = $config->sandboxNumber;

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'verify' => boolval($this->secure)
        ]);
    }

    /**
     * Get instantiate of channel.
     *
     * @param $config
     * @return WhatsappChannel
     */
    public static function factory($config = null)
    {
        return new WhatsappChannel($config = $config ?? new Whatsapp());
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
        $message = $notification->toWhatsapp($receiver);

        $sandboxIsSet = !empty($this->sandboxNumber);
        $chatIdIsNotSet = !isset($message['payload']['chatId']);
        $phoneNumberIsNotSet = !isset($message['payload']['phone']);

        if ($sandboxIsSet) {
            $message['payload']['chatId'] = self::detectChatId($this->sandboxNumber);
        } else if ($chatIdIsNotSet && $phoneNumberIsNotSet) {
            if (empty($sendTo = $this->resolveKeyValue($receiver, $notification))) {
                return false;
            }
            $message['payload']['chatId'] = $sendTo;
        }

        try {
            $response = $this->client->request($message['method'], $message['url'], [
                'query' => ['token' => $this->token],
                'form_params' => $message['payload']
            ]);
            $result = json_decode($response->getBody(), true);

            $resultResponse = $result['sent'] ?? 1;
            if (empty($resultResponse) || $resultResponse == '0' || $resultResponse == false) {
                log_message('error', WhatsappChannel::class . ' : ' . json_encode($result));
            }

            return json_decode($response->getBody(), true);
        } catch (GuzzleException $e) {
            log_message('error', WhatsappChannel::class . ' : http request error - ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Get whatsapp client object.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Resolve key value of recipient.
     *
     * @param $receiver
     * @param $notification
     * @return array|string
     */
    protected function resolveKeyValue($receiver, $notification = null)
    {
        $sendTo = '';

        if (method_exists($receiver, $method = 'keyNotification')) {
            $sendTo = $receiver->keyNotification('whatsapp', $notification);
        } elseif (is_array($receiver) && key_exists('chat_id', $receiver)) {
            $sendTo = $receiver['chat_id'];
        } elseif (is_array($receiver) && key_exists('chatId', $receiver)) {
            $sendTo = $receiver['chatId'];
        } else if (is_string($receiver)) {
            $sendTo = $receiver;
        }

        return $sendTo;
    }

    /**
     * Detect chat id from string (support for ID country number).
     *
     * @param $value
     * @return string|string[]
     */
    public static function detectChatId($value)
    {
        $chatId = str_replace([' ', '+'], '', $value);
        if (strpos($chatId, '-') !== false) {
            if (!(strpos($chatId, '@g.us') !== false)) {
                $chatId .= '@g.us';
            }
        } else if (!(strpos($chatId, '@c.us') !== false)) {
            $chatId = preg_replace('/^08/', '628', $chatId);
            $chatId .= '@c.us';
        }

        return $chatId;
    }
}