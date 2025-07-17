<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class TestController extends Controller
{
    public function checkSuperAdminPermissions()
    {
        $db = \Config\Database::connect();
        
        // Check Super Admin permissions
        $superAdminPerms = $db->table('role_permissions rp')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', 1)
            ->select('p.id, p.slug, p.link, p.description')
            ->get()
            ->getResultArray();
            
        echo "<h3>Super Admin Permissions:</h3>";
        echo "<p>Total: " . count($superAdminPerms) . "</p>";
        
        foreach ($superAdminPerms as $perm) {
            echo "- ID: {$perm['id']}, Slug: {$perm['slug']}, Link: {$perm['link']}, Description: {$perm['description']}<br>";
        }
        
        // Check specifically dashboard permission
        $dashboardPerm = $db->table('role_permissions rp')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', 1)
            ->where('p.slug', 'dashboard')
            ->countAllResults();
            
        echo "<h3>Dashboard Permission Check:</h3>";
        echo "Super Admin has dashboard permission: " . ($dashboardPerm > 0 ? 'YES' : 'NO');
    }
}
