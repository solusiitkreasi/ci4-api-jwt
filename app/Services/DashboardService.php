<?php

namespace App\Services;

use App\Models\TransaksiWebModel;
use App\Models\UserModel;

class DashboardService
{
    private $transaksiModel;
    private $userModel;

    public function __construct()
    {
        $this->transaksiModel = new TransaksiWebModel();
        $this->userModel = new UserModel();
    }

    /**
     * Get dashboard statistics for specific user
     */
    public function getDashboardStats(int $userId): array
    {
        return [
            'total_transactions' => $this->getTotalTransactions($userId),
            'completed_transactions' => $this->getCompletedTransactions($userId),
            'pending_transactions' => $this->getPendingTransactions($userId),
            'monthly_growth' => $this->getMonthlyGrowth($userId),
        ];
    }

    /**
     * Get total transaction count for user based on role
     */
    private function getTotalTransactions(int $userId): int
    {
        $builder = $this->transaksiModel->builder();
        $this->applyRoleBasedFiltering($builder, $userId);
        return $builder->countAllResults();
    }

    /**
     * Get completed transaction count for user based on role
     */
    private function getCompletedTransactions(int $userId): int
    {
        $builder = $this->transaksiModel->builder();
        $this->applyRoleBasedFiltering($builder, $userId);
        $builder->where('is_proses_tol', 1);
        return $builder->countAllResults();
    }

    /**
     * Get pending transaction count for user based on role
     */
    private function getPendingTransactions(int $userId): int
    {
        $builder = $this->transaksiModel->builder();
        $this->applyRoleBasedFiltering($builder, $userId);
        $builder->where('is_proses_tol', 0);
        return $builder->countAllResults();
    }

    /**
     * Get monthly growth percentage
     */
    private function getMonthlyGrowth(int $userId): float
    {
        $currentMonth = date('Y-m');
        $lastMonth = date('Y-m', strtotime('-1 month'));

        // Current month transactions
        $currentBuilder = $this->transaksiModel->builder();
        $this->applyRoleBasedFiltering($currentBuilder, $userId);
        $currentBuilder->where('DATE_FORMAT(wkt_input, "%Y-%m")', $currentMonth);
        $currentCount = $currentBuilder->countAllResults();

        // Last month transactions
        $lastBuilder = $this->transaksiModel->builder();
        $this->applyRoleBasedFiltering($lastBuilder, $userId);
        $lastBuilder->where('DATE_FORMAT(wkt_input, "%Y-%m")', $lastMonth);
        $lastCount = $lastBuilder->countAllResults();

        if ($lastCount == 0) {
            return $currentCount > 0 ? 100.0 : 0.0;
        }

        return round((($currentCount - $lastCount) / $lastCount) * 100, 2);
    }

    /**
     * Apply role-based filtering to builder
     */
    private function applyRoleBasedFiltering($builder, int $userId): void
    {
        $userRoles = $this->userModel->getRoles($userId);
        $roleNames = array_column($userRoles, 'name');
        
        if (in_array('Store Pic', $roleNames)) {
            $this->applyStorePicFiltering($builder, $userId);
        } elseif (in_array('Super Admin', $roleNames)) {
            // Super Admin can see all transactions - no additional filtering needed
            return;
        } else {
            // Default: only show own transactions
            $builder->where('pic_input', $userId);
        }
    }

    /**
     * Apply Store Pic specific filtering based on group membership
     */
    private function applyStorePicFiltering($builder, int $userId): void
    {
        $userData = $this->userModel->select('kode_group')->where('id', $userId)->first();
        $userGroup = $userData['kode_group'] ?? null;
        
        if (!$userGroup) {
            // No group assigned, fallback to own transactions only
            $builder->where('pic_input', $userId);
            return;
        }
        
        try {
            $dbStore = \Config\Database::connect('db_tol');
            $storeQuery = "SELECT customer_id FROM db_tol.mst_customer WHERE group_customer = ?";
            $storeResults = $dbStore->query($storeQuery, [$userGroup])->getResult();
            
            if (empty($storeResults)) {
                // No stores found for this group, fallback to own transactions
                $builder->where('pic_input', $userId);
                return;
            }
            
            $storeIds = array_map(function($row) { 
                return $row->customer_id; 
            }, $storeResults);
            $builder->whereIn('customer_id', $storeIds);
            
        } catch (\Exception $e) {
            log_message('error', '[Dashboard Service Store Pic Filtering] Database error: ' . $e->getMessage());
            // On error, fallback to own transactions only
            $builder->where('pic_input', $userId);
        }
    }

    /**
     * Get recent transactions with enhanced data
     */
    public function getRecentTransactionsWithDetails(int $userId, int $limit = 10): array
    {
        $builder = $this->transaksiModel->select('
            transaction_pr_h_apis.*, 
            users.name as customer_name, 
            users.kode_customer,
            CASE 
                WHEN transaction_pr_h_apis.is_proses_tol = 1 THEN "Selesai"
                ELSE "Proses"
            END as status_text,
            CASE 
                WHEN transaction_pr_h_apis.is_proses_tol = 1 THEN "success"
                ELSE "info"
            END as status_class
        ')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left');
        
        $this->applyRoleBasedFiltering($builder, $userId);
        
        return $builder->orderBy('wkt_input', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }
}
