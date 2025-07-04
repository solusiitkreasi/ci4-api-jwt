<?php
namespace App\Services;

use App\Models\TransaksiModel;
use App\Models\UserModel;

class TransactionService
{
    protected $transaksiModel;
    protected $userModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiModel();
        $this->userModel = new UserModel();
    }

    /**
     * Membuat transaksi baru
     * @param array $data
     * @return int|false ID transaksi baru atau false jika gagal
     */
    public function createTransaction(array $data)
    {

        // Validasi sederhana, bisa dikembangkan
        if (empty($data['customer_id']) ) {
            return false;
        }

        // Tambahkan validasi bisnis lain jika perlu
        return $this->transaksiModel->insert($data) ? $data['id'] : false;
    }

    /**
     * Mendapatkan detail transaksi
     * @param int $id
     * @return array|null
     */
    public function getTransaction($id)
    {
        return $this->transaksiModel->find($id);
    }

    /**
     * Mendapatkan daftar transaksi (bisa difilter user, status, dsb)
     * @param array $filter
     * @return array
     */
    public function listTransactions(array $filter = [])
    {
        $builder = $this->transaksiModel;
        if (!empty($filter['user_id'])) {
            $builder = $builder->where('user_id', $filter['user_id']);
        }
        if (!empty($filter['status'])) {
            $builder = $builder->where('status', $filter['status']);
        }
        return $builder->findAll();
    }

    /**
     * Update status transaksi
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        return $this->transaksiModel->update($id, ['status' => $status]);
    }

    /**
     * Hapus transaksi
     * @param int $id
     * @return bool
     */
    public function deleteTransaction($id)
    {
        return $this->transaksiModel->delete($id);
    }
}
