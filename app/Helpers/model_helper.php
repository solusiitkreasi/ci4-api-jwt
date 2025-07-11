<?php
if (!function_exists('get_model_frame_backend')) {
    /**
     * Ambil data model frame dari database (untuk web backend, fallback jika API gagal)
     * @return array
     */
    function get_model_frame_backend()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT * FROM db_tol.mst_model');
        $getModel = $sql->getResult();
        $dataWithBase64 = [];
        foreach ($getModel as $model) {
            if (!empty($model->gambar)) {
                $model->gambar_m = "data:image/png;base64,".base64_encode($model->gambar);
            } else {
                $model->gambar_m = null;
            }
            // Nama model: jika tidak ada, pakai 'Model_' + nomor
            if (empty($model->nama_model) && !empty($model->nomor)) {
                $model->nama_model = 'Model_' . $model->nomor;
            }
            unset($model->gambar);
            $dataWithBase64[] = $model;
        }
        return $dataWithBase64;
    }
}

if (!function_exists('get_lensa_master_backend')) {
    /**
     * Ambil data master lensa dari database (untuk web backend, fallback jika API gagal)
     * @return array
     */
    function get_lensa_master_backend()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT kode_lensa_5digit as kode_lensa, nama_lensa_5digit as nama_lensa FROM db_tol.mst_jlensa_5digit WHERE aktif=1');
        return $sql->getResult();
    }
}

if (!function_exists('get_jasa_master_backend')) {
    /**
     * Ambil data master jasa dari database (untuk web backend, fallback jika API gagal)
     * @return array
     */
    function get_jasa_master_backend()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query("SELECT kode_jasa, nama_jasa FROM db_tol.mst_jjasa WHERE aktif=1 AND kode_jasa IS NOT NULL AND kode_jasa != ''");
        return $sql->getResult();
    }
}


// Template: tambahkan fungsi master lain di sini jika dibutuhkan ke depan
// if (!function_exists('get_xxx_master_backend')) { ... }
