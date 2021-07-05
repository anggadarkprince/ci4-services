<?php

namespace App\Controllers;

use Config\Services;

class Home extends BaseController
{
	public function index()
	{
        $loggedUser = Services::auth()->user();
        //dd($loggedUser);
        $loggedCustomer = Services::auth()->group('customer')->user();
        $loggedUser2 = Services::auth()->user();
        dd(['web' => $loggedUser, 'customer' => $loggedCustomer, '2' => $loggedUser2]);

        return view('welcome_message');
	}
}
