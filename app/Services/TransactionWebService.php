<?php

namespace App\Services;

use App\Models\TransaksiWebModel;
use App\Models\UserModel;
use App\Models\TransaksiJasaModel;
use App\Models\LogHistoryTransaksiModel;
use Ramsey\Uuid\Uuid;

class TransactionWebService
{
    // Inisialisasi model dan database
    protected $transaksiWebModel;
    protected $userModel;
    protected $transaksiJasaModel;
    protected $logHistoryModel;
    protected $db;

    public function __construct()
    {
        $this->transaksiWebModel = new TransaksiWebModel();
        $this->userModel = new UserModel();
        $this->transaksiJasaModel = new TransaksiJasaModel();
        $this->logHistoryModel = new LogHistoryTransaksiModel();
        $this->db = \Config\Database::connect();
        helper(['response', 'text']);
    }

    /**
     * Proses utama pembuatan transaksi
     * - Validasi max_id lensa
     * - Generate UUID transaksi
     * - Simpan data transaksi utama
     * - Proses dan simpan jasa
     * - Simpan log transaksi
     * - Commit/Rollback database
     */
    public function createTransactionLogic(object $data_lensa, array $data_jasa, object $user): array
    {
        // Mulai transaksi database
        $this->db->transStart();

        try {
            // Generate UUID transaksi (32 karakter tanpa hyphen)
            $transactionId = Uuid::uuid4()->getHex()->toString();
            // Siapkan data transaksi utama
            $transaksiData = $this->buildTransactionData($transactionId, $data_lensa, $user);

            // Hitung jumlah lensa yang dipilih
            $totalLensa = 0;
            if (($data_lensa->hanya_jasa ?? '0') == '0') {
                if (!empty($data_lensa->r_lensa)) $totalLensa++;
                if (!empty($data_lensa->l_lensa)) $totalLensa++;
                // Proses logic tambahan (total pdf, pdn, koridor, mbs, dll)
                $logic_data = $this->calculateTransactionLogic($data_lensa);
                //Merger All
                $transaksiData = array_merge($transaksiData, $logic_data);
            }

            $insert_pr_h = $this->transaksiWebModel->insert($transaksiData);

            // Simpan data transaksi utama ke database
            if (!$insert_pr_h) {
                $this->db->transRollback();
                return ['status' => 'error', 'message' => 'Gagal menyimpan transaksi utama.', 'errors' => $this->transaksiWebModel->errors()];
            }

            // Proses dan simpan jasa jika ada
            if (!empty($data_jasa)) {
                $jasa_result = $this->processJasa($transactionId, $data_jasa, $data_lensa->hanya_jasa, $totalLensa);
                
                if ($jasa_result['status'] === 'error') {
                    $this->db->transRollback();
                    return $jasa_result;
                }
            } elseif ($data_lensa->hanya_jasa == '1') {
                // Jika hanya jasa, data jasa wajib ada
                $this->db->transRollback();
                return ['status' => 'error', 'message' => 'Jika memilih "Hanya Jasa", data jasa tidak boleh kosong.'];
            }

            // Simpan log transaksi
            $this->logTransaction($transactionId, $user->id, $data_lensa->hanya_jasa, $totalLensa);

            // Commit transaksi database
            $this->db->transCommit();
            
            // Ambil data transaksi yang baru dibuat untuk response
            $newTransaction = $this->transaksiWebModel->find($transactionId);
            return ['status' => 'success', 'data' => $newTransaction];

        } catch (\Exception $e) {
            // Jika terjadi error, rollback transaksi dan log error
            $this->db->transRollback();
            log_message('error', '[ERROR] Create Transaction Service: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Terjadi kesalahan internal saat membuat transaksi.'  . $e->getMessage()];
        }
    }


