<?php
namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Services\AuditLogService;

class LogActivityController extends BaseController
{
    public function index()
    {
        $user = session('user');
        if (!$user || $user['role'] !== 'Super Admin') {
            return redirect()->to('/backend/dashboard')->with('error', 'Akses ditolak.');
        }

        $auditService = new AuditLogService();
        $logs = $auditService->getAuditLogs([], 1, 100);

        return view('backend/pages/log_activity', [
            'logs' => $logs
        ]);
    }
}
