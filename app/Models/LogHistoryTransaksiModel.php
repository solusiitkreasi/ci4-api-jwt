<?php

namespace App\Models;

use CodeIgniter\Model;

class LogHistoryTransaksiModel extends Model
{
    protected $table            = 'transaction_history_log';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'id',
        'transaction_id',
        'user_id', // User yang melakukan aksi (bisa admin, client, atau sistem/null)
        'action',
        'details' // JSON atau text
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // Tidak ada updated_at di skema ini

    public function transaction()
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}