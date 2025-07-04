<?php

namespace App\Models;

use CodeIgniter\Model;

class UserActivationModel extends Model
{
    protected $table      = 'user_activations';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'token', 'expires_at', 'created_at'];
    public $timestamps = false;
}