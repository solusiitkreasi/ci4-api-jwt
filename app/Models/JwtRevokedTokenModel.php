<?php
namespace App\Models;

use CodeIgniter\Model;

class JwtRevokedTokenModel extends Model
{
    protected $table = 'jwt_revoked_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'token', 'revoked_at'
    ];
    protected $useTimestamps = false;
}
