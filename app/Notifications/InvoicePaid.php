<?php

namespace App\Notifications;

use App\Services\Notification\Messages\DatabaseNotificationBuilder;
use App\Services\Notification\Messages\EmailNotificationBuilder;
use App\Services\Notification\Messages\PusherNotificationBuilder;
use App\Services\Notification\Messages\WhatsappNotificationBuilder;
use App\Services\Notification\Notification;
use Config\Services;

class InvoicePaid extends Notification
{
    private $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($receiver)
    {
        return ['whatsapp', 'pusher'];
    }

    public function toDatabase($receiver)
    {
        return DatabaseNotificationBuilder::newData()
            ->setRelatedId($this->invoice->id)
            ->setChannel("payment-notification.{$receiver->id}")
            ->setEvent('invoice-published')
            ->setData([
                'message' => 'Your invoice #44235 is waiting for payment',
                'url' => 'https://warehouse.app/invoice/44235',
                'time' => date('Y-m-d H:i:s'),
            ])
            ->build();

        return [
            'id_user' => $receiver->id,
            'id_related' => $this->invoice->id,
            'channel' => "payment-notification.{$receiver->id}",
            'event' => 'invoice-published',
            'data' => json_encode([
                'message' => 'Your invoice #44235 is waiting for payment',
                'url' => 'https://warehouse.app/invoice/44235',
                'time' => date('Y-m-d H:i:s'),
                'description' => 'New invoice data'
            ])
        ];
    }

    public function toPusher($receiver)
    {
        return PusherNotificationBuilder::newData()
            ->setChannel("payment-notification.{$receiver->id}")
            ->setEvent('invoice-published')
            ->setPayload([
                'message' => 'Your invoice #44235 is waiting for payment',
                'url' => 'https://warehouse.app/invoice/44235',
                'time' => date('Y-m-d H:i:s'),
            ])
            ->build();

        return [
            'channel' => "payment-notification.{$receiver->id}",
            'event' => 'invoice-published',
            'payload' => [
                'message' => 'Your invoice #44235 is waiting for payment',
                'url' => 'https://warehouse.app/invoice/44235',
                'time' => date('Y-m-d H:i:s'),
            ]
        ];
    }

    public function toWhatsapp($receiver)
    {
        $message = 'Your invoice #44235 is waiting for payment';

        // send file
        return WhatsappNotificationBuilder::newChat()
            ->setBody('https://transcon-indonesia.com/sso/assets/dist/img/no-avatar.png')
            ->setFileName("invoice.pdf")
            ->setCaption($message)
            ->buildForSendFile();

        // send message
        return WhatsappNotificationBuilder::newChat()
            ->setBody($message)
            ->buildForSendMessage();
    }

    public function toMail($receiver)
    {
        return EmailNotificationBuilder::newMail()
            ->setSubject('Hey ' . ($notifiable->name ?? '') . ' Invoice #44235 is published')
            //->setMessage("<h1>Hello</h1><p>Your invoice #44235 is waiting for payment</p>")
            ->setBody('emails/invoice', ['user' => $receiver, 'invoice' => $this->invoice])
            ->setCC('ari@mail.com')
            ->setBCC('wijaya@mail.com')
            ->addAttachment(WRITEPATH . 'uploads/public/attachments/2021/06/1624947625_306e3835e48e9957fbda.pdf')
            ->build();

        return [
            'subject' => 'Hey ' . ($notifiable->name ?? '') . ' Invoice #44235 is published',
            'message' => "<h1>Hello</h1><p>Your invoice #44235 is waiting for payment</p>",
            'cc' => 'ari@mail.com',
            'bcc' => 'wijaya@mail.com',
            'attachment' => [
                'source' => WRITEPATH . 'uploads/public/attachments/2021/06/1624947625_306e3835e48e9957fbda.pdf'
            ]
        ];

        return Services::email()
            ->setSubject('Hey ' . $notifiable->name . ' Invoice #44235 is published')
            ->setMessage("<h1>Hello</h1><p>Your invoice #44235 is waiting for payment</p>");
    }

}