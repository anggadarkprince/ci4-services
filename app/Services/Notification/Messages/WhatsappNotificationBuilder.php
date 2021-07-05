<?php

namespace App\Services\Notification\Messages;

use InvalidArgumentException;

class WhatsappNotificationBuilder
{
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    private static $instance;

    protected $chat = [];

    private function __construct()
    {
        // create object via newChat()
    }

    /**
     * Build new whatsapp config message.
     *
     * @return WhatsappNotificationBuilder
     */
    public static function newChat()
    {
        if (is_null(self::$instance)) {
            self::$instance = new WhatsappNotificationBuilder();
        }
        self::$instance->reset();

        return self::$instance;
    }

    /**
     * Reset build whatsapp message.
     *
     * @return $this
     */
    public function reset()
    {
        $this->chat = [];

        return $this;
    }

    /**
     * Set phone email.
     *
     * @param $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->chat['payload']['phone'] = $phone;

        return $this;
    }

    /**
     * Set chat id.
     *
     * @param $chatId
     * @return $this
     */
    public function setChatId($chatId)
    {
        $this->chat['payload']['phone'] = $chatId;

        return $this;
    }

    /**
     * Set body.
     *
     * @param $message
     * @return $this
     */
    public function setBody($message)
    {
        $this->chat['payload']['body'] = $message;

        return $this;
    }

    /**
     * Set file name.
     *
     * @param $fileName
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->chat['payload']['filename'] = $fileName;

        return $this;
    }

    /**
     * Set caption.
     *
     * @param $caption
     * @return $this
     */
    public function setCaption($caption)
    {
        $this->chat['payload']['caption'] = $caption;

        return $this;
    }

    /**
     * Build and return config send message.
     *
     * @return array
     */
    public function buildForSendMessage()
    {
        $this->chat['url'] = 'sendMessage';
        $this->chat['method'] = self::METHOD_POST;

        if (!isset($this->chat['payload']['body'])) {
            throw new InvalidArgumentException("Body is required, try to call setBody()");
        }

        return $this->chat;
    }

    /**
     * Build and return config send file.
     *
     * @return array
     */
    public function buildForSendFile()
    {
        $this->chat['url'] = 'sendFile';
        $this->chat['method'] = self::METHOD_POST;

        if (!isset($this->chat['payload']['body'])) {
            throw new InvalidArgumentException("Body is required, try to call setBody()");
        }

        if (!isset($this->chat['payload']['filename'])) {
            throw new InvalidArgumentException("File name is required, try to call setFileName()");
        }

        return $this->chat;
    }

}