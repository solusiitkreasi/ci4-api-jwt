<?php
namespace App\Models;

use CodeIgniter\Model;

class AuditTrailModel extends Model
{
    protected $table = 'audit_trails';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'action', 'description', 'ip_address', 'user_agent', 'created_at'
    ];
    protected $useTimestamps = false;
}
