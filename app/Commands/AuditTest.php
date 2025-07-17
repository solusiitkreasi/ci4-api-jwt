<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\AuditLogService;

class AuditTest extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Testing';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'audit:test';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Test audit log functionality';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'audit:test [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Testing Audit Log Functionality...', 'yellow');
        CLI::newLine();
        
        try {
            // Test database connection
            $db = \Config\Database::connect();
            CLI::write('✓ Database connection successful', 'green');
            
            // Check if audit_logs table exists
            $tables = $db->listTables();
            $auditTableExists = in_array('audit_logs', $tables);
            
            if ($auditTableExists) {
                CLI::write('✓ audit_logs table exists', 'green');
                
                // Test AuditLogService
                $auditService = new AuditLogService();
                
                // Create test log
                $success = $auditService->logAction(
                    1, // user_id
                    'CLI_TEST',
                    'audit_system',
                    null,
                    null,
                    [
                        'test_message' => 'CLI test successful',
                        'command' => 'audit:test',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]
                );
                
                if ($success) {
                    CLI::write('✓ Test audit log created successfully', 'green');
                    
                    // Get recent logs
                    $logs = $auditService->getAuditLogs([], 1, 5);
                    CLI::write("✓ Found {$logs['total']} total audit logs in database", 'green');
                    
                    if (!empty($logs['logs'])) {
                        CLI::newLine();
                        CLI::write('Recent audit logs:', 'cyan');
                        CLI::table(['ID', 'User', 'Action', 'Resource', 'Created'], array_map(function($log) {
                            return [
                                $log['id'],
                                $log['user_name'] ?? 'N/A',
                                $log['action'],
                                $log['resource'],
                                $log['created_at']
                            ];
                        }, array_slice($logs['logs'], 0, 3)));
                    }
                    
                    CLI::newLine();
                    CLI::write('Audit log test completed successfully!', 'green');
                } else {
                    CLI::write('✗ Failed to create test audit log', 'red');
                }
                
            } else {
                CLI::write('✗ audit_logs table does not exist', 'red');
                CLI::write('Run: php spark migrate', 'yellow');
            }
            
        } catch (\Throwable $e) {
            CLI::write('✗ Error: ' . $e->getMessage(), 'red');
            CLI::write('File: ' . $e->getFile() . ':' . $e->getLine(), 'yellow');
        }
    }
}
