<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Services;

class Upload extends BaseController
{
	public function index()
	{
        echo view('upload/create');
	}

	public function save()
    {
        $file = $this->request->getFile('attachment');

        $storage = Services::storage();

        $result = $storage->store($file, 'attachments/' . date('Y/m'));

        echo '<pre>';
        print_r($result);
        echo '</pre>';
    }
}
