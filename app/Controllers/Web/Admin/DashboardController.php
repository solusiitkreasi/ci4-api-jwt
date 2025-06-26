<?php 

namespace App\Controllers\Web\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\TransactionModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $productModel = new ProductModel();
        
        // Contoh mengambil data langsung dari model
        $data = [
            'title'        => 'Admin Dashboard',
            'totalUsers'   => $userModel->countAllResults(),
            'totalProducts'=> $productModel->countAllResults(),
            'userName'     => session()->get('name'),
        ];


        // tesx($data, session()->get());
        
        return view('backend/pages/dashboard', $data);
    }
}