    /**
     * Siapkan data transaksi utama untuk penyimpanan
     */
    private function buildTransactionData(string $transactionId, object $data_lensa, object $user): array
    {
        return [
            "id"            => $transactionId,
            "no_po"         => $data_lensa->no_po,
            "tgl_id"        => date('Y-m-d'),
            "tgl_selesai"   => date('Y-m-d', strtotime($data_lensa->tgl_selesai))??'',
            "nama_customer" => $data_lensa->nama_customer??'',
            "tgl_lahir"     => $data_lensa->tgl_lahir ?? '',
            "hanya_jasa"    => $data_lensa->hanya_jasa,
            "keterangan"    => $data_lensa->keterangan,
            "customer_id"   => $user->kode_customer,
            "pic_input"     => $user->id
        ];
    }

    /**
     * Hitung logic tambahan transaksi (total pdf, pdn, koridor, mbs, dll)
     * - Proses field lensa kanan/kiri
     * - Proses field foot transaksi
     */
    private function calculateTransactionLogic(object $data_lensa): array
    {
        $totalPdf   = ($data_lensa->r_pd_far ?? 0) + ($data_lensa->l_pd_far ?? 0);
        $totalPdn   = ($data_lensa->r_pd_near ?? 0) + ($data_lensa->l_pd_near ?? 0);
        $koridor    = (!empty($data_lensa->seg_height) && $data_lensa->seg_height >= 18) ? "LONG" : "SHORT";
        $mbs        = ($data_lensa->mbs_measurement) ? $data_lensa->mbs_measurement : '0';

        // Proses data lensa kanan & kiri
        $lensaData = [];
        if (!empty($data_lensa->r_lensa)) {
            // Data lensa kanan
            $lensaData += [ "r_lensa" => $data_lensa->r_lensa, 
                            "r_nama_lensa" => $data_lensa->r_nama_lensa, 
                            "r_spheris" => $data_lensa->r_spheris, 
                            "r_cylinder" => $data_lensa->r_cylinder,
                            "r_axis" => $data_lensa->r_axis, 
                            "r_additional" => $data_lensa->r_add, 
                            "r_prisma" => $data_lensa->r_prisma, 
                            "r_base" => $data_lensa->r_base,  
                            "r_prisma2" => $data_lensa->r_prisma2, 
                            "r_base2" => $data_lensa->r_base2, 
                            "r_base_curve" => $data_lensa->r_base_curve, 
                            "r_pd_far" => $data_lensa->r_pd_far, 
                            "r_pd_near" => $data_lensa->r_pd_near, 
                            "r_qty" => "1", 
                            "r_edge_thickness" => $data_lensa->r_et, 
                            "r_center_thickness" => $data_lensa->r_ct
                        ];
        }
        if (!empty($data_lensa->l_lensa)) {
            // Data lensa kiri
            $lensaData += [ "l_lensa" => $data_lensa->l_lensa, 
                            "l_nama_lensa" => $data_lensa->l_nama_lensa, 
                            "l_spheris" => $data_lensa->l_spheris, 
                            "l_cylinder" => $data_lensa->l_cylinder, 
                            "l_axis" => $data_lensa->l_axis, 
                            "l_additional" => $data_lensa->l_add, 
                            "l_prisma" => $data_lensa->l_prisma, 
                            "l_base" => $data_lensa->l_base,  
                            "l_prisma2" => $data_lensa->l_prisma2, 
                            "l_base2" => $data_lensa->l_base2, 
                            "l_base_curve" => $data_lensa->l_base_curve, 
                            "l_pd_far" => $data_lensa->l_pd_far, 
                            "l_pd_near" => $data_lensa->l_pd_near, 
                            "l_qty" => "1", 
                            "l_edge_thickness" => $data_lensa->l_et, 
                            "l_center_thickness" => $data_lensa->l_ct
                        ];
        }
        
        // Data foot transaksi
        $footData = [   "spesial_instruksi" => $data_lensa->spesial_instruksi ?? '',
                        "jenis_lensa"       => $data_lensa->jenis_lensa ?? '', 
                        "total_pdf"         => $totalPdf, 
                        "total_pdn"         => $totalPdn, 
                        "jenis_frame"       => $data_lensa->jenis_frame??'',
                        "frame_status"      => $data_lensa->status_frame??'', 
                        "model"             => $data_lensa->model??'', 
                        "note"              => $data_lensa->kondisi ?? '',
                        "wa"                => $data_lensa->wa ?? '5', 
                        "pt"                => $data_lensa->pt ?? '9', 
                        "bvd"               => $data_lensa->bvd ?? '12', 
                        "ffv"               => $data_lensa->ffv ?? '0', 
                        "v_code"            => $data_lensa->v_code ?? '0', 
                        "rd"                => $data_lensa->rd ?? '0', 
                        "pe"                => $data_lensa->pe ?? '',
                        "max_id"            => $data_lensa->mid ?? '0',
                        "vertical"          => $data_lensa->b_measurement ?? '0',
                        "effectif_diameter" => $data_lensa->ed_measurement ?? '0',
                        "lens_size"         => $data_lensa->a_measurement ?? '0',
                        "bridge_size"       => $data_lensa->dbl_measurement ?? '0',
                        "seg_height"        => $data_lensa->sh_py_measurement ?? '0',
                        "koridor"           => $koridor,
                        "mbs"               => $mbs, 
                        "finish_diameter"   => "0", 
                        "accessories"       => "0"
        ];

        $data_merger = array_merge($lensaData, $footData);

        return $data_merger;
    }

