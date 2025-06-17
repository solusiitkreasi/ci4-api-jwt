<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $allowedFields    = [
        'transaction_id', 'payment_method', 'amount_paid', 'payment_status' , 'paid_at', 'create_at', 'updated_at'
    ];

    protected $useTimestamps = true; // Hanya created_at di skema, updated_at tidak
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // Kosongkan jika tidak ada updated_at

    protected $validationRules = [
        'transaction_id' => 'required|integer',
        'payment_method' => 'required|max_length[50]',
        'amount_paid'    => 'required|decimal',
        'payment_status' => 'required'
    ];

    public function transaction()
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }
}