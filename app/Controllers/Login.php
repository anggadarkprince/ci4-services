<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Services;

class Login extends BaseController
{
	public function index()
	{
		echo view('auth/login');
	}

	public function customer()
	{
		echo view('auth/login-customer');
	}

	public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        $usernameField = 'username';
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        if ($isEmail) {
            $usernameField = 'email';
        }

        $credentials = [
            $usernameField => $username,
            'password' => $password,
        ];
        if (Services::auth()->login($credentials, $remember)) {
            return redirect()->to('/');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('status', 'danger')
                ->with('message', 'Login failed, check username or password');
        }
    }

	public function login_customer()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        $credentials = [
            'customer_key' => $username,
            'password' => $password,
        ];
        if (Services::auth()->group('customer')->login($credentials, $remember)) {
            return redirect()->to('/customer');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('status', 'danger')
                ->with('message', 'Login failed, check username or password');
        }
    }

    public function logout()
    {
        Services::auth()->logout();

        return redirect()->to('/login');
    }
}
