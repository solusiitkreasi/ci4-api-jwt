<?php

// Test script untuk audit log functionality
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap CodeIgniter
$paths = require __DIR__ . '/app/Config/Paths.php';
$bootstrap = new \CodeIgniter\Bootstrap();
$app = $bootstrap->initialize($paths);

try {
    // Test database connection
    $db = \Config\Database::connect();
    echo "✓ Database connection successful\n";
    
    // Check if audit_logs table exists
    $tables = $db->listTables();
    $auditTableExists = in_array('audit_logs', $tables);
    
    if ($auditTableExists) {
        echo "✓ audit_logs table exists\n";
        
        // Test AuditLogService
        $auditService = new \App\Services\AuditLogService();
        
        // Create test log
        $success = $auditService->logAction(
            1, // user_id
            'TEST_MIGRATION',
            'audit_system',
            null,
            null,
            [
                'test_message' => 'Migration test successful',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        );
        
        if ($success) {
            echo "✓ Audit log created successfully\n";
            
            // Get recent logs
            $logs = $auditService->getAuditLogs([], 1, 3);
            echo "✓ Found {$logs['total']} total audit logs\n";
            
            if (!empty($logs['logs'])) {
                echo "Latest audit log:\n";
                $latest = $logs['logs'][0];
                echo "  - ID: {$latest['id']}\n";
                echo "  - Action: {$latest['action']}\n";
                echo "  - Resource: {$latest['resource']}\n";
                echo "  - Created: {$latest['created_at']}\n";
            }
        } else {
            echo "✗ Failed to create audit log\n";
        }
        
    } else {
        echo "✗ audit_logs table does not exist\n";
    }
    
} catch (\Throwable $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nTest completed.\n";
