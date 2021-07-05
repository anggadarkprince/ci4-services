<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Whatsapp extends BaseConfig
{
    public $apiUrl = '';

    public $apiToken = '';

    public $secure = false;

    public $sandboxNumber = null;
}