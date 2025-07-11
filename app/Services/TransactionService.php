<?php

namespace App\Services;

use App\Models\TransaksiWebModel;
use App\Models\UserModel;
use App\Models\TransaksiJasaModel;
use App\Models\LogHistoryTransaksiModel;
use Ramsey\Uuid\Uuid;

class TransactionService
{
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

    public function createTransactionLogic(object $data_lensa, array $data_jasa, object $user): array
    {
        $this->db->transStart();

        try {
            $kode_lensa = ['68539' => '0', '68540' => '1', '68541' => '2', '68542' => '3'];
            $max_id = $data_lensa->max_id ?? "0";

            if (!empty($data_lensa->r_lensa) || !empty($data_lensa->l_lensa)) {
                $validationError = $this->validateMaxId($data_lensa, $kode_lensa);
                if ($validationError) {
                    $this->db->transRollback();
                    return ['status' => 'error', 'message' => $validationError];
                }
            }

            $transactionId = Uuid::uuid4()->getHex()->toString();
            $transaksiData = $this->buildTransactionData($transactionId, $data_lensa, $user);

            $totalLensa = 0;
            if (($data_lensa->hanya_jasa ?? '0') == '0') {
                if (!empty($data_lensa->r_lensa)) $totalLensa++;
                if (!empty($data_lensa->l_lensa)) $totalLensa++;
                $logic_data = $this->calculateTransactionLogic($data_lensa);
                $transaksiData = array_merge($transaksiData, $logic_data);
            }

            if (!$this->transaksiWebModel->insert($transaksiData)) {
                $this->db->transRollback();
                return ['status' => 'error', 'message' => 'Gagal menyimpan transaksi utama.', 'errors' => $this->transaksiWebModel->errors()];
            }

            if (!empty($data_jasa)) {
                $jasa_result = $this->processJasa($transactionId, $data_jasa, $data_lensa->hanya_jasa, $totalLensa);
                if ($jasa_result['status'] === 'error') {
                    $this->db->transRollback();
                    return $jasa_result;
                }
            } elseif ($data_lensa->hanya_jasa == '1') {
                $this->db->transRollback();
                return ['status' => 'error', 'message' => 'Jika memilih "Hanya Jasa", data jasa tidak boleh kosong.'];
            }

            $this->logTransaction($transactionId, $user->id, $data_lensa->hanya_jasa, $totalLensa);

            $this->db->transCommit();
            
            $newTransaction = $this->transaksiWebModel->find($transactionId);
            return ['status' => 'success', 'data' => $newTransaction];

        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', '[ERROR] Create Transaction Service: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Terjadi kesalahan internal saat membuat transaksi.'];
        }
    }

    private function validateMaxId(object $data_lensa, array $kode_lensa): ?string
    {
        $max_id = $data_lensa->max_id ?? null;
        if ($max_id === null) return 'Max Id wajib diisi jika ada data lensa.';

        $lenses = ['R' => $data_lensa->r_lensa, 'L' => $data_lensa->l_lensa];
        foreach ($lenses as $prefix => $lensa_code) {
            if (empty($lensa_code)) continue;

            $is_special = array_key_exists($lensa_code, $kode_lensa);
            if ($is_special && strlen($max_id) > 3) {
                return "Error di Lensa {$prefix}: Max Id untuk lensa spesial tidak boleh lebih dari 3 digit.";
            }
            if (!$is_special && (strlen($max_id) < 4 || $max_id < 1001)) {
                return "Error di Lensa {$prefix}: Max Id untuk lensa non-spesial harus 4 digit dan dimulai dari 1001.";
            }
        }
        return null;
    }

    private function buildTransactionData(string $transactionId, object $data_lensa, object $user): array
    {
        return [
            "id" => $transactionId,
            "no_po" => $data_lensa->no_po,
            "customer_id" => $user->kode_customer,
            "nama_customer" => $data_lensa->nama_customer,
            "hanya_jasa" => $data_lensa->hanya_jasa,
            "spesial_instruksi" => $data_lensa->spesial_instruksi,
            "keterangan" => $data_lensa->keterangan,
            "tgl_id" => date('Y-m-d'),
            "pic_input" => $user->id
        ];
    }

