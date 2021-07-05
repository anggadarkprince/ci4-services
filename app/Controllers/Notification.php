<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Notifications\InvoicePaid;
use App\Services\Notification\Channels\WhatsappChannel;
use Config\Services;
use stdClass;

class Notification extends BaseController
{
	public function index()
	{
        $invoice = new stdClass;
        $invoice->id = 132;
        $invoice->amount = 10000;
        $invoice->no_invoice = '232523';

	    //$notificationManager = new NotificationManager();
        //$driver = $notificationManager->resolveDriver('whatsapp');

        //$email = \Config\Services::email();
        //$email->setFrom('someone@example.com', "Admin");
        //$email->setTo('someone@example.com');
        //$email->setSubject('Email Test');
        //$email->setMessage('Testing the email class.');
        //$result = $email->send();

        // Via receiver object
        $user = (new UserModel())->find([1, 2]);
        //$user->notify(new InvoicePaid($invoice));

        // Manual send via notification manager directly
        //(new NotificationManager())->send('angga@mail.com', new InvoicePaid());

        // Send via channel directly
        //Services::notification()->send($user, new InvoicePaid($invoice));
        /*service('notification')
            ->via([
                \App\Config\Notification::MAIL_CHANNEL,
                \App\Config\Notification::WHATSAPP_CHANNEL
            ])
            ->send($user, new InvoicePaid($invoice)); */

        // pusher
        Services::notification()->send($user, new InvoicePaid($invoice), ['pusher', 'database']);

        Services::notification()->read(1);
        Services::notification()->readAll($user);

        WhatsappChannel::factory()->send('065232343', new InvoicePaid($invoice));
	}
}
