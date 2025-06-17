<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        $this->db->table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => Time::now(),
            'updated_at' => Time::now()
        ]);

        // Client User
        $this->db->table('users')->insert([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'client',
            'created_at' => Time::now(),
            'updated_at' => Time::now()
        ]);

        // API Key
        $this->db->table('api_keys')->insert([
            'client_name' => 'Public Mobile App',
            'key_value' => bin2hex(random_bytes(32)), // Generate random key
            'permissions' => json_encode(['read_products', 'read_categories']),
            'status' => 'active'
        ]);

        // Categories
        $categories = [
            ['name' => 'Elektronik', 'slug' => 'elektronik'],
            ['name' => 'Pakaian', 'slug' => 'pakaian'],
        ];
        $this->db->table('categories')->insertBatch($categories);

        // Products
        $products = [
            [
                'category_id' => 1, // Elektronik
                'name' => 'Laptop Super Canggih',
                'description' => 'Laptop dengan spek dewa.',
                'price' => 15000000.00,
                'stock' => 10
            ],
            [
                'category_id' => 2, // Pakaian
                'name' => 'Kaos Keren Polos',
                'description' => 'Kaos bahan katun adem.',
                'price' => 150000.00,
                'stock' => 50
            ]
        ];
        $this->db->table('products')->insertBatch($products);
    }
}