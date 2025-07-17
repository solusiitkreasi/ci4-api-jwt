<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\BaseController;
use App\Services\AuditLogService;
use App\Services\ErrorHandlerService;
use App\Services\ValidationService;

class AuditController extends BaseController
{
    protected $auditLogService;
    protected $validationService;
    protected $currentUser;
    
    public function __construct()
    {
        $this->auditLogService = new AuditLogService();
        $this->validationService = new ValidationService();
        $this->currentUser = service('request')->user ?? null;
    }
    
    /**
     * Get audit logs with filtering
     * GET /api/admin/audit-logs
     */
    public function getAuditLogs()
    {
        try {
            // Validate pagination
            $paginationParams = $this->request->getGet(['page', 'perPage']);
            $validation = $this->validationService->validatePagination($paginationParams);
            
            if (!$validation['valid']) {
                return ErrorHandlerService::handleValidationError($validation['errors']);
            }
            
            // Get filters
            $filters = $this->request->getGet([
                'user_id', 'action', 'resource', 'date_from', 'date_to'
            ]);
            
            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Get audit logs
            $result = $this->auditLogService->getAuditLogs(
                $filters,
                $validation['page'],
                $validation['perPage']
            );
            
            $meta = [
                'pagination' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'perPage' => $result['perPage'],
                    'totalPages' => $result['totalPages']
                ],
                'filters_applied' => $filters
            ];
            
            return ErrorHandlerService::apiSuccess(
                $result['logs'],
                'Audit logs retrieved successfully',
                200,
                $meta
            );
            
        } catch (\Throwable $e) {
            return ErrorHandlerService::handleDatabaseError($e, 'fetching audit logs');
        }
    }
    
    /**
     * Get audit statistics
     * GET /api/admin/audit-logs/stats
     */
    public function getAuditStats()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get action statistics for last 30 days
            $actionStats = $db->query("
                SELECT 
                    action,
                    COUNT(*) as count,
                    COUNT(DISTINCT user_id) as unique_users
                FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY action
                ORDER BY count DESC
            ")->getResultArray();
            
            // Get resource statistics
            $resourceStats = $db->query("
                SELECT 
                    resource,
                    COUNT(*) as count
                FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY resource
                ORDER BY count DESC
            ")->getResultArray();
            
            // Get daily activity for last 7 days
            $dailyActivity = $db->query("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_actions,
                    COUNT(DISTINCT user_id) as active_users
                FROM audit_logs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ")->getResultArray();
            
            $stats = [
                'action_statistics' => $actionStats,
                'resource_statistics' => $resourceStats,
                'daily_activity' => $dailyActivity,
                'generated_at' => date('Y-m-d H:i:s')
            ];
            
            return ErrorHandlerService::apiSuccess(
                $stats,
                'Audit statistics generated successfully'
            );
            
        } catch (\Throwable $e) {
            return ErrorHandlerService::handleDatabaseError($e, 'generating audit statistics');
        }
    }
    
    /**
     * Test audit logging functionality
     * POST /api/admin/audit-logs/test
     */
    public function testAuditLog()
    {
        try {
            if (!$this->currentUser) {
                return ErrorHandlerService::apiError('User not authenticated', 401);
            }
            
            $testData = [
                'test_field' => 'test_value',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $success = $this->auditLogService->logAction(
                $this->currentUser['id'],
                'TEST',
                'audit_system',
                null,
                null,
                $testData
            );
            
            if ($success) {
                return ErrorHandlerService::apiSuccess(
                    ['logged' => true],
                    'Test audit log created successfully'
                );
            } else {
                return ErrorHandlerService::apiError('Failed to create test audit log', 500);
            }
            
        } catch (\Throwable $e) {
            return ErrorHandlerService::handleDatabaseError($e, 'creating test audit log');
        }
    }
}
