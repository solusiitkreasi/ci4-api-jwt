<?php 

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TransactionModel; // Gunakan model yang sama dengan API

class DashboardController extends BaseController
{
    public function index()
    {
        $transactionModel = new TransactionModel();
        
        $data = [
            'title' => 'Dashboard',
            // Gunakan paginasi bawaan model untuk tampilan web
            'transactions' => $transactionModel
                                ->select('transactions.*, users.name as customer_name')
                                ->join('users', 'users.id = transactions.user_id')
                                ->orderBy('transaction_date', 'DESC')
                                ->paginate(10, 'transactions'), // 10 item per halaman, grup 'transactions'
            'pager' => $transactionModel->pager,
        ];

        return view('backend/pages/dashboard', $data);
    }
}