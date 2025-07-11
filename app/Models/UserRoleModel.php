<?php
namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $primaryKey = ['user_id', 'role_id'];
    protected $allowedFields = ['user_id', 'role_id'];
    public $timestamps = false;

    protected function setPrimaryKey($userId, $roleId)
    {
        $this->where('user_id', $userId)->where('role_id', $roleId);
    }
}
