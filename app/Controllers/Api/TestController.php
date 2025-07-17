<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\AuditLogService;

class TestController extends BaseController
{
    public function testAuditLog()
    {
        try {
            $auditService = new AuditLogService();
            
            // Test logging
            $success = $auditService->logAction(
                1, // user_id (assuming user with ID 1 exists)
                'TEST_ACTION',
                'test_resource',
                null,
                null,
                [
                    'test_data' => 'This is a test audit log',
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            );
            
            if ($success) {
                // Get recent audit logs to verify
                $logs = $auditService->getAuditLogs([], 1, 5);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Audit log test successful',
                    'recent_logs' => $logs['logs'],
                    'total_logs' => $logs['total']
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => 'Failed to create audit log'
                ]);
            }
            
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error testing audit log: ' . $e->getMessage()
            ]);
        }
    }
    
    public function checkTables()
    {
        try {
            $db = \Config\Database::connect();
            
            // Check if audit_logs table exists
            $tables = $db->listTables();
            $auditTableExists = in_array('audit_logs', $tables);
            
            // Get table structure if exists
            $structure = null;
            $sampleData = null;
            
            if ($auditTableExists) {
                $structure = $db->getFieldData('audit_logs');
                $sampleData = $db->table('audit_logs')
                    ->orderBy('created_at', 'DESC')
                    ->limit(3)
                    ->get()
                    ->getResultArray();
            }
            
            return $this->response->setJSON([
                'success' => true,
                'audit_table_exists' => $auditTableExists,
                'all_tables' => $tables,
                'table_structure' => $structure,
                'sample_data' => $sampleData
            ]);
            
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error checking tables: ' . $e->getMessage()
            ]);
        }
    }
}
