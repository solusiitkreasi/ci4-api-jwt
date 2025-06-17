<?php 

namespace App\Models;

use CodeIgniter\Model;

class ApiKeyModel extends Model
{
    protected $table = 'api_keys';
    protected $primaryKey = 'id';
    protected $allowedFields = ['client_name', 'key_value', 'permissions', 'status'];
    protected $useTimestamps = true;
}