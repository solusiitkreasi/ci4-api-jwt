<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ApiDocumentationMenuSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Add API Documentation menu
        $menuData = [
            'name' => 'API Documentation',
            'slug' => 'api-docs',
            'description' => 'API Documentation',
            'menu_on' => '1',
            'parent_id' => 0,
            'sequence' => 10,
            'link' => 'api-docs',
            'icon' => 'bookmark',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $permissionId = $db->table('permissions')->insert($menuData);
        $insertedId = $db->insertID();
        
        // Give permission to Super Admin and Admin roles
        $roles = [1, 7]; // Super Admin and Admin
        
        foreach ($roles as $roleId) {
            $db->table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $insertedId
            ]);
        }
        
        echo "API Documentation menu added successfully!\n";
        echo "Permission ID: " . $insertedId . "\n";
    }
}
