<?php


namespace App\Models;


use App\Entities\UserToken;
use CodeIgniter\Model;

class UserTokenModel extends Model
{
    protected $table                = 'user_tokens';
    protected $returnType    = UserToken::class;

}