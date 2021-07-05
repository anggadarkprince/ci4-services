<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Services;

class Customer extends BaseController
{
	public function index()
	{
        $loggedUser = Services::auth()->user();

        dd($loggedUser);
	}

    public function logout()
    {
        Services::auth()->logout();

        return redirect()->to('/login/customer');
    }
}
