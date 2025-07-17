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
        // Ambil data group_customer untuk filter Group Store
        // Ambil data group store dari db_tol
        $db_tol  = \Config\Database::connect('db_tol');
        $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group IS NOT NULL AND kode_group != '' ORDER BY nama_group ASC")->getResult();
        $data = [
            'title' => 'Manajemen Transaksi',
            'group_customer' => $group_customer,
        ];

        return view('backend/pages/transaksi/list', $data);
    }

    public function datatables()
    {
        $request    = $this->request;
        $model      = new TransaksiWebModel();
        $userModel  = new UserModel();
        
        // Kolom harus sesuai urutan datatables (No, No PO, Kode Customer, Nama Klien, Tanggal, Status, Aksi)
        $columns = [
            null, // No (nomor urut, tidak digunakan untuk order)
            'no_po',
            'kode_customer', 
            'customer_name',
            'wkt_input', // kolom ke-4 (index 4) untuk sorting tanggal
            'is_proses_tol',
            'transaction_pr_h_apis.id',
        ];

        $draw           = (int) $request->getGet('draw');
        $start          = (int) $request->getGet('start');
        $length         = (int) $request->getGet('length');
        $search         = $request->getGet('search')['value'] ?? '';
        $orderColIdx    = (int) $request->getGet('order')[0]['column'] ?? 4; // Default ke kolom tanggal
        $orderCol       = $columns[$orderColIdx] ?? 'wkt_input';

        if ($orderCol === null) $orderCol = 'wkt_input';
        $orderDir = $request->getGet('order')[0]['dir'] ?? 'desc';
        // Paksa order default jika tidak ada order dari datatables
        if (!$request->getGet('order')) {
            $orderCol = 'wkt_input';
            $orderDir = 'desc';
        }

        $pic_input = session()->get('user_id');

        $builder = $model->select('transaction_pr_h_apis.*, users.name as customer_name, users.kode_customer')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left');

        // Filtering logic here...
        $this->applyRoleBasedFiltering($builder, $userModel, $pic_input);

        // Apply additional filters (customer, status, date range)
        $this->applyCustomFilters($builder, $request);

        // Total sebelum filter
        $totalRecords = $model->countAllResults(false);

        // Filter pencarian
        if ($search) {
            $builder->groupStart()
                ->like('no_po', $search)
                ->orLike('users.name', $search)
                ->orLike('users.kode_customer', $search)
                ->orLike('transaction_pr_h_apis.customer_id', $search)
                ->groupEnd();
        }

        // Total setelah filter
        $recordsFiltered = $builder->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        $builder->limit($length, $start);
        $data = $builder->get()->getResultArray();
        

        $resultData = [];
        foreach($data as $row){
            $no = $start + 1;
            $status = ($row['is_proses_tol'] == 1)
                ? '<span class="badge bg-success">Sudah</span>'
                : '<span class="badge bg-info">Proses</span>';
            
            // Format tanggal untuk sorting yang lebih baik
            $formattedDate = $row['wkt_input'] ? date('Y-m-d H:i:s', strtotime($row['wkt_input'])) : '';
            
            $resultData[] = [
                $no,
                $row['no_po'],
                $row['kode_customer'],
                $row['customer_name'],
                $formattedDate, // Kirim dalam format ISO untuk sorting yang konsisten
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

    /**
     * Apply role-based access control filtering
     * 
     * @param object $builder Query builder instance
     * @param UserModel $userModel User model instance
     * @param int $pic_input Current user ID
     * @return void
     */
    private function applyRoleBasedFiltering($builder, UserModel $userModel, int $pic_input): void
    {
        $userRoles = $userModel->getRoles($pic_input);
        $roleNames = array_column($userRoles, 'name');
        
        if (in_array('Store Pic', $roleNames)) {
            $this->applyStorePicFiltering($builder, $userModel, $pic_input);
        } elseif (in_array('Super Admin', $roleNames)) {
            // Super Admin can see all transactions - no additional filtering needed
            return;
        } elseif (in_array('Admin', $roleNames)) {
            // Super Admin can see all transactions - no additional filtering needed
            return;
        } else {
            // Default: only show own transactions
            $builder->where('pic_input', $pic_input);
        }
    }

    /**
     * Apply Store Pic specific filtering based on group membership
     * 
     * @param object $builder Query builder instance
     * @param UserModel $userModel User model instance
     * @param int $pic_input Current user ID
     * @return void
     */
    private function applyStorePicFiltering($builder, UserModel $userModel, int $pic_input): void
    {
        $userData = $userModel->select('kode_group')->where('id', $pic_input)->first();
        $userGroup = $userData['kode_group'] ?? null;
        
        if (!$userGroup) {
            // No group assigned, fallback to own transactions only
            $builder->where('pic_input', $pic_input);
            return;
        }
        
        try {
            $dbStore = \Config\Database::connect('db_tol');
            $storeQuery = "SELECT customer_id FROM db_tol.mst_customer WHERE group_customer = ?";
            $storeResults = $dbStore->query($storeQuery, [$userGroup])->getResult();
            
            if (empty($storeResults)) {
                // No stores found for this group, fallback to own transactions
                $builder->where('pic_input', $pic_input);
                return;
            }
            
            $storeIds = array_map(function($row) { 
                return $row->customer_id; 
            }, $storeResults);
            $builder->whereIn('customer_id', $storeIds);
            
        } catch (\Exception $e) {
            log_message('error', '[Store Pic Filtering] Database error: ' . $e->getMessage());
            // On error, fallback to own transactions only
            $builder->where('pic_input', $pic_input);
        }
    }

    /**
     * Apply custom filters from request parameters
     * 
     * @param object $builder Query builder instance
     * @param object $request Request instance
     * @return void
     */
    private function applyCustomFilters($builder, $request): void
    {
        // Filter berdasarkan group store
        $filterGroup = $request->getGet('filter_group');
        if ($filterGroup) {
            // Ambil semua customer_id dari group yang dipilih
            $db = \Config\Database::connect('db_tol');
            $storeResults = $db->query('SELECT customer_id FROM mst_customer WHERE group_customer = ?', [$filterGroup])->getResult();
            $storeIds = array_map(function($row) { return $row->customer_id; }, $storeResults);
            if (!empty($storeIds)) {
                $builder->whereIn('transaction_pr_h_apis.customer_id', $storeIds);
            } else {
                // Jika tidak ada store, filter kosong
                $builder->where('transaction_pr_h_apis.customer_id', '');
            }
        }

        // Filter berdasarkan store
        $filterStore = $request->getGet('filter_store');
        if ($filterStore) {
            $builder->where('transaction_pr_h_apis.customer_id', $filterStore);
        }

        // Filter status
        $filterStatus = $request->getGet('filter_status');
        if ($filterStatus !== null && $filterStatus !== '') {
            $builder->where('transaction_pr_h_apis.is_proses_tol', $filterStatus);
        }

        // Filter tanggal
        $this->applyDateRangeFilter($builder, $request);
    }

    /**
     * Apply date range filtering
     * 
     * @param object $builder Query builder instance
     * @param object $request Request instance
     * @return void
     */
    private function applyDateRangeFilter($builder, $request): void
    {
        $filterDateFrom = $request->getGet('filter_date_from');
        $filterDateTo = $request->getGet('filter_date_to');

        if ($filterDateFrom) {
            $fromDate = \DateTime::createFromFormat('d-m-Y', $filterDateFrom);
            if ($fromDate) {
                $builder->where('DATE(wkt_input) >=', $fromDate->format('Y-m-d'));
            }
        }

        if ($filterDateTo) {
            $toDate = \DateTime::createFromFormat('d-m-Y', $filterDateTo);
            if ($toDate) {
                $builder->where('DATE(wkt_input) <=', $toDate->format('Y-m-d'));
            }
        }
    }

    /**
     * Export data transaksi ke CSV
     */
    public function exportCsv()
    {
        $request = $this->request;
        $model = new TransaksiWebModel();
        $userModel = new UserModel();
        
        $pic_input = session()->get('user_id');
        
        $builder = $model->select('transaction_pr_h_apis.*, users.name as customer_name, users.kode_customer')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left');

        // Apply role-based filtering
        $this->applyRoleBasedFiltering($builder, $userModel, $pic_input);
        
        // Apply custom filters (menggunakan filter group dan store)
        $this->applyCustomFilters($builder, $request);

        // Apply search filter jika ada
        $search = $request->getGet('search');
        if ($search) {
            $builder->groupStart()
                ->like('no_po', $search)
                ->orLike('users.name', $search)
                ->orLike('users.kode_customer', $search)
                ->orLike('transaction_pr_h_apis.customer_id', $search)
                ->groupEnd();
        }

        $builder->orderBy('wkt_input', 'desc');
        $data = $builder->get()->getResultArray();
        
        // Set header untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=transaksi_export_' . date('Y-m-d_H-i-s') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, [
            'No PO',
            'Kode Customer', 
            'Nama Customer',
            'Tanggal Input',
            'Status',
            'Hanya Jasa',
            'Jenis Lensa'
        ]);
        
        // Data CSV
        foreach ($data as $row) {
            $status = ($row['is_proses_tol'] == 1) ? 'Sudah' : 'Proses';
            $hanjaJasa = ($row['hanya_jasa'] == 1) ? 'Ya' : 'Tidak';
            
            fputcsv($output, [
                $row['no_po'],
                $row['kode_customer'],
                $row['customer_name'],
                $row['wkt_input'],
                $status,
                $hanjaJasa,
                $row['jenis_lensa'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
}