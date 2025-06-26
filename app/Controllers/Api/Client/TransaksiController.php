<?php

namespace App\Controllers\Api\Client;

use App\Controllers\BaseController;
use App\Models\TransaksiModel;
use App\Models\TransaksiJasaModel;
use App\Models\LogHistoryTransaksiModel;
use CodeIgniter\API\ResponseTrait;
use Config\Services; // Untuk mengambil data user dari filter JWT
use Ramsey\Uuid\Uuid;

class TransaksiController extends BaseController
{
    use ResponseTrait;
    protected $transaksiModel;
    protected $transaksiJasaModel;
    protected $logHistoryModel;
    protected $currentUser;

    public function __construct()
    {

        $this->transaksiModel           = new TransaksiModel();
        $this->transaksiJasaModel       = new TransaksiJasaModel();
        $this->logHistoryModel          = new LogHistoryTransaksiModel();
        helper(['response', 'text']); // 'text' untuk random_string
        
        // Mengambil user yang sudah diautentikasi oleh JWTAuthFilter
        // Pastikan $request->user diset di filter atau Services::injectMock('user', $user);
        $this->currentUser = service('request')->user ?? Services::getSharedInstance('user');
    }

    #--- Transaction OR Independent
    public function listTrn()
    {
        // Pagination example
        $page       = $this->request->getGet('page') ?? 1;
        $perPage    = $this->request->getGet('perPage') ?? 10;
        
        // Filtering example (by category_id)
        $categoryId     = $this->request->getGet('category_id');
        $customer_id    = $this->currentUser->kode_customer;
        
        $transaksi   = $this->transaksiModel
                        ->select('id, no_po, customer_id, nama_customer, 
                                hanya_jasa, jenis_lensa, wkt_input')
                        ->where('customer_id', $customer_id);

        // $transaksi = $this->transaksiModel->findAll();

        // tesx($transaksi);

        // tesx($this->transaksiModel->getLastQuery() );
        
        $transaksi   = $this->transaksiModel->paginate($perPage, 'default', $page);
        
        $pager       = $this->transaksiModel->pager;

        $data = [
            'transaksi'          => $transaksi,
            'pagination'        => [
                'total'         => $pager->getTotal(),
                'perPage'       => $pager->getPerPage(),
                'currentPage'   => $pager->getCurrentPage(),
                'lastPage'      => $pager->getLastPage(),
            ]
        ];
        return api_response($data, 'Transaksi fetched successfully');
    }
    
    public function getTrnDetail($id = NULL)
    {

        // GET Data Transaksi
        $transaksi = $this->transaksiModel->where('id',$id)->findAll();

        if (!$transaksi) {
            return api_error('Detail Transaksi not found', 404);
        }

        $getjasa   = $this->transaksiJasaModel->where('id',$id)->findAll();

        foreach ($getjasa as &$jasa) {
            
            $jasa_id = $jasa['jasa_id'];
            
            $db     = \Config\Database::connect('db_tol');
            $sql    = $db->query('SELECT kode_jasa, nama_jasa FROM db_tol.mst_jjasa 
                                WHERE kode_jasa="'.$jasa_id.'" AND aktif=1');
            $getJasaNama = $sql->getRow();
            
            $jasa['nama_jasa'] = $getJasaNama->nama_jasa;
        }
        
        $data['transaksi']  = $transaksi;
        $data['jasa']       = $getjasa;

        return api_response($data, 'Detail Transaksi fetched successfully');
    }

    public function createTrn()
    {

        $rules = [
            'data_lensa'        => 'permit_empty',
            // 'data_lensa.hanya_jasa'  => 'is_natural_no_zero',
            // 'data_lensa.kode_lensa' => 'required',
            // 'items.*.quantity' => 'required|is_natural_no_zero',
            // Tambahkan validasi untuk payment jika ada
            // 'payment.method' => 'permit_empty|alpha_dash', // Contoh: 'bank_transfer', 'credit_card'
            // 'payment.details' => 'permit_empty|is_array'  // Data spesifik per metode bayar
            'data_jasa'     => 'is_array'
        ];

        $messages = [
            // 'items.*.product_id' => [
            //     'is_not_unique' => 'One or more products are invalid or not found.'
            // ]
        ];

        // if (!$this->validate($rules, $messages)) {
        //     return api_error('Validation failed', $this->getResponsegetStatusCode(), $this->validator->getErrors());
        // }


        $data_lensa = $this->request->getVar('data_lensa');
        $data_jasa  = $this->request->getVar('data_jasa');
        $user       = $this->currentUser;

        # MID cek pada field max_id
        # kode lensa ( 68539, 68540, 68541, 68542 ) , tidak boleh lebih dari  3 digit / value 999 
        # selain itu, harus di isi ribuan / 4 digit,  dimulai 1001 
        $kode_lensa = array('68539'=>'0', '68540'=>'1', '68541'=>'2', '68542'=>'3');

        $max_id = "0";
        if($data_lensa->r_lensa || $data_lensa->l_lensa)
        {
            
            if (array_key_exists($data_lensa->r_lensa,$kode_lensa)) {

                if(strlen($data_lensa->max_id) > '3')
                {
                   return api_error('Max Id (Lensa R), untuk kode lensa (68539, 68540, 68541, 68542) tidak boleh lebih dari 3 digit / value 999, selain itu harus di isi ribuan / 4 digit dimulai 1001', 404);     
                }else{
                    $max_id = $data_lensa->max_id;
                }

            } else {

                if(strlen($data_lensa->max_id) < '4')
                {
                   return api_error('Max Id (Lensa R), untuk kode lensa (68539, 68540, 68541, 68542) tidak boleh lebih dari 3 digit / value 999, selain itu harus di isi ribuan / 4 digit dimulai 1001', 404);     
                }else{

                    if($data_lensa->max_id >= '1001')
                    {
                        $max_id = $data_lensa->max_id;
                    }else{
                        return api_error('Max Id (Lensa R), untuk kode lensa (68539, 68540, 68541, 68542) tidak boleh lebih dari 3 digit / value 999, selain itu harus di isi ribuan / 4 digit dimulai 1001', 404); 
                    }

                }

                
            }

            if (array_key_exists($data_lensa->l_lensa,$kode_lensa)) {

                

                if(strlen($data_lensa->max_id) > '3')
                {
                   return api_error('Max Id (Lensa L), untuk kode lensa (68539, 68540, 68541, 68542) tidak boleh lebih dari 3 digit / value 999, selain itu harus di isi ribuan / 4 digit dimulai 1001', 404);     
                }else{
                    $max_id = $data_lensa->max_id;
                }

            } else {

                if(strlen($data_lensa->max_id) < '4')
                {
                   return api_error('Max Id (Lensa L), untuk kode lensa (68539, 68540, 68541, 68542) tidak boleh lebih dari 3 digit / value 999, selain itu harus di isi ribuan / 4 digit dimulai 1001', 404);     
                }else{

                    if($data_lensa->max_id >= '1001')
                    {
                        $max_id = $data_lensa->max_id;
                    }else{
                        return api_error('Max Id (Lensa L), untuk kode lensa (68539, 68540, 68541, 68542) tidak boleh lebih dari 3 digit / value 999, selain itu harus di isi ribuan / 4 digit dimulai 1001', 404); 
                    }

                }

            }

        }


        $db = \Config\Database::connect();
        $db->transStart(); // Mulai transaksi database

        try {
            
            

            // ✅ GENERATE UUID untuk ID Transaksi
            // SEBELUM: $transactionId = Uuid::uuid4()->toString();
            // Menghasilkan: "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" (36 karakter)

            // SESUDAH : (Menjadi 32 karakter tanpa hyphen):
            $transactionId      = Uuid::uuid4()->getHex()->toString();

            $transactionCode    = 'INV/' . date('Ymd') . '/' . strtoupper(random_string('alnum', 6));

            // 1. Simpan ke tabel 'transaction_pr_h_api'
            $trnAwal = [
                "id"                => $transactionId,
                "no_po"             => $data_lensa->no_po, // No PO di generate oleh Independent, Input Manual, Wajib Di isi
                "customer_id"       => $user->kode_customer, 
                "nama_customer"     => $data_lensa->nama_customer,
                "hanya_jasa"        => $data_lensa->hanya_jasa,
            ];

            $totalLensa = 0;
            $totalPdf   = 0;
            $totalPdn   = 0;
            $koridor    = "";
            // Cek If Hanya Jasa
            if($data_lensa->hanya_jasa == '0')
            {
                // Pengecekan Data

                    # Total Lensa
                    if($data_lensa->r_lensa){
                        $totalLensa +=1; 
                    }
                    if($data_lensa->l_lensa){
                        $totalLensa +=1;
                    }

                    #total pdf
                    if($data_lensa->r_pdf){
                        $totalPdf +=$data_lensa->r_pdf; 
                    }
                    if($data_lensa->l_pdf){
                        $totalPdf +=$data_lensa->l_pdf;
                    }

                    #total pdn
                    if($data_lensa->r_pdn){
                        $totalPdn +=$data_lensa->r_pdn; 
                    }
                    if($data_lensa->l_pdn){
                        $totalPdn +=$data_lensa->l_pdn;
                    }

                    # get nilai koridor
                    if ($data_lensa->seg_height)
                    {
                        if($data_lensa->seg_height <= "18")
                        {
                            $koridor = "SHORT";
                        }else{
                            $koridor = "LONG";
                        }
                    }

                    #get nilai mbs
                    if ($data_lensa->effectif_diameter > 0 & $data_lensa->lens_size > 0 & $data_lensa->bridge_size > 0  & $totalPdf > 0){
                        $mbs = ROUND($data_lensa->effectif_diameter+$data_lensa->lens_size+$data_lensa->bridge_size+2-$totalPdf);
                    } else {
                        $mbs = '0';
                    }

                // End Pengecekan Data

                // Lensa Kanan Cek
                if($data_lensa->r_lensa)
                {
                    $lensaR = [
                        "r_lensa"           => $data_lensa->r_lensa,
                        "r_spheris"         => $data_lensa->r_spheris,
                        "r_cylinder"        => $data_lensa->r_cylinder,
                        "r_bcurve"          => $data_lensa->r_bcurve,
                        "r_axis"            => $data_lensa->r_axis,
                        "r_additional"      => $data_lensa->r_additional,
                        "r_pd_far"          => $data_lensa->r_pd_far,
                        "r_pd_near"         => $data_lensa->r_pd_near,
                        "r_prisma"          => $data_lensa->r_prisma,
                        "r_base"            => $data_lensa->r_base,
                        "r_prisma2"         => $data_lensa->r_prisma2,
                        "r_base2"           => $data_lensa->r_base2,
                        "r_qty"             => "1",
                        "r_base_curve"      => $data_lensa->r_base_curve,
                        "r_edge_thickness"  => $data_lensa->r_edge_thickness,
                        "r_center_thickness"=> $data_lensa->r_center_thickness,
                    ];
                }else{
                    $lensaR = [];
                }

                // Lensa Kiri Cek
                if($data_lensa->l_lensa)
                {
                    $lensaL = [
                        "l_lensa"           => $data_lensa->l_lensa,
                        "l_spheris"         => $data_lensa->l_spheris,
                        "l_cylinder"        => $data_lensa->l_cylinder,
                        "l_bcurve"          => $data_lensa->l_bcurve,
                        "l_axis"            => $data_lensa->l_axis,
                        "l_additional"      => $data_lensa->l_additional,
                        "l_pd_far"          => $data_lensa->l_pd_far,
                        "l_pd_near"         => $data_lensa->l_pd_near,
                        "l_prisma"          => $data_lensa->l_prisma,
                        "l_base"            => $data_lensa->l_base,
                        "l_prisma2"         => $data_lensa->l_prisma2,
                        "l_base2"           => $data_lensa->l_base2,
                        "l_qty"             => "1",
                        "l_base_curve"      => $data_lensa->l_base_curve,
                        "l_edge_thickness"  => $data_lensa->l_edge_thickness,
                        "l_center_thickness"=> $data_lensa->l_center_thickness,
                    ];
                }else{
                    $lensaL = [];
                }

            
                $trnFoot = [
                    "jenis_lensa"       => $data_lensa->jenis_lensa, // Dari data group lensa

                    "total_pdf"         => $totalPdf,
                    "total_pdn"         => $totalPdn,
                    "frame_status"      => $data_lensa->frame_status,
                    "jenis_frame"       => $data_lensa->jenis_frame,
                    "model"             => $data_lensa->model,
                    "note"              => $data_lensa->note,
                    "effectif_diameter" => $data_lensa->effectif_diameter,
                    "lens_size"         => $data_lensa->lens_size,
                    "bridge_size"       => $data_lensa->bridge_size,
                    "seg_height"        => $data_lensa->seg_height,
                    "vertical"          => $data_lensa->vertical,
                    "wa"                => $data_lensa->wa,
                    "pt"                => $data_lensa->pt,
                    "bvd"               => $data_lensa->bvd,
                    "ffv"               => $data_lensa->ffv,
                    "v_code"            => $data_lensa->v_code,
                    "rd"                => $data_lensa->rd,
                    "pe"                => $data_lensa->pe,
                    "mbs"               => $mbs,
                    "koridor"           => $koridor,
                    "max_id"            => $max_id,
                    "finish_diameter"   => "0", // Null Belum tau, belum ada data
                    "accessories"       => "0", // Null
                ];
            }else{
                $lensaR = [];
                $lensaL = [];
                $trnFoot =[];
            }

            $trnAkhir = [
                "spesial_instruksi" => $data_lensa->spesial_instruksi,
                "keterangan"        => $data_lensa->keterangan,
                "tgl_id"            => date('Y-m-d'),
                "pic_input"         => $user->id
            ];

            // Merger Data Array
            $transaksiData = array_merge($trnAwal, $lensaR, $lensaL,$trnFoot, $trnAkhir);

            // Insert Data
            $transaksiId = $this->transaksiModel->insert($transaksiData);

            // Cek Insert False
            if (!$transaksiId) {
                $db->transRollback();
                return api_error('Failed to create transaksi.', 500, $this->transaksiModel->errors());
            }

            if($data_jasa){
                // Simpan ke tabel 'jasa_data'
                $trnJasaData = [];
                foreach ($data_jasa as $jasa) {
                    $trnJasaData[] = [
                        // 'id' akan diisi setelah transaksi utama dibuat
                        'id'                 => $transaksiId,
                        'jasa_id'            => $jasa->jasa_id,
                        'qty'                => $totalLensa,
                    ];
                }

                if (!$this->transaksiJasaModel->insertBatch($trnJasaData)) {
                    $db->transRollback();
                    return api_error('Failed to save jasa details.', 500, $this->transaksiJasaModel->errors());
                }
            }

            $logJasa ='';
            if(!$data_jasa){
                $logJasa = '(Tidak Ada Jasa)';
            }

            // Simpan ke tabel 'log_history_transaksi'
            $IdLog      = Uuid::uuid4()->getHex()->toString();
            $logData = [
                'id'                => $IdLog,
                'transaction_id'    => $transaksiId,
                'user_id'           => $user->id,
                'action'            => 'Transaction Created '.$logJasa,
                'details'           => json_encode(['items_count' => $totalLensa])
            ];
            $this->logHistoryModel->insert($logData);

            $db->transCommit(); // Selesaikan transaksi

            // Ambil data transaksi yang baru dibuat untuk response
            $newTransaction = $this->transaksiModel->find($transaksiId);

            return api_response($newTransaction, 'Transaction created successfully', 201);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[ERROR] Create Transaction: ' . $e->getMessage());
            return api_error('An error occurred during transaction: ' . $e->getMessage(), 500);
        }

    }

}