    /**
     * Proses dan simpan jasa ke database
     * - Validasi qty jasa sesuai aturan
     * - Cek kode jasa di master
     * - Simpan batch jasa
     */
    private function processJasa(string $transactionId, array $data_jasa, string $hanya_jasa, int $totalLensa): array
    {
        $trnJasaData = [];
        foreach ($data_jasa as $jasa) {
            if(isset($jasa->jasa_id)){
                $jasa = (object)$jasa;
                // Tentukan qty jasa sesuai kondisi
                $qty_jasa = ($hanya_jasa == '1') ? ($jasa->jasa_qty ?? 0) : $totalLensa;

                // Validasi qty jasa jika hanya jasa
                if ($hanya_jasa == '1') {
                    if (!isset($jasa->jasa_qty)) {
                        return ['status' => 'error', 'message' => "Parameter Qty Jasa untuk jasa ID {$jasa->jasa_id} tidak ada."];
                    }
                    if ($qty_jasa < 1 || $qty_jasa > 2) {
                        return ['status' => 'error', 'message' => "Qty untuk jasa ID {$jasa->jasa_id} harus antara 1 dan 2."];
                    }
                }

                // Cek kode jasa di master
                $db_jasa = \Config\Database::connect('db_tol');
                $getJasaNama = $db_jasa->query('SELECT kode_jasa FROM db_tol.mst_jjasa WHERE kode_jasa=? AND aktif=1', [$jasa->jasa_id])->getRow();
                if (!$getJasaNama) {
                    return ['status' => 'error', 'message' => "Kode jasa {$jasa->jasa_id} tidak ditemukan atau tidak aktif."];
                }

                // Siapkan data jasa untuk batch insert
                $trnJasaData[] = ['id' => $transactionId, 'jasa_id' => $jasa->jasa_id, 'qty' => $qty_jasa];
        
            }
        }

        // Simpan batch jasa ke database
        if (!empty($trnJasaData) && !$this->transaksiJasaModel->insertBatch($trnJasaData)) {
            return ['status' => 'error', 'message' => 'Gagal menyimpan detail jasa.', 'errors' => $this->transaksiJasaModel->errors()];
        }

        return ['status' => 'success'];
    }

    /**
     * Simpan log transaksi ke tabel log_history_transaksi
     */
    private function logTransaction(string $transactionId, int $userId, string $hanya_jasa, int $totalLensa)
    {
        $logJasa = ($hanya_jasa == '1') ? '(Hanya Jasa)' : '';
        $logData = [
            'id' => Uuid::uuid4()->getHex()->toString(),
            'transaction_id' => $transactionId,
            'user_id' => $userId,
            'action' => 'Transaction Created ' . $logJasa,
            'details' => json_encode(['items_count' => $totalLensa])
        ];
        $this->logHistoryModel->insert($logData);
    }

    /**
     * Ambil data transaksi berdasarkan ID
     */
    public function getTransaction($id)
    {
        return $this->transaksiWebModel->find($id);
    }
}