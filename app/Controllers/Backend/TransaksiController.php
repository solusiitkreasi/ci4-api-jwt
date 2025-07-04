<?php 

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TransactionModel; // Gunakan model yang sama dengan API
use App\Models\TransaksiModel;
use App\Models\UserModel;

class TransaksiController extends BaseController
{
    public function index()
    {
        $transactionModel = new TransactionModel();
        $transaksiModel = new TransaksiModel();
        $userModel = new UserModel();
        // Ambil daftar kode_customer unik untuk filter
        $customers = $userModel->select('kode_customer, name')
            ->where('kode_customer IS NOT NULL')
            ->where('kode_customer !=', '')
            ->groupBy('kode_customer')
            ->orderBy('kode_customer', 'ASC')
            ->findAll();
        $data = [
            'title' => 'Manajemen Transaksi',
            // Gunakan paginasi bawaan model untuk tampilan web
            'transaksi' => $transaksiModel
                                ->select('transaction_pr_h_apis.*, users.name as customer_name')
                                ->join('users', 'users.id = transaction_pr_h_apis.pic_input')
                                ->orderBy('wkt_input', 'DESC')
                                ->paginate(10, 'transaksi'), // 10 item per halaman, grup 'transaction_pr_h_apis'
            'pager' => $transaksiModel->pager,
            'currentPage' => $transaksiModel->pager->getCurrentPage('transaksi'), // The current page number
            'totalPages'  => $transaksiModel->pager->getPageCount('transaksi'),   // The total page count
            'customers'   => $customers,
        ];

        return view('backend/pages/transaksi/list', $data);
    }

    public function datatables()
    {
        $request    = $this->request;
        $model      = new TransaksiModel();

        // Kolom harus sesuai urutan datatables (No, No PO, Nama Klien, wkt_input, Status, Aksi)
        $columns = [
            null, // No (nomor urut, tidak digunakan untuk order)
            'no_po',
            'customer_name',
            'wkt_input', // kolom ke-3 (index 3) untuk sorting tanggal
            'is_proses_tol',
            'transaction_pr_h_apis.id',
        ];

        $draw           = (int) $request->getGet('draw');
        $start          = (int) $request->getGet('start');
        $length         = (int) $request->getGet('length');
        $search         = $request->getGet('search')['value'] ?? '';
        $orderColIdx    = (int) $request->getGet('order')[0]['column'] ?? 3;
        $orderCol       = $columns[$orderColIdx] ?? 'wkt_input';

        if ($orderCol === null) $orderCol = 'wkt_input';
        $orderDir = $request->getGet('order')[0]['dir'] ?? 'desc';
        // Paksa order default jika tidak ada order dari datatables
        if (!$request->getGet('order')) {
            $orderCol = 'wkt_input';
            $orderDir = 'desc';
        }

        $builder = $model->select('transaction_pr_h_apis.*, users.name as customer_name, users.kode_customer')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left');

        // Filter by customer_id (kode_customer)
        $filterCustomer = $request->getGet('filter_customer');
        if ($filterCustomer) {
            $builder->where('transaction_pr_h_apis.customer_id', $filterCustomer);
        }
        // Filter by status (is_proses_tol)
        $filterStatus = $request->getGet('filter_status');
        if ($filterStatus !== null && $filterStatus !== '') {
            $builder->where('transaction_pr_h_apis.is_proses_tol', $filterStatus);
        }
        // Filter by tanggal (range)
        $filterDateFrom = $request->getGet('filter_date_from');
        $filterDateTo   = $request->getGet('filter_date_to');
        if ($filterDateFrom) {
            // Format ke Y-m-d
            $from = \DateTime::createFromFormat('d-m-Y', $filterDateFrom);
            if ($from) {
                $builder->where('DATE(wkt_input) >=', $from->format('Y-m-d'));
            }
        }
        if ($filterDateTo) {
            $to = \DateTime::createFromFormat('d-m-Y', $filterDateTo);
            if ($to) {
                $builder->where('DATE(wkt_input) <=', $to->format('Y-m-d'));
            }
        }

        // Total sebelum filter
        $totalRecords = $model->countAllResults(false);

        // Filter pencarian
        if ($search) {
            $builder->groupStart()
                ->like('no_po', $search)
                ->orLike('users.name', $search)
                ->orLike('transaction_pr_h_apis.is_proses_tol', $search)
                ->groupEnd();
        }

        // Total setelah filter
        $recordsFiltered = $builder->countAllResults(false);

        $builder->orderBy($orderCol, $orderDir);
        $builder->limit($length, $start);
        $data = $builder->get()->getResultArray();

        $result = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => array_map(function($row) use (&$start) {
                static $i = 0;
                $no = $start + (++$i);
                // Badge status: null/0 = Proses, 1 = Sudah
                $status = ($row['is_proses_tol'] == 1)
                    ? '<span class="badge bg-success">Sudah</span>'
                    : '<span class="badge bg-info">Proses</span>';
                return [
                    $no,
                    $row['no_po'],
                    $row['kode_customer'],
                    $row['customer_name'],
                    $row['wkt_input'],
                    $status,
                    '<a href="/backend/transaksi/detail/'.esc($row['id']).'" class="btn btn-sm btn-info">Detail</a>'
                ];
            }, $data)
        ];

