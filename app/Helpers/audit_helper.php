<?php
if (!function_exists('log_audit')) {
    /**
     * Log aktivitas user ke tabel audit_trails
     *
     * @param int|null $userId
     * @param string $action
     * @param string|null $description
     */
    function log_audit($userId, $action, $description = null)
    {
        $auditModel = new \App\Models\AuditTrailModel();
        $request = service('request');
        $auditModel->insert([
            'user_id'    => $userId,
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
