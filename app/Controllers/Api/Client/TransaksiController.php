<?php

namespace App\Controllers\Api\Client;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TransaksiModel;
use App\Models\TransaksiJasaModel;
use CodeIgniter\API\ResponseTrait;
use Config\Services;
use App\Services\TransactionService;

class TransaksiController extends BaseController
{
    use ResponseTrait;
    protected $currentUser;
    protected $transactionService;
    protected $transaksiModel; // Tetap ada untuk list/detail
    protected $transaksiJasaModel;

    public function __construct()
    {
        $this->transactionService = new TransactionService();
        $this->transaksiModel = new TransaksiModel();
        $this->transaksiJasaModel = new TransaksiJasaModel();
        $this->currentUser = service('request')->user ?? Services::getSharedInstance('user');
        helper(['response', 'text']);
    }

    public function listTrn()
    {
        $userModel = new UserModel();
        $page = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('perPage') ?? 10;
        $pic_input = $this->currentUser->id;

        $builder = $this->transaksiModel->select('id, no_po, customer_id, nama_customer, hanya_jasa, jenis_lensa, wkt_input');

        $getRole = $userModel->getRoles($pic_input);
        $filterRole = array_column($getRole, 'name');

        if (in_array('Store Pic', $filterRole)) {
            $get_customers_data = $userModel->select('kode_group')->where('id', $pic_input)->first();
            $get_group = $get_customers_data['kode_group'] ?? null;
            if ($get_group) {
                $db_store = \Config\Database::connect('db_tol');
                $get_store = $db_store->query("SELECT customer_id FROM db_tol.mst_customer WHERE group_customer = ?", [$get_group])->getResult();
                $storeIDArray = array_map(fn($row) => $row->customer_id, $get_store);
                if (!empty($storeIDArray)) {
                    $builder->whereIn('customer_id', $storeIDArray);
                } else {
                    $builder->where('pic_input', $pic_input); // Fallback jika grup tidak punya store
                }
            } else {
                $builder->where('pic_input', $pic_input);
            }
        } else {
            $builder->where('pic_input', $pic_input);
        }
        
        $transaksi = $builder->paginate($perPage, 'default', $page);
        $pager = $this->transaksiModel->pager;

        return api_response([
            'transaksi' => $transaksi,
            'pagination' => [
                'total' => $pager->getTotal(),
                'perPage' => $pager->getPerPage(),
                'currentPage' => $pager->getCurrentPage(),
                'lastPage' => $pager->getLastPage(),
            ]
        ], 'Transaksi fetched successfully');
    }
    
    public function getTrnDetail($id = null)
    {
        $builder = $this->transaksiModel->select('id, no_po, customer_id, nama_customer, hanya_jasa, jenis_lensa, wkt_input');
        $pic_input = $this->currentUser->id;
        $builder->where('pic_input', $pic_input);
        $transaksi = $builder->find($id);
        if (!$transaksi) {
            return api_error('Detail Transaksi not found', 404);
        }

        $getjasa = $this->transaksiJasaModel->where('id', $id)->findAll();
        foreach ($getjasa as &$jasa) {
            $db = \Config\Database::connect('db_tol');
            $jasaInfo = $db->query('SELECT nama_jasa FROM db_tol.mst_jjasa WHERE kode_jasa=? AND aktif=1', [$jasa['jasa_id']])->getRow();
            $jasa['nama_jasa'] = $jasaInfo ? $jasaInfo->nama_jasa : 'Kode Jasa Not Found';
        }
        
        return api_response([
            'transaksi' => $transaksi,
            'jasa' => $getjasa
        ], 'Detail Transaksi fetched successfully');
    }

    public function createTrn()
    {
        $data_lensa = $this->request->getVar('data_lensa');
        $data_jasa  = $this->request->getVar('data_jasa') ?? [];
        $user       = $this->currentUser;

        if (empty($data_lensa) || !is_object($data_lensa)) {
            return api_error('data_lensa is required and must be an object.', 400);
        }

        $result = $this->transactionService->createTransactionLogic($data_lensa, $data_jasa, $user);

        if ($result['status'] === 'error') {
            return api_error($result['message'], 400, ($result['errors'] ?? []));
        }

        return api_response($result['data'], 'Transaction created successfully', 201);
    }
}