        return $this->response->setJSON($result);
    }

    public function detail($id)
    {
        $transaksiModel = new TransaksiModel();
        $transaksiJasaModel = new \App\Models\TransaksiJasaModel();
        $jasaModel = new \App\Models\JasaModel();
        $userModel = new \App\Models\UserModel();

        // Ambil data transaksi utama
        $transaksi = $transaksiModel
            ->select('transaction_pr_h_apis.*, users.name as pic')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left')
            ->where('transaction_pr_h_apis.id', $id)
            ->first();

        // Ambil data jasa terkait transaksi (tanpa join ke tabel jasa)
        $jasaTransaksi = $transaksiJasaModel
            ->where('transaction_pr_jasa_d_apis.id', $id)
            ->findAll();

        // Ambil semua jasa_id yang terlibat
        $jasaIds = array_column($jasaTransaksi, 'jasa_id');
        $jasaList = [];
        if (!empty($jasaIds)) {
            // Query ke JasaModel (db_tol) untuk ambil nama jasa
            $jasaList = $jasaModel->whereIn('kode_jasa', $jasaIds)->findAll();
            $jasaList = array_column($jasaList, 'nama_jasa', 'kode_jasa'); // [id => nama]
        }

        // Gabungkan nama jasa ke data jasa transaksi
        foreach ($jasaTransaksi as &$jasa) {
            $jasa['nama_jasa'] = $jasaList[$jasa['jasa_id']] ?? '-';
        }
        unset($jasa);

        return view('backend/pages/transaksi/detail', [
            'transaksi' => $transaksi,
            'jasa' => $jasaTransaksi
        ]);
    }

    public function exportCsv()
    {
        $request    = $this->request;
        $model      = new TransaksiModel();
        $builder = $model->select('transaction_pr_h_apis.*, users.name as customer_name, users.kode_customer')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left');

        // Filter by customer_id (kode_customer)
        $filterCustomer = $request->getGet('filter_customer');
        if ($filterCustomer) {
            $builder->where('transaction_pr_h_apis.customer_id', $filterCustomer);
        }
        // Filter by status (is_proses_tol)
        $filterStatus = $request->getGet('filter_status');
        if ($filterStatus !== null && $filterStatus !== '') {
            $builder->where('transaction_pr_h_apis.is_proses_tol', $filterStatus);
        }
        // Filter by tanggal (range)
        $filterDateFrom = $request->getGet('filter_date_from');
        $filterDateTo   = $request->getGet('filter_date_to');
        if ($filterDateFrom) {
            $from = \DateTime::createFromFormat('d-m-Y', $filterDateFrom);
            if ($from) {
                $builder->where('DATE(wkt_input) >=', $from->format('Y-m-d'));
            }
        }
        if ($filterDateTo) {
            $to = \DateTime::createFromFormat('d-m-Y', $filterDateTo);
            if ($to) {
                $builder->where('DATE(wkt_input) <=', $to->format('Y-m-d'));
            }
        }
        // Filter pencarian
        $search = $request->getGet('search');
        if ($search) {
            $builder->groupStart()
                ->like('no_po', $search)
                ->orLike('users.name', $search)
                ->orLike('transaction_pr_h_apis.is_proses_tol', $search)
                ->groupEnd();
        }
        $builder->orderBy('wkt_input', 'DESC');
        $data = $builder->get()->getResultArray();

        // Set header CSV
        $filename = 'transaksi_export_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        // Header kolom
        fputcsv($output, ['No', 'No PO', 'Kode Customer', 'Nama Klien', 'Tanggal', 'Status']);
        $no = 1;
        foreach ($data as $row) {
            $status = ($row['is_proses_tol'] == 1) ? 'Sudah' : 'Proses';
            fputcsv($output, [
                $no++, $row['no_po'], $row['kode_customer'], $row['customer_name'], $row['wkt_input'], $status
            ]);
        }
        fclose($output);
        exit;
    }

}