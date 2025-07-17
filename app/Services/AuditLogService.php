<?php

namespace App\Services;

class AuditLogService
{
    protected $db;
    
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Log user actions for audit trail
     */
    public function logAction(
        int $userId,
        string $action,
        string $resource,
        ?int $resourceId = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?string $ipAddress = null
    ): bool {
        try {
            $data = [
                'user_id' => $userId,
                'action' => $action,
                'resource' => $resource,
                'resource_id' => $resourceId,
                'old_data' => $oldData ? json_encode($oldData) : null,
                'new_data' => $newData ? json_encode($newData) : null,
                'ip_address' => $ipAddress ?? service('request')->getIPAddress(),
                'user_agent' => service('request')->getUserAgent()->getAgentString(),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            return $this->db->table('audit_logs')->insert($data);
            
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create audit log: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get audit logs with filtering
     */
    public function getAuditLogs(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $builder = $this->db->table('audit_logs al')
            ->select('al.*, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = al.user_id', 'left');
            
        // Apply filters
        if (!empty($filters['user_id'])) {
            $builder->where('al.user_id', $filters['user_id']);
        }
        
        if (!empty($filters['action'])) {
            $builder->where('al.action', $filters['action']);
        }
        
        if (!empty($filters['resource'])) {
            $builder->where('al.resource', $filters['resource']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('al.created_at >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('al.created_at <=', $filters['date_to']);
        }
        
        // Get total count
        $total = $builder->countAllResults(false);
        
        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $logs = $builder->orderBy('al.created_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();
            
        return [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
}
