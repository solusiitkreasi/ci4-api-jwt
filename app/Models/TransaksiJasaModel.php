<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiJasaModel extends Model
{
    protected $table            = 'transaction_pr_jasa_d_apis';
    // protected $primaryKey       = 'id';
    // protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    // protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'jasa_id',
        'id',
        'qty',
        'pic_input',
        'wkt_input'
        
    ];

     // Dates
    protected $useTimestamps = true; // Ini akan mengelola updated_at
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'wkt_input'; // Sesuaikan jika wkt_input adalah created_at
    protected $updated_at    = '';

    // Relasi jika diperlukan
    public function transaksi()
    {
        return $this->belongsTo(TransaksiModel::class, 'id');
    }

}