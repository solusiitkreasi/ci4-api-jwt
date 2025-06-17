<?php

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

use App\Models\TransaksiModel;
use App\Models\TransaksiJasaModel;

use App\Models\PaymentModel;
use App\Models\LogHistoryTransaksiModel;
use App\Models\ProductModel;
use CodeIgniter\API\ResponseTrait;
use Config\Services; // Untuk mengambil data user dari filter JWT

use Ramsey\Uuid\Uuid;

class ClientController extends BaseController
{
    use ResponseTrait;
    protected $transactionModel;
    protected $transactionDetailModel;

    protected $transaksiModel;
    protected $transaksiJasaModel;

    protected $paymentModel;
    protected $logHistoryModel;
    protected $productModel;
    protected $currentUser;

    public function __construct()
    {
        $this->transactionModel         = new TransactionModel();
        $this->transactionDetailModel   = new TransactionDetailModel();

        $this->transaksiModel           = new TransaksiModel();
        $this->transaksiJasaModel       = new TransaksiJasaModel();

        $this->paymentModel             = new PaymentModel();
        $this->logHistoryModel          = new LogHistoryTransaksiModel();
        $this->productModel             = new ProductModel();
        helper(['response', 'text']); // 'text' untuk random_string
        
        // Mengambil user yang sudah diautentikasi oleh JWTAuthFilter
        // Pastikan $request->user diset di filter atau Services::injectMock('user', $user);
        $this->currentUser = service('request')->user ?? Services::getSharedInstance('user');
    }

    public function profile()
    {
        // $this->currentUser sudah berisi data user yang login
        if (!$this->currentUser) {
            return api_error('User not authenticated.', 401); // Seharusnya sudah ditangani filter
        }
        
        return api_response($this->currentUser, 'Profile fetched successfully');
    }

    public function createTransaction()
    {
        $rules = [
            'items' => 'required|is_array',
            'items.*.product_id' => 'required|is_natural_no_zero|is_not_unique[products.id]',
            'items.*.quantity' => 'required|is_natural_no_zero',
            // Tambahkan validasi untuk payment jika ada
            'payment.method' => 'permit_empty|alpha_dash', // Contoh: 'bank_transfer', 'credit_card'
            'payment.details' => 'permit_empty|is_array'  // Data spesifik per metode bayar
        ];

        $messages = [
            'items.*.product_id' => [
                'is_not_unique' => 'One or more products are invalid or not found.'
            ]
        ];


        if (!$this->validate($rules, $messages)) {
            return api_error('Validation failed', $this->getResponsegetStatusCode(), $this->validator->getErrors());
        }

        $items = $this->request->getVar('items');
        $paymentData = $this->request->getVar('payment');
        $userId = $this->currentUser->id;

        $db = \Config\Database::connect();
        $db->transStart(); // Mulai transaksi database

        try {
            $totalAmount = 0;
            $transactionDetailsData = [];

            foreach ($items as $item) {
                $product = $this->productModel->find($item->product_id);
                if (!$product || $product['stock'] < $item->quantity) {
                    $db->transRollback();
                    return api_error("Product '{$product['name']}' is out of stock or insufficient.", 400);
                }

                $subtotal = $product['price'] * $item->quantity;
                $totalAmount += $subtotal;

                $transactionDetailsData[] = [
                    // 'transaction_id' akan diisi setelah transaksi utama dibuat
                    'product_id'            => $item->product_id,
                    'quantity'              => $item->quantity,
                    'price_per_unit'        => $product['price'], // Harga saat transaksi
                    'subtotal'              => $subtotal
                ];

                // Kurangi stok produk
                $this->productModel->update($item->product_id, ['stock' => $product['stock'] - $item->quantity]);
            }
            
            $transactionCode = 'INV/' . date('Ymd') . '/' . strtoupper(random_string('alnum', 6));

            // 1. Simpan ke tabel 'transactions'
            $transactionData = [
                'user_id' => $userId,
                'transaction_code' => $transactionCode,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Status awal
            ];
            $transactionId = $this->transactionModel->insert($transactionData);

            if (!$transactionId) {
                $db->transRollback();
                return api_error('Failed to create transaction.', 500, $this->transactionModel->errors());
            }

            // 2. Simpan ke tabel 'transaction_details'
            foreach ($transactionDetailsData as &$detail) {
                $detail['transaction_id'] = $transactionId;
            }
            unset($detail); // Hapus referensi
            if (!$this->transactionDetailModel->insertBatch($transactionDetailsData)) {
                $db->transRollback();
                return api_error('Failed to save transaction details.', 500, $this->transactionDetailModel->errors());
            }

            // 3. Simpan ke tabel 'payments' (jika ada)
            if (!empty($paymentData) && !empty($paymentData->method)) {
                $paymentInput = [
                    'transaction_id'    => $transactionId,
                    'payment_method'    => $paymentData->method,
                    'amount_paid'       => $totalAmount, // Asumsi bayar lunas, bisa disesuaikan
                    'payment_status'    => 'pending', // Atau 'success' jika langsung
                    'paid_at'           => date('Y-m-d h:i:s'),
                    'payment_gateway_response' => '-'
                    // 'payment_proof_url' => $paymentData['proof_url'] ?? null,
                    // 'external_payment_id' => $paymentData['external_id'] ?? null,
                ];
                $paymentSave = $this->paymentModel->insert($paymentInput);

                if (!$paymentSave) {
                    $db->transRollback();
                    return api_error('Failed to record payment.', 500, $this->paymentModel->errors());
                }
            }

            // 4. Simpan ke tabel 'log_history_transaksi'
            $logData = [
                'transaction_id'    => $transactionId,
                'user_id'           => $userId,
                'action'            => 'Transaction Created',
                'details'           => json_encode(['items_count' => count($items), 'total' => $totalAmount])
            ];
            $this->logHistoryModel->insert($logData);

            $db->transCommit(); // Selesaikan transaksi

            // Ambil data transaksi yang baru dibuat untuk response
            $newTransaction = $this->transactionModel
                ->select('transactions.*, users.name as user_name, users.email as user_email')
                ->join('users', 'users.id = transactions.user_id')
                ->find($transactionId);
            
            // Ambil detailnya juga
            $newTransaction['details'] = $this->transactionDetailModel
                ->select('transaction_details.*, products.name as product_name')
                ->join('products', 'products.id = transaction_details.product_id')
                ->where('transaction_id', $transactionId)->findAll();

            return api_response($newTransaction, 'Transaction created successfully', 201);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[ERROR] Create Transaction: ' . $e->getMessage());
            return api_error('An error occurred during transaction: ' . $e->getMessage(), 500);
        }
    }

