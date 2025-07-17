<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SuperAdminPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Give Super Admin (role_id = 1) all permissions
        $db = \Config\Database::connect();
        
        // First, clear existing Super Admin permissions
        $db->table('role_permissions')->where('role_id', 1)->delete();
        
        // Get all permission IDs
        $permissions = $db->table('permissions')->select('id')->get()->getResultArray();
        
        $data = [];
        foreach ($permissions as $permission) {
            $data[] = [
                'role_id' => 1,
                'permission_id' => $permission['id']
            ];
        }
        
        // Insert all permissions for Super Admin
        if (!empty($data)) {
            $db->table('role_permissions')->insertBatch($data);
            echo "Super Admin permissions assigned successfully!\n";
            echo "Total permissions assigned: " . count($data) . "\n";
        } else {
            echo "No permissions found to assign.\n";
        }
    }
}
