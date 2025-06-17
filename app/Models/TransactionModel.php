<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';

    // PENTING: Matikan auto-increment karena kita akan generate UUID sendiri
    // protected $useAutoIncrement = false;
    protected $useAutoIncrement = true;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Pertimbangkan soft delete jika transaksi bisa 'dihapus' sementara
    protected $allowedFields    = [
        'user_id',
        'transaction_code',
        'total_amount',
        'status',
        'transaction_date' // transaction_date bisa diisi otomatis jika default CURRENT_TIMESTAMP di DB
    ];

    // Dates
    protected $useTimestamps = true; // Ini akan mengelola updated_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'transaction_date'; // Sesuaikan jika transaction_date adalah created_at
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // Relasi jika diperlukan
    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetailModel::class, 'transaction_id');
    }

    public function payment()
    {
        return $this->hasOne(PaymentModel::class, 'transaction_id');
    }

    public function logs()
    {
        return $this->hasMany(LogHistoryTransaksiModel::class, 'transaction_id');
    }
}