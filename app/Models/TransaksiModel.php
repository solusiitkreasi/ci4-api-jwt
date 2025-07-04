<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table            = 'transaction_pr_h_apis';
    protected $primaryKey       = 'id';
    // PENTING: Matikan auto-increment karena kita akan generate UUID sendiri
    protected $useAutoIncrement = false;
    // protected $useAutoIncrement = true;

    protected $returnType       = 'array';

    // Dengan mengatur ini ke false, semua field diizinkan.
    protected $protectFields    = false; 
    
    // protected $allowedFields    = [
        //     'id',
        //     'no_po',
        //     'customer_id',
        //     'nama_customer',
        //     'tgl_id',
        //     'hanya_jasa',
        //     'jenis_lensa',
        //     'r_lensa',
        //     'r_spheris',
        //     'r_cylinder',
        //     'r_bcurve',
        //     'r_axis',
        //     'r_additional',
        //     'r_pd_far',
        //     'r_pd_near',
        //     'r_prisma',
        //     'r_base',
        //     'r_prisma2',
        //     'r_base2',
        //     'r_qty',
        //     'r_base_curve',
        //     'r_edge_thickness',
        //     'r_center_thickness',
        //     'l_lensa',
        //     'l_spheris',
        //     'l_cylinder',
        //     'l_bcurve',
        //     'l_axis',
        //     'l_additional',
        //     'l_pd_far',
        //     'l_pd_near',
        //     'l_prisma',
        //     'l_base',
        //     'l_prisma2',
        //     'l_base2',
        //     'l_qty',
        //     'l_base_curve',
        //     'l_edge_thickness',
        //     'l_center_thickness',
        //     'total_pdf',
        //     'total_pdn',
        //     'frame_status',
        //     'jenis_frame',
        //     'model',
        //     'note',
        //     'effectif_diameter',
        //     'lens_size',
        //     'bridge_size',
        //     'seg_height',
        //     'vertical',
        //     'wa',
        //     'pt',
        //     'bvd',
        //     'ffv',
        //     'v_code',
        //     'rd',
        //     'pe',
        //     'mbs',
        //     'koridor',
        //     'max_id',
        //     'finish_diameter',
        //     'spesial_instruksi',
        //     'keterangan',
        //     'pic_input',
        //     'wkt_input' // transaction_date bisa diisi otomatis jika default CURRENT_TIMESTAMP di DB
    // ];

    // Dates
    protected $useTimestamps = true; // Ini akan mengelola updated_at
    protected $createdField  = 'wkt_input'; // Sesuaikan jika wkt_input adalah created_at
    protected $updated_at    = '';

    // Relasi jika diperlukan
    // public function user()
    // {
    //     return $this->belongsTo(UserModel::class, 'kode_customer');
    // }

    // public function jasa()
    // {
    //     return $this->hasMany(TransaksiJasaModel::class, 'id');
    // }


    // public function logs()
    // {
    //     return $this->hasMany(LogHistoryTransaksiModel::class, 'transaction_id');
    // }


}