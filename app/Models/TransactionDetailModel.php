<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionDetailModel extends Model
{
    protected $table            = 'transaction_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'transaction_id',
        'product_id',
        'quantity',
        'price_per_unit',
        'subtotal'
    ];

    // Tidak ada timestamps di tabel ini sesuai skema awal, jika ada, tambahkan:
    // protected $useTimestamps = false;

    // Relasi jika diperlukan
    public function transaction()
    {
        return $this->belongsTo(TransactionModel::class, 'transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class, 'product_id');
    }
}