    public function listTransactions()
    {
        $userId = $this->currentUser->id;
        $transactions = $this->transactionModel
            ->where('user_id', $userId)
            ->orderBy('transaction_date', 'DESC')
            ->findAll();
        return api_response($transactions, 'User transactions fetched successfully');
    }

    public function getTransactionDetail($transactionCode)
    {
        $userId = $this->currentUser->id;
        $transaction = $this->transactionModel
            ->select('transactions.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = transactions.user_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.transaction_code', $transactionCode)
            ->first();

        if (!$transaction) {
            return api_error('Transaction not found or access denied.', 404);
        }

        $transaction['details'] = $this->transactionDetailModel
            ->select('transaction_details.*, products.name as product_name')
            ->join('products', 'products.id = transaction_details.product_id')
            ->where('transaction_id', $transaction['id'])->findAll();
        
        // Jika ada payment
        $transaction['payment'] = $this->paymentModel->where('transaction_id', $transaction['id'])->first();
        
        // Jika ada log
        $transaction['logs'] = $this->logHistoryModel->where('transaction_id', $transaction['id'])->orderBy('created_at', 'ASC')->findAll();

        return api_response($transaction, 'Transaction detail fetched successfully');
    }



    
    #--- Transaction OR Independent
    public function listTrn()
    { }
    

    public function getTrnDetail($transactionCode)
    { }


    public function createTrn()
    {

        $rules = [
            'data_lensa'        => 'permit_empty',
            // 'data_lensa.hanya_jasa'  => 'is_natural_no_zero',
            'data_lensa.kode_lensa' => 'required',
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
            $totalLensa = 0;
            if($data_lensa->r_lensa){
                $totalLensa +=1; 
            }
            if($data_lensa->l_lensa){
                $totalLensa +=1;
            }

            #total pdf
            $totalPdf = 0;
            if($data_lensa->r_pdf){
                $totalPdf +=$data_lensa->r_pdf; 
            }
            if($data_lensa->l_pdf){
                $totalPdf +=$data_lensa->l_pdf;
            }

            # get nilai koridor
            $koridor = "";
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
                "nama_customer"     => $user->nama_customer,
                "hanya_jasa"        => $data_lensa->hanya_jasa,
                "jenis_lensa"       => $data_lensa->jenis_lensa, // Dari data group lensa
            ];

            // Lensa Kanan Cek
            if($data_lensa->r_lensa)
            {
                $lensaR = [

                    "r_lensa"           => $data_lensa->r_lensa, // Lensa Kanan
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
                    "l_lensa"           => $data_lensa->l_lensa, // Lensa Kanan
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

            $trnAkhir = [
                "total_pdf"         => $totalPdf,
                "total_pdn"         => 10,
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
                "spesial_instruksi" => $data_lensa->spesial_instruksi,
                "keterangan"        => $data_lensa->keterangan,
                "tgl_id"            => date('Y-m-d'),
                "pic_input"         => $user->id
            ];

            // Merger Data Array
            $transaksiData = array_merge($trnAwal, $lensaR, $lensaL, $trnAkhir);

            // Insert Data
            $transaksiId = $this->transaksiModel->insert($transaksiData);

            // tesx($this->transaksiModel->errors());

            if (!$transaksiId) {
                $db->transRollback();
                return api_error('Failed to create transaksi.', 500, $this->transaksiModel->errors());
            }

            // 2. Simpan ke tabel 'jasa_data'
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


            // Simpan ke tabel 'log_history_transaksi'
            $logData = [
                'transaction_id'    => $transaksiId,
                'user_id'           => $user->id,
                'action'            => 'Transaction Created',
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