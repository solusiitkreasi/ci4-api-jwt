<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UpdateDashboardLinkSeeder extends Seeder
{
    public function run()
    {
        // Update dashboard link to include 'backend/' prefix
        $this->db->table('permissions')
            ->where('slug', 'dashboard')
            ->update([
                'link' => 'backend/dashboard',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        echo "Dashboard link updated successfully!\n";
        echo "Dashboard menu now points to 'backend/dashboard'\n";
    }
}
