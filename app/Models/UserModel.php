<?php

namespace App\Models;

use App\Entities\User;
use App\Services\Notification\ReceiveNotification;
use CodeIgniter\Model;

class UserModel extends Model
{

	protected $table                = 'users';
	protected $primaryKey           = 'id';
    //protected $returnType           = 'object';
    protected $returnType    = User::class;
}
