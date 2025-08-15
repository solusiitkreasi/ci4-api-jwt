<?php 

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TransaksiWebModel; // Gunakan model web yang baru
use App\Models\UserModel;
use App\Services\TransactionWebService;
use App\Services\PaymentService; // Tetap ada jika diperlukan nanti

class TransaksiController extends BaseController
{
    protected $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionWebService();
    }

    public function index()
    {
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

        if (!$transaksi) {
            // Jika transaksi tidak ditemukan, redirect ke list transaksi
            return redirect()->to(base_url('backend/transaksi'));
        }

        $transaksiJasaModel = new \App\Models\TransaksiJasaModel();
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

    /**
     * Generate validation rules untuk form transaksi
     * 
     * @param object $data_lensa Data lensa dari POST
     * @param array $postData Semua data POST
     * @return array Rules validasi
     */
    private function getTransaksiValidationRules($data_lensa, $postData)
    {
        // Rules dasar untuk semua transaksi
        $rules = [
            'data_lensa.no_po' => [
                'label' => 'Nomor Nota',
                'rules' => 'required|string|min_length[3]|max_length[50]',
                'errors' => [
                    'required' => 'Nomor Nota wajib diisi.',
                    'min_length' => 'Nomor Nota minimal terdiri dari 3 karakter.',
                    'max_length' => 'Nomor Nota maksimal 50 karakter.',
                    'string' => 'Nomor Nota harus berupa teks yang valid.'
                ]
            ],
            'data_lensa.nama_customer' => [
                'label' => 'Nama Pelanggan',
                'rules' => 'required|string|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Nama Pelanggan wajib diisi.',
                    'min_length' => 'Nama Pelanggan minimal terdiri dari 3 karakter.',
                    'max_length' => 'Nama Pelanggan maksimal 100 karakter.',
                    'string' => 'Nama Pelanggan harus berupa teks yang valid.'
                ]
            ],
            'data_lensa.tgl_selesai' => [
                'label' => 'Tanggal Selesai TOL',
                'rules' => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required' => 'Tanggal Selesai TOL wajib diisi.',
                    'valid_date' => 'Format Tanggal Selesai TOL tidak valid.'
                ]
            ],
            'data_lensa.hanya_jasa' => [
                'label' => 'Jenis Transaksi',
                'rules' => 'required|in_list[0,1]',
                'errors' => [
                    'required' => 'Jenis Transaksi wajib dipilih.',
                    'in_list' => 'Jenis Transaksi tidak valid. Pilih "Lensa + Jasa" atau "Hanya Jasa".'
                ]
            ],
            'data_lensa.keterangan' => [
                'label' => 'Keterangan',
                'rules' => 'permit_empty|string|max_length[500]',
                'errors' => [
                    'string' => 'Keterangan harus berupa teks.',
                    'max_length' => 'Keterangan maksimal 500 karakter.'
                ]
            ],
        ];

        // Tambahkan rules untuk frame measurements
        $rules = array_merge($rules, $this->getFrameMeasurementRules());

        // Rules khusus berdasarkan jenis transaksi
        if (($data_lensa->hanya_jasa ?? '0') == '0') {
            // Mode Lensa + Jasa
            $rules = array_merge($rules, $this->getLensaValidationRules());
        } else {
            // Mode Hanya Jasa
            $rules = array_merge($rules, $this->getJasaValidationRules($postData));
        }

        return $rules;
    }

    /**
     * Rules validasi untuk frame measurements
     */
    private function getFrameMeasurementRules()
    {
        return [
            'data_lensa.a_measurement' => [
                'label' => 'A (Lens Size)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'A (Lens Size) harus berupa angka.',
                    'greater_than_equal_to' => 'A (Lens Size) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.dbl_measurement' => [
                'label' => 'DBL (Bridge Size)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'DBL (Bridge Size) harus berupa angka.',
                    'greater_than_equal_to' => 'DBL (Bridge Size) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.ed_measurement' => [
                'label' => 'ED (Effective Diameter)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'ED (Effective Diameter) harus berupa angka.',
                    'greater_than_equal_to' => 'ED (Effective Diameter) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.b_measurement' => [
                'label' => 'B (Vertical)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'B (Vertical) harus berupa angka.',
                    'greater_than_equal_to' => 'B (Vertical) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.sh_py_measurement' => [
                'label' => 'SH/PY (Segment Height)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'SH/PY (Segment Height) harus berupa angka.',
                    'greater_than_equal_to' => 'SH/PY (Segment Height) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.mbs_measurement' => [
                'label' => 'MBS',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'MBS harus berupa angka.',
                    'greater_than_equal_to' => 'MBS tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.r_et' => [
                'label' => 'ET Kanan (Edge Thickness)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'ET Kanan (Edge Thickness) harus berupa angka.',
                    'greater_than_equal_to' => 'ET Kanan (Edge Thickness) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.r_ct' => [
                'label' => 'CT Kanan (Center Thickness)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'CT Kanan (Center Thickness) harus berupa angka.',
                    'greater_than_equal_to' => 'CT Kanan (Center Thickness) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.l_et' => [
                'label' => 'ET Kiri (Edge Thickness)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'ET Kiri (Edge Thickness) harus berupa angka.',
                    'greater_than_equal_to' => 'ET Kiri (Edge Thickness) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.l_ct' => [
                'label' => 'CT Kiri (Center Thickness)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'CT Kiri (Center Thickness) harus berupa angka.',
                    'greater_than_equal_to' => 'CT Kiri (Center Thickness) tidak boleh kurang dari 0.'
                ]
            ],
        ];
    }

    /**
     * Rules validasi untuk data lensa (mode Lensa + Jasa)
     */
    private function getLensaValidationRules()
    {
        return [
            'data_lensa.kd_brand' => [
                'label' => 'Kode Brand',
                'rules' => 'permit_empty|string|max_length[20]',
                'errors' => [
                    'string' => 'Kode Brand harus berupa teks.',
                    'max_length' => 'Kode Brand maksimal 20 karakter.'
                ]
            ],
            'data_lensa.jenis_lensa' => [
                'label' => 'Jenis Lensa',
                'rules' => 'permit_empty|string|max_length[50]',
                'errors' => [
                    'string' => 'Jenis Lensa harus berupa teks.',
                    'max_length' => 'Jenis Lensa maksimal 50 karakter.'
                ]
            ],

            'data_lensa.r_lensa' => [
                'label' => 'Kode Lensa Kanan',
                'rules' => 'permit_empty|string|max_length[50]',
                'errors' => [
                    'string' => 'Kode Lensa Kanan harus berupa teks yang valid.',
                    'max_length' => 'Kode Lensa Kanan maksimal 50 karakter.'
                ]
            ],
            'data_lensa.r_spheris' => [
                'label' => 'SPH Kanan',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'SPH Kanan harus berupa teks yang valid.',
                    'max_length' => 'SPH Kanan maksimal 10 karakter.'
                ]
            ],
            'data_lensa.r_cylinder' => [
                'label' => 'CYL Kanan',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'CYL Kanan harus berupa teks yang valid.',
                    'max_length' => 'CYL Kanan maksimal 10 karakter.'
                ]
            ],
            'data_lensa.r_axis' => [
                'label' => 'AXIS Kanan',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'AXIS Kanan harus berupa teks yang valid.',
                    'max_length' => 'AXIS Kanan maksimal 10 karakter.'
                ]
            ],
            'data_lensa.r_add' => [
                'label' => 'ADD Kanan',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'ADD Kanan harus berupa teks yang valid.',
                    'max_length' => 'ADD Kanan maksimal 10 karakter.'
                ]
            ],
            'data_lensa.r_prisma' => [
                'label' => 'Prisma Kanan',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'Prisma Kanan harus berupa teks yang valid.',
                    'max_length' => 'Prisma Kanan maksimal 10 karakter.'
                ]
            ],
            'data_lensa.r_pd_far' => [
                'label' => 'PDF Kanan (PD Far)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'PDF Kanan (PD Far) harus berupa angka.',
                    'greater_than_equal_to' => 'PDF Kanan (PD Far) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.r_pd_near' => [
                'label' => 'PDN Kanan (PD Near)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'PDN Kanan (PD Near) harus berupa angka.',
                    'greater_than_equal_to' => 'PDN Kanan (PD Near) tidak boleh kurang dari 0.'
                ]
            ],

            'data_lensa.l_lensa' => [
                'label' => 'Kode Lensa Kiri',
                'rules' => 'permit_empty|string|max_length[50]',
                'errors' => [
                    'string' => 'Kode Lensa Kiri harus berupa teks yang valid.',
                    'max_length' => 'Kode Lensa Kiri maksimal 50 karakter.'
                ]
            ],
            'data_lensa.l_spheris' => [
                'label' => 'SPH Kiri',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'SPH Kiri harus berupa teks yang valid.',
                    'max_length' => 'SPH Kiri maksimal 10 karakter.'
                ]
            ],
            'data_lensa.l_cylinder' => [
                'label' => 'CYL Kiri',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'CYL Kiri harus berupa teks yang valid.',
                    'max_length' => 'CYL Kiri maksimal 10 karakter.'
                ]
            ],
            'data_lensa.l_axis' => [
                'label' => 'AXIS Kiri',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'AXIS Kiri harus berupa teks yang valid.',
                    'max_length' => 'AXIS Kiri maksimal 10 karakter.'
                ]
            ],
            'data_lensa.l_add' => [
                'label' => 'ADD Kiri',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'ADD Kiri harus berupa teks yang valid.',
                    'max_length' => 'ADD Kiri maksimal 10 karakter.'
                ]
            ],
            'data_lensa.l_prisma' => [
                'label' => 'Prisma Kiri',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'Prisma Kiri harus berupa teks yang valid.',
                    'max_length' => 'Prisma Kiri maksimal 10 karakter.'
                ]
            ],
            'data_lensa.l_pd_far' => [
                'label' => 'PDF Kiri (PD Far)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'PDF Kiri (PD Far) harus berupa angka.',
                    'greater_than_equal_to' => 'PDF Kiri (PD Far) tidak boleh kurang dari 0.'
                ]
            ],
            'data_lensa.l_pd_near' => [
                'label' => 'PDN Kiri (PD Near)',
                'rules' => 'permit_empty|numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric' => 'PDN Kiri (PD Near) harus berupa angka.',
                    'greater_than_equal_to' => 'PDN Kiri (PD Near) tidak boleh kurang dari 0.'
                ]
            ],

            'data_lensa.base_curve' => [
                'label' => 'Base Curve',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'Base Curve harus berupa teks yang valid.',
                    'max_length' => 'Base Curve maksimal 10 karakter.'
                ]
            ],
            'data_lensa.prisma2' => [
                'label' => 'Prisma 2',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'Prisma 2 harus berupa teks yang valid.',
                    'max_length' => 'Prisma 2 maksimal 10 karakter.'
                ]
            ],
            'data_lensa.base2' => [
                'label' => 'Base 2',
                'rules' => 'permit_empty|string|max_length[10]',
                'errors' => [
                    'string' => 'Base 2 harus berupa teks yang valid.',
                    'max_length' => 'Base 2 maksimal 10 karakter.'
                ]
            ],
            
        ];
    }

    /**
     * Rules validasi untuk data jasa (mode Hanya Jasa)
     */
    private function getJasaValidationRules($postData)
    {
        $rules = [];
        
        if (!empty($postData['data_jasa'])) {
            foreach ($postData['data_jasa'] as $key => $jasa) {
                $rules["data_jasa.{$key}.jasa_id"] = [
                    'label' => 'Kode Jasa',
                    'rules' => 'required|string|max_length[20]',
                    'errors' => [
                        'required' => 'Kode Jasa wajib diisi.',
                        'string' => 'Kode Jasa harus berupa teks yang valid.',
                        'max_length' => 'Kode Jasa maksimal 20 karakter.'
                    ]
                ];
                $rules["data_jasa.{$key}.jasa_nama"] = [
                    'label' => 'Nama Jasa',
                    'rules' => 'required|string|max_length[100]',
                    'errors' => [
                        'required' => 'Nama Jasa wajib diisi.',
                        'string' => 'Nama Jasa harus berupa teks yang valid.',
                        'max_length' => 'Nama Jasa maksimal 100 karakter.'
                    ]
                ];
                $rules["data_jasa.{$key}.jasa_qty"] = [
                    'label' => 'Jumlah Jasa',
                    'rules' => 'required|integer|greater_than[0]|less_than_equal_to[2]',
                    'errors' => [
                        'required' => 'Jumlah Jasa wajib diisi.',
                        'integer' => 'Jumlah Jasa harus berupa angka bulat.',
                        'greater_than' => 'Jumlah Jasa harus lebih dari 0 (nol).',
                        'less_than_equal_to' => 'Jumlah Jasa maksimal 2 (dua).'
                    ]
                ];
            }
        }
        
        return $rules;
    }

    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            
            $validation = \Config\Services::validation();
            $postData   = $this->request->getPost();
            $data_lensa = (object) ($postData['data_lensa'] ?? []);
            $data_jasa  = $postData['data_jasa'] ?? [];

            // Generate validation rules menggunakan function terpisah
            $rules = $this->getTransaksiValidationRules($data_lensa, $postData);
            if (!$this->validate($rules)) {
                return $this->showError('Validasi gagal, silakan periksa kembali data yang Anda masukkan.', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $user = $userModel->find(session()->get('user_id'));
            if (!$user) {
                return $this->showError('Sesi tidak valid atau user tidak ditemukan.');
            }

            // Cek no_po sudah ada untuk customer_id
            $transaksiModel = new \App\Models\TransaksiWebModel();
            $existing = $transaksiModel->where('no_po', $data_lensa->no_po)
                ->where('customer_id', $user['kode_customer'])
                ->first();
            if ($existing) {
                // Kirim error khusus ke view beserta salesman
                return $this->showError('Nomor Nota yang Anda masukkan sudah terdaftar untuk toko ini. Silakan gunakan nomor nota yang berbeda.', ['no_po_exists' => true, 'no_po' => $data_lensa->no_po]);
            }
            
            $result = $this->transactionService->createTransactionLogic($data_lensa, $data_jasa, (object)$user);
            if ($result['status'] === 'error') {
                return $this->showError($result['message']);
            }

            return view('backend/pages/transaksi/create_result', [
                'transaksi' => $result,
                'payment' => null
            ]);
        }

        $db_tol = \Config\Database::connect('db_tol');
        $kode_customer = session()->get('kode_customer');
        # GET Salesman
        $query_sales = 'SELECT b.nama_salesman
            FROM db_tol.mst_sales_customer a
            LEFT JOIN db_tol.mst_salesman b ON a.salesman_id = b.salesman_id
            WHERE customer_id ="'.$kode_customer.'" AND (DATE(tgl_awal) <= NOW()) AND (DATE(tgl_akhir)>=NOW())
        ';
        $sales = $db_tol->query($query_sales)->getRowArray();
        if($sales){
            $nama_sales = $sales['nama_salesman'];
        }else{
            $nama_sales = '-';
        }

        $userModel = new UserModel();
        $customers = $userModel->select('kode_customer, name')
            ->where('kode_customer IS NOT NULL')->where('kode_customer !=', '')
            ->groupBy('kode_customer')->orderBy('kode_customer', 'ASC')
        ->findAll();

        $userId = session()->get('user_id');
        return view('backend/pages/transaksi/create', [
            'title' => 'Create Transaksi',
            'customers' => $customers,
            'user_id' => $userId,
            'salesman' => $nama_sales
        ]);
    }

    private function showError(string $msg, ?array $errors = null)
    {
        // Ambil salesman
        $db_tol         = \Config\Database::connect('db_tol');
        $userModel      = new UserModel();
        $user           = $userModel->find(session()->get('user_id'));
        $kode_customer  = $user['kode_customer'];
        $query_sales    = 'SELECT b.nama_salesman FROM db_tol.mst_sales_customer a
                            LEFT JOIN db_tol.mst_salesman b ON a.salesman_id = b.salesman_id
                            WHERE customer_id ="'.$kode_customer.'" AND (DATE(tgl_awal) <= NOW()) AND (DATE(tgl_akhir)>=NOW())
                        ';
        $sales          = $db_tol->query($query_sales)->getRowArray();
        $nama_sales     = $sales ? $sales['nama_salesman'] : '-';
        
        $customers = $userModel->select('kode_customer, name')
            ->where('kode_customer IS NOT NULL')->where('kode_customer !=', '')
            ->groupBy('kode_customer')->orderBy('kode_customer', 'ASC')->findAll();
        $userId = session()->get('user_id');
        return view('backend/pages/transaksi/create', [
            'error' => $msg,
            'errors' => $errors, // Teruskan array errors ke view
            'customers' => $customers,
            'user_id' => $userId,
            'salesman' => $nama_sales
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