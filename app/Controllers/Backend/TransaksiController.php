<?php 

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TransaksiWebModel; // Gunakan model web yang baru
use App\Models\UserModel;
use App\Services\TransactionService;
use App\Services\PaymentService; // Tetap ada jika diperlukan nanti

class TransaksiController extends BaseController
{
    protected $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
    }

    public function index()
    {
        $transaksiModel = new TransaksiWebModel();
        $userModel = new UserModel();
        $customers = $userModel->select('kode_customer, name')
            ->where('kode_customer IS NOT NULL')
            ->where('kode_customer !=', '')
            ->groupBy('kode_customer')
            ->orderBy('kode_customer', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Manajemen Transaksi',
            'customers'   => $customers,
        ];

        return view('backend/pages/transaksi/list', $data);
    }

    public function datatables()
    {
        $request    = $this->request;
        $model      = new TransaksiWebModel();
        $userModel  = new UserModel();
        
        $columns = [
            null, 
            'no_po',
            'customer_name',
            'wkt_input',
            'is_proses_tol',
            'transaction_pr_h_apis.id',
        ];

        $draw           = (int) $request->getGet('draw');
        $start          = (int) $request->getGet('start');
        $length         = (int) $request->getGet('length');
        $search         = $request->getGet('search')['value'] ?? '';
        $orderColIdx    = (int) ($request->getGet('order')[0]['column'] ?? 3);
        $orderCol       = $columns[$orderColIdx] ?? 'wkt_input';
        $orderDir       = $request->getGet('order')[0]['dir'] ?? 'desc';

        $builder = $model->select('transaction_pr_h_apis.*, users.name as customer_name, users.kode_customer')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left');

        // Filtering logic here...

        $totalRecords = $builder->countAllResults(false);

        if ($search) {
            $builder->groupStart()
                ->like('no_po', $search)
                ->orLike('users.name', $search)
                ->orLike('transaction_pr_h_apis.is_proses_tol', $search)
                ->groupEnd();
        }

        $recordsFiltered = $builder->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        if($length !== -1){
            $builder->limit($length, $start);
        }
        $data = $builder->get()->getResultArray();

        $resultData = [];
        foreach($data as $row){
            $no = $start + 1;
            $status = ($row['is_proses_tol'] == 1)
                ? '<span class="badge bg-success">Sudah</span>'
                : '<span class="badge bg-info">Proses</span>';
            $resultData[] = [
                $no,
                $row['no_po'],
                $row['kode_customer'],
                $row['customer_name'],
                $row['wkt_input'],
                $status,
                '<a href="/backend/transaksi/detail/'.esc($row['id']).'" class="btn btn-sm btn-info">Detail</a>'
            ];
            $start++;
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $resultData
        ]);
    }

    public function detail($id)
    {
        $transaksiModel = new TransaksiWebModel();
        $transaksi = $transaksiModel->select('transaction_pr_h_apis.*, users.name as pic')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left')
            ->where('transaction_pr_h_apis.id', $id)
            ->first();

        // ... (logic to get jasa name) ...
        $transaksiJasaModel = new \App\Models\TransaksiJasaModel();
        // $jasaTransaksi = $transaksiJasaModel->where('id', $id)->findAll();

        $getjasa = $transaksiJasaModel->where('id', $id)->findAll();
        foreach ($getjasa as &$jasa) {
            $db = \Config\Database::connect('db_tol');
            $jasaInfo = $db->query('SELECT nama_jasa FROM db_tol.mst_jjasa WHERE kode_jasa=? AND aktif=1', [$jasa['jasa_id']])->getRow();
            $jasa['nama_jasa'] = $jasaInfo ? $jasaInfo->nama_jasa : 'Kode Jasa Not Found';
        }

        return view('backend/pages/transaksi/detail', [
            'transaksi' => $transaksi,
            'jasa' => $getjasa
        ]);
    }

    public function create()
    {
        if ($this->request->getMethod() === 'post') {
            $validation = \Config\Services::validation();
            $postData = $this->request->getPost();
            $data_lensa = (object) ($postData['data_lensa'] ?? []);

            // Aturan validasi dinamis berdasarkan input
            $rules = [
                'data_lensa.no_po' => 'required|string|min_length[3]',
                'data_lensa.nama_customer' => 'required|string|min_length[3]',
                'data_lensa.hanya_jasa' => 'required|in_list[0,1]',
            ];

            // Aturan validasi untuk Lensa, hanya jika bukan "Hanya Jasa"
            if (($data_lensa->hanya_jasa ?? '0') == '0') {
                $rules['data_lensa.r_lensa'] = 'permit_empty|string'; // Contoh, bisa dibuat lebih spesifik
                $rules['data_lensa.l_lensa'] = 'permit_empty|string';
                // Tambahkan aturan lain untuk spheris, cylinder, dll. jika diperlukan
            }

            // Aturan validasi untuk Jasa
            if (!empty($postData['data_jasa'])) {
                foreach ($postData['data_jasa'] as $key => $jasa) {
                    $rules["data_jasa.{$key}.jasa_id"] = 'required|string';
                    $rules["data_jasa.{$key}.jasa_qty"] = 'required|integer|greater_than[0]';
                }
            }

            if (!$this->validate($rules)) {
                return $this->showError('Validasi gagal, periksa kembali input Anda.', $this->validator->getErrors());
            }

            $data_jasa  = $postData['data_jasa'] ?? [];
            
            $userModel = new UserModel();
            $user = $userModel->find(session()->get('user_id'));

            if (!$user) {
                return $this->showError('Sesi tidak valid atau user tidak ditemukan.');
            }

            $result = $this->transactionService->createTransactionLogic($data_lensa, $data_jasa, (object)$user);

            if ($result['status'] === 'error') {
                return $this->showError($result['message']);
            }

            return view('backend/pages/transaksi/create_result', [
                'transaksi' => $result['data'],
                'payment' => null
            ]);
        }

        $userModel = new UserModel();
        $customers = $userModel->select('kode_customer, name')
            ->where('kode_customer IS NOT NULL')->where('kode_customer !=', '')
            ->groupBy('kode_customer')->orderBy('kode_customer', 'ASC')->findAll();
            
        return view('backend/pages/transaksi/create', ['customers' => $customers]);
    }

    private function showError(string $msg, ?array $errors = null)
    {
        $userModel = new UserModel();
        $customers = $userModel->select('kode_customer, name')
            ->where('kode_customer IS NOT NULL')->where('kode_customer !=', '')
            ->groupBy('kode_customer')->orderBy('kode_customer', 'ASC')->findAll();

        return view('backend/pages/transaksi/create', [
            'error' => $msg,
            'errors' => $errors, // Teruskan array errors ke view
            'customers' => $customers
        ]);
    }
}