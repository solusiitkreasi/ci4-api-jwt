<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuditPermissionsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Check if audit permissions already exist
        $existingPermission = $db->table('permissions')
            ->where('slug', 'view-audit-logs')
            ->get()
            ->getRow();
            
        if ($existingPermission) {
            echo "Audit permissions already exist.\n";
            return;
        }
        
        // Get the highest sequence number in Master group (parent_id = 2)
        $lastSequence = $db->table('permissions')
            ->where('parent_id', 2)
            ->selectMax('sequence')
            ->get()
            ->getRow();
            
        $nextSequence = ($lastSequence->sequence ?? 0) + 1;
        
        // Insert audit logs permissions
        $permissions = [
            [
                'name' => 'Audit Logs',
                'slug' => 'audit-logs',
                'description' => 'Audit Logs Management',
                'menu_on' => 1,
                'parent_id' => 2, // Master group
                'sequence' => $nextSequence,
                'link' => 'audit-logs',
                'icon' => 'shield-check',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'View Audit Logs',
                'slug' => 'view-audit-logs',
                'description' => 'View Audit Logs',
                'menu_on' => 0,
                'parent_id' => 0, // Will be updated after parent is inserted
                'sequence' => 1,
                'link' => 'audit-logs',
                'icon' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'View Audit Statistics',
                'slug' => 'view-audit-stats',
                'description' => 'View Audit Statistics',
                'menu_on' => 0,
                'parent_id' => 0, // Will be updated after parent is inserted
                'sequence' => 2,
                'link' => 'audit-logs/stats',
                'icon' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        // Insert parent permission first
        $parentId = $db->table('permissions')->insert($permissions[0]);
        
        if (!$parentId) {
            echo "Failed to insert parent audit permission.\n";
            return;
        }
        
        echo "Inserted parent audit permission with ID: {$parentId}\n";
        
        // Update child permissions with correct parent_id
        $permissions[1]['parent_id'] = $parentId;
        $permissions[2]['parent_id'] = $parentId;
        
        // Insert child permissions
        foreach (array_slice($permissions, 1) as $permission) {
            $childId = $db->table('permissions')->insert($permission);
            if ($childId) {
                echo "Inserted child audit permission: {$permission['name']} with ID: {$childId}\n";
            } else {
                echo "Failed to insert child audit permission: {$permission['name']}\n";
            }
        }
        
        echo "Audit permissions seeded successfully!\n";
    }
}
