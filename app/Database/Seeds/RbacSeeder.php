<?php 

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class RbacSeeder extends Seeder
{
    public function run()
    {
        $roleModel = new \App\Models\RoleModel();
        $permissionModel = new \App\Models\PermissionModel();
        $userModel = new \App\Models\UserModel();

        // 1. Definisikan Roles
        $roles = [
            ['name' => 'Super Admin', 'description' => 'Memiliki semua hak akses'],
            ['name' => 'Client', 'description' => 'Pengguna terdaftar standar'],
            // Tambahkan peran lain jika perlu, misal 'Editor', 'Support Staff'
        ];
        foreach ($roles as $role) {
            $roleModel->insert($role);
        }
        $superAdminRole = $roleModel->where('name', 'Super Admin')->first();
        $clientRole = $roleModel->where('name', 'Client')->first();

        // 2. Definisikan Permissions (slug harus unik dan deskriptif)
        $permissions = [
            // User Management
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'description' => 'CRUD semua user'],
            ['name' => 'View Users', 'slug' => 'view-users', 'description' => 'Melihat daftar user'],
            // Role & Permission Management
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'description' => 'CRUD roles dan assignment permission ke role'],
            ['name' => 'Manage Permissions', 'slug' => 'manage-permissions', 'description' => 'CRUD permissions'],
            // Product Management
            ['name' => 'Manage Products', 'slug' => 'manage-products', 'description' => 'CRUD produk'],
            ['name' => 'View Products', 'slug' => 'view-products', 'description' => 'Melihat daftar produk (publik)'],
            // Category Management
            ['name' => 'Manage Categories', 'slug' => 'manage-categories', 'description' => 'CRUD kategori'],
            ['name' => 'View Categories', 'slug' => 'view-categories', 'description' => 'Melihat daftar kategori (publik)'],
            // Transaction Management
            ['name' => 'Create Transaction', 'slug' => 'create-transaction', 'description' => 'Membuat transaksi baru (client)'],
            ['name' => 'View Own Transactions', 'slug' => 'view-own-transactions', 'description' => 'Melihat transaksi milik sendiri (client)'],
            ['name' => 'Manage All Transactions', 'slug' => 'manage-all-transactions', 'description' => 'Melihat dan mengubah status semua transaksi (admin)'],
            // API Key Management
            ['name' => 'Manage API Keys', 'slug' => 'manage-api-keys', 'description' => 'CRUD API Keys untuk aplikasi eksternal'],
            // Profile Management
            ['name' => 'View Own Profile', 'slug' => 'view-own-profile', 'description' => 'Melihat profil sendiri'],
        ];
        $allPermissionIds = [];
        foreach ($permissions as $perm) {
            $permissionId = $permissionModel->insert($perm);
            $allPermissionIds[] = $permissionId;
        }

        // 3. Assign Permissions to Roles
        // Super Admin mendapatkan semua permission
        if ($superAdminRole) {
            $roleModel->assignPermissions($superAdminRole['id'], $allPermissionIds);
        }

        // Client mendapatkan permission tertentu
        if ($clientRole) {
            $clientPermissionSlugs = [
                'view-products', 'view-categories', 'create-transaction', 
                'view-own-transactions', 'view-own-profile'
            ];
            $clientPermissionIds = [];
            foreach ($clientPermissionSlugs as $slug) {
                $p = $permissionModel->where('slug', $slug)->first();
                if ($p) $clientPermissionIds[] = $p['id'];
            }
            $roleModel->assignPermissions($clientRole['id'], $clientPermissionIds);
        }

        // 4. Assign Default Roles to Existing Users (Contoh: semua user lama jadi Client, user admin jadi Super Admin)
        $adminUser = $userModel->where('email', 'admin@example.com')->first();
        if ($adminUser && $superAdminRole) {
            $userModel->assignRole($adminUser['id'], $superAdminRole['id']);
        }
        
        $clientUser = $userModel->where('email', 'client@example.com')->first();
        if ($clientUser && $clientRole) {
             // Hapus peran lama jika ada, lalu assign yang baru
            $userModel->assignRoles($clientUser['id'], [$clientRole['id']]);
        }
        // Anda mungkin perlu loop semua user dan assign default role 'Client'
        // $allUsers = $userModel->findAll();
        // foreach ($allUsers as $user) {
        //     if ($user['email'] !== 'admin@example.com') { // Contoh
        //         $existingRoles = $userModel->getRoles($user['id']);
        //         if (empty($existingRoles) && $clientRole) { // Hanya jika belum punya role
        //             $userModel->assignRole($user['id'], $clientRole['id']);
        //         }
        //     }
        // }
    }
}