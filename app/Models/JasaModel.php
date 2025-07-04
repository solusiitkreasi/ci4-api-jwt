<?php
namespace App\Models;

use CodeIgniter\Model;

class JasaModel extends Model
{
    protected $table = 'mst_jjasa';
    protected $primaryKey = 'kode_jasa';
    protected $returnType = 'array';
    protected $allowedFields = ['kode_jasa', 'nama_jasa'];
    protected $DBGroup = 'db_tol';
}
