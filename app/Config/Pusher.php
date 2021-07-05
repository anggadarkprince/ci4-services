<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Pusher extends BaseConfig
{
    public $appId = '';

    public $appKey = '';

    public $appSecret = '';

    public $cluster = 'ap1';

    public $encrypted = false;
}