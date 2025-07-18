<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JwtDemoSeeder extends Seeder
{
    public function run()
    {
        // Cek apakah user demo sudah ada
        $user = $this->db->table('users')->where('email', 'demo@example.com')->get()->getRow();
        if (!$user) {
            $this->db->table('users')->insert([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $userId = $this->db->insertID();
        } else {
            $userId = $user->id;
        }

        // Contoh refresh token
        $this->db->table('jwt_refresh_tokens')->insert([
            'user_id' => $userId,
            'refresh_token' => bin2hex(random_bytes(40)),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'revoked' => false,
        ]);
    }
}
