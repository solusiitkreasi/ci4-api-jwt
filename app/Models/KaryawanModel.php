<?php 

namespace App\Models;

use CodeIgniter\Model;
use Config\Database; // Penting untuk mengakses konfigurasi database

class KaryawanModel extends Model
{
    protected $table      = 'mst_biodata'; // Nama tabel mst_biodata di DB HRD_ALL
    protected $primaryKey = 'biodata_id'; // Sesuaikan dengan primary key tabel mst_biodata di DB HRD_ALL
    protected $returnType = 'array';
    protected $allowedFields = ['biodata_id', 'nip', 'nama_lengkap']; // Sesuaikan

    // Menggunakan koneksi 'dbhrdall'
    protected $DBGroup = 'db_hrd_all';

    public function __construct()
    {
        parent::__construct();
        // Anda bisa menginstansiasi koneksi langsung di sini jika diperlukan,
        // tapi properti $dbhrdall sudah cukup untuk sebagian besar kasus.
    }
}