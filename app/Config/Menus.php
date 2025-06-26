<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Menus extends BaseConfig
{
    /**
     * Mendefinisikan struktur menu sidebar.
     * Kunci array level pertama adalah nama Peran (Role).
     * Setiap item menu memiliki:
     * - title: Teks yang akan ditampilkan.
     * - route: Nama route yang dituju (dari Routes.php).
     * - icon: Kelas ikon (misal: dari Font Awesome).
     * - permission: Slug permission yang dibutuhkan untuk melihat menu ini.
     * Jika kosong atau tidak ada, menu akan selalu tampil untuk peran tersebut.
     */
    public array $menu = [
        // Menu untuk peran 'Super Admin'
        'Super Admin' => [
            [
                'group_title' => null, // Item tanpa grup
                'items' => [
                    [
                        'title' => 'Dashboard',
                        'route' => 'admin.dashboard',
                        'icon'  => 'fas fa-tachometer-alt',
                        'permission' => '' // Semua admin bisa lihat dashboard
                    ],
                ]
            ],
            [
                'group_title' => 'MANAJEMEN KONTEN', // Judul grup menu
                'items' => [
                    [
                        'title' => 'Produk',
                        'route' => 'admin.products',
                        'icon'  => 'fas fa-box-open',
                        'permission' => 'manage-products'
                    ],
                    [
                        'title' => 'Kategori',
                        'route' => 'admin.categories', // Anda perlu membuat route & controller ini
                        'icon'  => 'fas fa-tags',
                        'permission' => 'manage-categories'
                    ],
                ]
            ],
            [
                'group_title' => 'MANAJEMEN PENGGUNA',
                'items' => [
                    [
                        'title' => 'Daftar Pengguna',
                        'route' => 'admin.users', // Anda perlu membuat route & controller ini
                        'icon'  => 'fas fa-users',
                        'permission' => 'view-users'
                    ],
                    [
                        'title' => 'Peran & Izin',
                        'route' => 'admin.roles', // Anda perlu membuat route & controller ini
                        'icon'  => 'fas fa-user-shield',
                        'permission' => 'manage-roles'
                    ],
                ]
            ],
            [
                'group_title' => 'PENGATURAN',
                'items' => [
                    [
                        'title' => 'API Keys',
                        'route' => 'admin.apikeys', // Anda perlu membuat route & controller ini
                        'icon'  => 'fas fa-key',
                        'permission' => 'manage-api-keys'
                    ],
                ]
            ]
        ],

        // Menu untuk peran 'Client'
        'Client' => [
            [
                'group_title' => null,
                'items' => [
                    [
                        'title' => 'Dashboard',
                        'route' => 'client.dashboard',
                        'icon'  => 'fas fa-tachometer-alt',
                        'permission' => '' // Semua client bisa lihat dashboard
                    ],
                ]
            ],
            [
                'group_title' => 'AREA SAYA',
                'items' => [
                    [
                        'title' => 'Transaksi Saya',
                        'route' => 'client.transactions', // Anda perlu membuat route & controller ini
                        'icon'  => 'fas fa-history',
                        'permission' => 'view-own-transactions'
                    ],
                    [
                        'title' => 'Profil Saya',
                        'route' => 'client.profile', // Anda perlu membuat route & controller ini
                        'icon'  => 'fas fa-user-circle',
                        'permission' => 'view-own-profile'
                    ],
                ]
            ]
        ]
    ];
}