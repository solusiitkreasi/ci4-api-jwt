<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiWebModel extends Model
{
    protected $table            = 'transaction_pr_h_apis';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';

    // Perlindungan Mass Assignment diaktifkan.
    // Hanya field yang secara eksplisit dilewatkan melalui service yang akan disimpan.
    protected $protectFields    = true;


    // Mendefinisikan semua kolom yang diizinkan untuk insert/update.
    protected $allowedFields    = [
        'id', 'no_po', 'customer_id', 'nama_customer', 'hanya_jasa', 'jenis_lensa', 'tgl_id',
        'r_lensa', 'r_nama_lensa', 'r_spheris', 'r_cylinder', 'r_bcurve', 'r_axis', 'r_additional',
        'r_pd_far', 'r_pd_near', 'r_prisma', 'r_base', 'r_prisma2', 'r_base2', 'r_base_curve',
        'r_edge_thickness', 'r_center_thickness', 'r_qty', 'l_lensa', 'l_nama_lensa', 'l_spheris',
        'l_cylinder', 'l_bcurve', 'l_axis', 'l_additional', 'l_pd_far', 'l_pd_near', 'l_prisma',
        'l_base', 'l_prisma2', 'l_base2', 'l_base_curve', 'l_edge_thickness', 'l_center_thickness',
        'l_qty', 'total_pdf', 'total_pdn', 'effectif_diameter', 'lens_size', 'bridge_size',
        'seg_height', 'mbs', 'vertical', 'accessories', 'spesial_instruksi', 'keterangan',
        'frame_status', 'note', 'model', 'jenis_frame', 'wa', 'pt', 'bvd', 'ffv', 'rd', 'max_id',
        'v_code', 'pe', 'koridor', 'finish_diameter', 'is_proses_tol', 'pic_input', 'wkt_input', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'wkt_input';
    protected $updatedField  = 'updated_at';
}