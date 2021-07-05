<?php

namespace App\Services\Notification\Channels;

use App\Services\Notification\Notification;
use App\Services\Notification\NotificationChannel;
use Config\Email;
use Config\Services;

class MailChannel implements NotificationChannel
{
    protected $fromName;

    protected $fromEmail;

    /**
     * MailChannel constructor.
     *
     * @param Email $config
     */
    public function __construct(Email $config)
    {
        $config = $config ?? new Email();

        $this->fromName = $config->fromName;
        $this->fromEmail = $config->fromEmail;
    }

    /**
     * Get instantiate of channel.
     *
     * @param $config
     * @return MailChannel
     */
    public static function factory($config = null)
    {
        return new MailChannel($config = $config ?? new Email());
    }

    /**
     * Send notification to main channel.
     *
     * @param $receiver
     * @param Notification $notification
     * @return bool
     */
    public function send($receiver, Notification $notification)
    {
        $message = $notification->toMail($receiver);

        // Email from native object
        if ($message instanceof \CodeIgniter\Email\Email) {
            return $this->sendFromMailer($receiver, $message);
        }

        // Manually send email by config array
        if (is_array($message)) {
            return $this->sendFromConfigArray($receiver, $message);
        }

        return false;
    }

    /**
     * Send email from native mailer library.
     *
     * @param $receiver
     * @param $message
     * @return mixed
     */
    protected function sendFromMailer($receiver, \CodeIgniter\Email\Email $message)
    {
        if (empty($message->fromName)) {
            $message->fromName = $this->fromName;
        }
        if (empty($message->fromEmail)) {
            $message->fromEmail = $this->fromEmail;
        }

        $sendTo = $this->resolveKeyValue($receiver);
        if (!empty($sendTo)) {
            $message->setTo($sendTo);
        }

        return $message->send();
    }

    /**
     * Send email by config array.
     *
     * @Example
     * $message = [
     *   to => address@mail.com,
     *   cc => [cc1@mail.com, cc2@mail.com],
     *   bcc => bcc1@mail.com,
     *   subject => Invoice is published,
     *   message => <p>Invoice #34234 waiting for payment</p>,
     *   attachment => [
     *      source => /upload/to/file.pdf,
     *      disposition => attachment
     *   ]
     * ]
     *
     * @param $receiver
     * @param $message
     * @return bool
     */
    protected function sendFromConfigArray($receiver, $message)
    {
        $fromMail = $message['from'] ?? $this->fromEmail;
        $fromName = $message['from_name'] ?? $this->fromName;

        $email = Services::email()
            ->setMailType($message['mail_type'] ?? 'html')
            ->setFrom($fromMail, $fromName);

        $sendTo = $this->resolveKeyValue($receiver);
        if (!empty($sendTo)) {
            $email->setTo($sendTo);
        }

        if (isset($message['cc'])) {
            $email->setCC($message['cc']);
        }

        if (isset($message['bcc'])) {
            $email->setBCC($message['bcc']);
        }

        $email->setSubject($message['subject'] ?? 'No Email Subject');
        $email->setMessage($message['message'] ?? '');

        if (isset($message['attachment']) && !key_exists(0, $message['attachment'])) {
            $message['attachment'] = [$message['attachment']];
        }

        foreach ($message['attachment'] ?? [] as $attachment) {
            if (isset($attachment['source'])) {
                $source = $attachment['source'];
                $disposition = $attachment['disposition'] ?? '';
                $fileName = $attachment['file_name'] ?? null;
                $mime = $attachment['mime'] ?? '';
                $email->attach($source, $disposition, $fileName, $mime);
            }
        }

        return $email->send();
    }

    /**
     * Resolve key value of recipient.
     *
     * @param $receiver
     * @return array|string
     */
    protected function resolveKeyValue($receiver)
    {
        $sendTo = '';

        if (method_exists($receiver, $method = 'keyNotification')) {
            $sendTo = $receiver->keyNotification('mail');
        } elseif (is_array($receiver) && is_numeric(array_keys($receiver)[0] ?? '')) {
            $sendTo = $receiver;
        } elseif (is_array($receiver) && key_exists('to', $receiver)) {
            $sendTo = $receiver['to'];
        } else if (is_string($receiver)) {
            $sendTo = $receiver;
        }

        return $sendTo;
    }
}