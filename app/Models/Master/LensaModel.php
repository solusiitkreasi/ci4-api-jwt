<?php 

namespace App\Models\Master;

use CodeIgniter\Model;
use Config\Database; // Penting untuk mengakses konfigurasi database

class LensaModel extends Model
{
    protected $table      = 'mst_jlensa_5digit'; // Nama tabel mst_biodata di DB HRD_ALL
    protected $primaryKey = 'kode_lensa_5digit'; // Sesuaikan dengan primary key tabel mst_biodata di DB HRD_ALL
    protected $returnType = 'array';
    protected $allowedFields = ['kode_lensa_5digit', 'lensa_5digit_id', 'nama_lensa_5digit']; // Sesuaikan

    // Menggunakan koneksi 'db_tol'
    protected $DBGroup = 'db_tol';

    public function __construct()
    {
        parent::__construct();
        // Anda bisa menginstansiasi koneksi langsung di sini jika diperlukan,
        // tapi properti $db_tol sudah cukup untuk sebagian besar kasus.
    }


    // public function getLensa5digit($)
}