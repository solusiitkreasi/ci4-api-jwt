<?php 

namespace App\Controllers\Web\Admin;

use App\Controllers\BaseController;
use App\Models\TransactionModel; // Gunakan model yang sama dengan API
use App\Models\TransaksiModel;

class TransaksiController extends BaseController
{
    public function index()
    {
        $transactionModel = new TransactionModel();
        $transaksiModel = new TransaksiModel();
        
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
        ];

        // tesx($data['pager']->links());

        return view('backend/pages/transaksi/list', $data);
    }
}