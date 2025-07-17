<?php 

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\TransaksiWebModel;
use App\Models\UserModel;
use App\Services\DashboardService;

class DashboardController extends BaseController
{
    public function index()
    {
        $transaksiModel = new TransaksiWebModel();
        $userModel = new UserModel();
        $dashboardService = new DashboardService();
        
        $pic_input = session()->get('user_id');
        
        // Get dashboard statistics
        $dashboardStats = $dashboardService->getDashboardStats($pic_input);
        
        // Get recent transactions with role-based filtering
        $recentTransactions = $this->getRecentTransactions($transaksiModel, $userModel, $pic_input);
        
        // Get chart data for monthly and yearly statistics
        $monthlyStats = $this->getMonthlyStats($transaksiModel, $userModel, $pic_input);
        $yearlyStats = $this->getYearlyStats($transaksiModel, $userModel, $pic_input);
        
        $data = [
            'title' => 'Dashboard',
            'dashboard_stats' => $dashboardStats,
            'recent_transactions' => $recentTransactions,
            'monthly_stats' => $monthlyStats,
            'yearly_stats' => $yearlyStats,
        ];

        return view('backend/pages/dashboard', $data);
    }

    /**
     * Get chart data for dashboard via AJAX
     */
    public function getChartData()
    {
        $transaksiModel = new TransaksiWebModel();
        $userModel = new UserModel();
        $pic_input = session()->get('user_id');
        
        $type = $this->request->getGet('type'); // 'monthly' or 'yearly'
        $year = $this->request->getGet('year') ?? date('Y');
        
        if ($type === 'monthly') {
            $data = $this->getMonthlyChartData($transaksiModel, $userModel, $pic_input, $year);
        } else {
            $data = $this->getYearlyChartData($transaksiModel, $userModel, $pic_input);
        }
        
        return $this->response->setJSON($data);
    }

    /**
     * Get recent transactions with role-based filtering
     */
    private function getRecentTransactions($transaksiModel, $userModel, $pic_input, $limit = 10)
    {
        $builder = $transaksiModel->select('transaction_pr_h_apis.*, users.name as customer_name, users.kode_customer')
            ->join('users', 'users.id = transaction_pr_h_apis.pic_input', 'left');
        
        $this->applyRoleBasedFiltering($builder, $userModel, $pic_input);
        
        return $builder->orderBy('wkt_input', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Get monthly statistics for current year
     */
    private function getMonthlyStats($transaksiModel, $userModel, $pic_input)
    {
        $currentYear = date('Y');
        return $this->getMonthlyChartData($transaksiModel, $userModel, $pic_input, $currentYear);
    }

    /**
     * Get yearly statistics for last 5 years
     */
    private function getYearlyStats($transaksiModel, $userModel, $pic_input)
    {
        return $this->getYearlyChartData($transaksiModel, $userModel, $pic_input);
    }

    /**
     * Get monthly chart data for specific year
     */
    private function getMonthlyChartData($transaksiModel, $userModel, $pic_input, $year)
    {
        $builder = $transaksiModel->select("
            MONTH(wkt_input) as month,
            COUNT(*) as total_transactions,
            SUM(CASE WHEN is_proses_tol = 1 THEN 1 ELSE 0 END) as completed_transactions
        ");
        
        $this->applyRoleBasedFiltering($builder, $userModel, $pic_input);
        
        $builder->where('YEAR(wkt_input)', $year)
                ->groupBy('MONTH(wkt_input)')
                ->orderBy('MONTH(wkt_input)', 'ASC');
        
        $results = $builder->get()->getResultArray();
        
        // Initialize data for all 12 months
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[$i] = [
                'month' => $i,
                'month_name' => date('F', mktime(0, 0, 0, $i, 1)),
                'total_transactions' => 0,
                'completed_transactions' => 0
            ];
        }
        
        // Fill with actual data
        foreach ($results as $row) {
            $monthlyData[$row['month']] = [
                'month' => $row['month'],
                'month_name' => date('F', mktime(0, 0, 0, $row['month'], 1)),
                'total_transactions' => (int)$row['total_transactions'],
                'completed_transactions' => (int)$row['completed_transactions']
            ];
        }
        
        return array_values($monthlyData);
    }

    /**
     * Get yearly chart data for last 5 years
     */
    private function getYearlyChartData($transaksiModel, $userModel, $pic_input)
    {
        $currentYear = date('Y');
        $startYear = $currentYear - 4; // Last 5 years
        
        $builder = $transaksiModel->select("
            YEAR(wkt_input) as year,
            COUNT(*) as total_transactions,
            SUM(CASE WHEN is_proses_tol = 1 THEN 1 ELSE 0 END) as completed_transactions
        ");
        
        $this->applyRoleBasedFiltering($builder, $userModel, $pic_input);
        
        $builder->where('YEAR(wkt_input) >=', $startYear)
                ->where('YEAR(wkt_input) <=', $currentYear)
                ->groupBy('YEAR(wkt_input)')
                ->orderBy('YEAR(wkt_input)', 'ASC');
        
        $results = $builder->get()->getResultArray();
        
        // Initialize data for all years
        $yearlyData = [];
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $yearlyData[$year] = [
                'year' => $year,
                'total_transactions' => 0,
                'completed_transactions' => 0
            ];
        }
        
        // Fill with actual data
        foreach ($results as $row) {
            $yearlyData[$row['year']] = [
                'year' => $row['year'],
                'total_transactions' => (int)$row['total_transactions'],
                'completed_transactions' => (int)$row['completed_transactions']
            ];
        }
        
        return array_values($yearlyData);
    }

    /**
     * Apply role-based access control filtering (same as TransaksiController)
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
        } else {
            // Default: only show own transactions
            $builder->where('pic_input', $pic_input);
        }
    }

    /**
     * Apply Store Pic specific filtering based on group membership
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
            log_message('error', '[Dashboard Store Pic Filtering] Database error: ' . $e->getMessage());
            // On error, fallback to own transactions only
            $builder->where('pic_input', $pic_input);
        }
    }
}