    private function calculateTransactionLogic(object $data_lensa): array
    {
        $totalPdf = ($data_lensa->r_pdf ?? 0) + ($data_lensa->l_pdf ?? 0);
        $totalPdn = ($data_lensa->r_pdn ?? 0) + ($data_lensa->l_pdn ?? 0);
        $koridor = (!empty($data_lensa->seg_height) && $data_lensa->seg_height <= 18) ? "SHORT" : "LONG";
        $mbs = ($data_lensa->effectif_diameter > 0 && $data_lensa->lens_size > 0 && $data_lensa->bridge_size > 0 && $totalPdf > 0)
            ? round($data_lensa->effectif_diameter + $data_lensa->lens_size + $data_lensa->bridge_size + 2 - $totalPdf)
            : '0';

        $lensaData = [];
        if (!empty($data_lensa->r_lensa)) {
            $lensaData += ["r_lensa" => $data_lensa->r_lensa, "r_spheris" => $data_lensa->r_spheris, "r_cylinder" => $data_lensa->r_cylinder, "r_bcurve" => $data_lensa->r_bcurve, "r_axis" => $data_lensa->r_axis, "r_additional" => $data_lensa->r_additional, "r_pd_far" => $data_lensa->r_pd_far, "r_pd_near" => $data_lensa->r_pd_near, "r_prisma" => $data_lensa->r_prisma, "r_base" => $data_lensa->r_base, "r_prisma2" => $data_lensa->r_prisma2, "r_base2" => $data_lensa->r_base2, "r_qty" => "1", "r_base_curve" => $data_lensa->r_base_curve, "r_edge_thickness" => $data_lensa->r_edge_thickness, "r_center_thickness" => $data_lensa->r_center_thickness];
        }
        if (!empty($data_lensa->l_lensa)) {
            $lensaData += ["l_lensa" => $data_lensa->l_lensa, "l_spheris" => $data_lensa->l_spheris, "l_cylinder" => $data_lensa->l_cylinder, "l_bcurve" => $data_lensa->l_bcurve, "l_axis" => $data_lensa->l_axis, "l_additional" => $data_lensa->l_additional, "l_pd_far" => $data_lensa->l_pd_far, "l_pd_near" => $data_lensa->l_pd_near, "l_prisma" => $data_lensa->l_prisma, "l_base" => $data_lensa->l_base, "l_prisma2" => $data_lensa->l_prisma2, "l_base2" => $data_lensa->l_base2, "l_qty" => "1", "l_base_curve" => $data_lensa->l_base_curve, "l_edge_thickness" => $data_lensa->l_edge_thickness, "l_center_thickness" => $data_lensa->l_center_thickness];
        }

        $footData = ["jenis_lensa" => $data_lensa->jenis_lensa, "total_pdf" => $totalPdf, "total_pdn" => $totalPdn, "frame_status" => $data_lensa->frame_status, "jenis_frame" => $data_lensa->jenis_frame, "model" => $data_lensa->model, "note" => $data_lensa->note, "effectif_diameter" => $data_lensa->effectif_diameter, "lens_size" => $data_lensa->lens_size, "bridge_size" => $data_lensa->bridge_size, "seg_height" => $data_lensa->seg_height, "vertical" => $data_lensa->vertical, "wa" => $data_lensa->wa, "pt" => $data_lensa->pt, "bvd" => $data_lensa->bvd, "ffv" => $data_lensa->ffv, "v_code" => $data_lensa->v_code, "rd" => $data_lensa->rd, "pe" => $data_lensa->pe, "mbs" => $mbs, "koridor" => $koridor, "max_id" => $data_lensa->max_id, "finish_diameter" => "0", "accessories" => "0"];

        return array_merge($lensaData, $footData);
    }

    private function processJasa(string $transactionId, array $data_jasa, string $hanya_jasa, int $totalLensa): array
    {
        $trnJasaData = [];
        foreach ($data_jasa as $jasa) {
            $jasa = (object)$jasa;
            $qty_jasa = ($hanya_jasa == '1') ? ($jasa->jasa_qty ?? 0) : $totalLensa;

            if ($hanya_jasa == '1') {
                if (!isset($jasa->jasa_qty)) {
                    return ['status' => 'error', 'message' => "Parameter Qty Jasa untuk jasa ID {$jasa->jasa_id} tidak ada."];
                }
                if ($qty_jasa < 1 || $qty_jasa > 2) {
                    return ['status' => 'error', 'message' => "Qty untuk jasa ID {$jasa->jasa_id} harus antara 1 dan 2."];
                }
            }

            $db_jasa = \Config\Database::connect('db_tol');
            $getJasaNama = $db_jasa->query('SELECT kode_jasa FROM db_tol.mst_jjasa WHERE kode_jasa=? AND aktif=1', [$jasa->jasa_id])->getRow();
            if (!$getJasaNama) {
                return ['status' => 'error', 'message' => "Kode jasa {$jasa->jasa_id} tidak ditemukan atau tidak aktif."];
            }

            $trnJasaData[] = ['id' => $transactionId, 'jasa_id' => $jasa->jasa_id, 'qty' => $qty_jasa];
        }

        if (!empty($trnJasaData) && !$this->transaksiJasaModel->insertBatch($trnJasaData)) {
            return ['status' => 'error', 'message' => 'Gagal menyimpan detail jasa.', 'errors' => $this->transaksiJasaModel->errors()];
        }

        return ['status' => 'success'];
    }

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

    public function getTransaction($id)
    {
        return $this->transaksiWebModel->find($id);
    }
}