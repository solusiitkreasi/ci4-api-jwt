<?php
namespace App\Models;

use CodeIgniter\Model;

class PaymentGatewayModel extends Model
{
    protected $table = 'payment_gateways';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'provider', 'mode', 'api_key', 'api_url', 'client_key', 'is_active', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
