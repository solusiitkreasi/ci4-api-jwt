<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuditLogTestSeeder extends Seeder
{
    public function run()
    {
        // Create test audit log entries
        $data = [
            [
                'user_id' => 1,
                'action' => 'SEEDER_TEST',
                'resource' => 'audit_system',
                'resource_id' => null,
                'old_data' => null,
                'new_data' => json_encode([
                    'test_message' => 'Migration and seeder test successful',
                    'created_by' => 'seeder'
                ]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'CodeIgniter Seeder',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'user_id' => 1,
                'action' => 'MIGRATION_COMPLETE',
                'resource' => 'database',
                'resource_id' => null,
                'old_data' => null,
                'new_data' => json_encode([
                    'table_created' => 'audit_logs',
                    'timestamp' => date('Y-m-d H:i:s')
                ]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Migration System',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 minute'))
            ]
        ];

        $this->db->table('audit_logs')->insertBatch($data);
        
        echo "Audit log test data seeded successfully!\n";
        echo "Added " . count($data) . " test audit log entries.\n";
    }
}
