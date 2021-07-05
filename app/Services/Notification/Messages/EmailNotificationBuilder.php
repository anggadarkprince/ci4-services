<?php

namespace App\Services\Notification\Messages;

use InvalidArgumentException;

class EmailNotificationBuilder
{
    private static $instance;

    protected $mail = [];

    private function __construct()
    {
        // create object via newMail()
    }

    /**
     * Build new email config message.
     *
     * @return EmailNotificationBuilder
     */
    public static function newMail()
    {
        if (is_null(self::$instance)) {
            self::$instance = new EmailNotificationBuilder();
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
        $this->mail = [];

        return $this;
    }

    /**
     * Set email receiver.
     *
     * @param $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->mail['to'] = $to;

        return $this;
    }

    /**
     * Set email subject.
     *
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->mail['subject'] = $subject;

        return $this;
    }

    /**
     * Set email plain message.
     *
     * @param $message
     * @param null $data
     * @return $this
     */
    public function setMessage($message, $data = null)
    {
        if (!empty($data)) {
            return $this->setBody($message, $data);
        }

        $this->mail['message'] = $message;

        return $this;
    }

    /**
     * Set body my template of mail and data.
     *
     * @param $template
     * @param $data
     * @return $this
     */
    public function setBody($template, $data)
    {
        $this->mail['message'] = view($template, $data);

        return $this;
    }

    /**
     * Set cc receiver.
     *
     * @param $cc
     * @return $this
     */
    public function setCC($cc)
    {
        $this->mail['cc'] = $cc;

        return $this;
    }

    /**
     * Set bbc receiver.
     *
     * @param $bcc
     * @return $this
     */
    public function setBCC($bcc)
    {
        $this->mail['bcc'] = $bcc;

        return $this;
    }

    /**
     * Reset attachment data.
     *
     * @return $this
     */
    public function clearAttachment()
    {
        $this->mail['attachments'] = [];

        return $this;
    }

    /**
     * Add attachment data.
     *
     * @param $source
     * @param string $disposition
     * @param null $newName
     * @param string $mime
     * @return $this
     */
    public function addAttachment($source, $disposition = '', $newName = null, $mime = '')
    {
        $this->mail['attachments'][] = [
            'source' => $source,
            'disposition' => $disposition,
            'new_name' => $newName,
            'mime' => $mime,
        ];

        return $this;
    }

    /**
     * Build and return mail config message.
     *
     * @return array
     */
    public function build()
    {
        if (!isset($this->mail['message'])) {
            throw new InvalidArgumentException("Message is required, try to call setBody() or setMessage()");
        }

        return $this->mail;
    }

}