<?php
namespace App\Models;

use CodeIgniter\Model;

class JwtRefreshTokenModel extends Model
{
    protected $table = 'jwt_refresh_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'refresh_token', 'expires_at', 'created_at', 'revoked'
    ];
    protected $useTimestamps = false;
}
