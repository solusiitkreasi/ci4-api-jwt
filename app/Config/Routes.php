<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index'); // Halaman default CI4, bisa dihapus/diubah

$routes->group('api/v1', function ($routes) {

    // Auth (Login, Register, Reset Password) - Tidak perlu filter auth di sini
    $routes->group('auth', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        $routes->post('register', 'AuthController::register');
        $routes->post('login', 'AuthController::login');
        $routes->post('forgot-password', 'AuthController::forgotPassword');
        $routes->post('reset-password', 'AuthController::resetPassword'); // Biasanya ada token di URL atau body
        $routes->post('logout', 'AuthController::logout', ['filter' => 'jwtAuth']);
    });

    // Public Data (Produk & Kategori) - Menggunakan API Key
    $routes->group('public', ['namespace' => 'App\Controllers\Api', 'filter' => 'apiKeyAuth'], function ($routes) {
        $routes->get('categories', 'PublicController::getCategories');
        $routes->get('categories/(:segment)', 'PublicController::getCategory/$1');
        $routes->get('products', 'PublicController::getProducts');
        $routes->get('products/(:segment)', 'PublicController::getProduct/$1');

        // Contoh permission API Key
        // $routes->get('products', 'PublicController::getProducts', ['filter' => 'apiKeyAuth:read_products']); 
    });

    // Client Area - Membutuhkan JWT dan permission yang sesuai
    $routes->group('client', ['namespace' => 'App\Controllers\Api\Client', 'filter' => 'jwtAuth'], function ($routes) {
        
        $routes->get('profile', 'ClientController::profile'); // view-own-profile

        //  Data Master 
            $routes->get('kodebrand', 'MasterController::getKodeBrand'); //, ['filter' => 'permission:manage-master']

            $routes->get('grouplensa', 'MasterController::getGroupLensa');

            $routes->get('lensa', 'MasterController::getLensa');

            $routes->get('spheris', 'MasterController::getSpheris');

            $routes->get('cylinder', 'MasterController::getCylinder');

            $routes->get('axis', 'MasterController::getAxis');

            $routes->get('additional', 'MasterController::getAdditional');

            $routes->get('model', 'MasterController::getModel');

            $routes->get('jasa', 'MasterController::getJasa');

            $routes->get('base', 'MasterController::getBase');

            $routes->get('prisma', 'MasterController::getPrisma');

            $routes->get('framestatus', 'MasterController::getFrameStatus');
            
            $routes->get('framejenis', 'MasterController::getFrameJenis');
        // End Data Master

        
        // Data Transaksi
            $routes->post('transaksi', 'TransaksiController::createTrn');

            $routes->get('transaksi', 'TransaksiController::listTrn');
            
            $routes->get('transaksi/(:segment)', 'TransaksiController::getTrnDetail/$1');
        // End Transaksi


        // Tanpa Permission
        $routes->post('transactions', 'ClientController::createTransaction');
        $routes->get('transactions', 'ClientController::listTransactions');
        $routes->get('transactions/(:segment)', 'ClientController::getTransactionDetail/$1');
        
        // Dengan Permission
        $routes->post('transactions', 'ClientController::createTransaction', ['filter' => ['jwtAuth', 'permission:create-transaction']]);
        $routes->get('transactions', 'ClientController::listTransactions', ['filter' => ['jwtAuth', 'permission:view-own-transactions']]);
        $routes->get('transactions/(:segment)', 'ClientController::getTransactionDetail/$1', ['filter' => ['jwtAuth', 'permission:view-own-transactions']]);

        // Tambahkan endpoint client lainnya
    });


    // Admin Area - Membutuhkan JWT dan permission yang sesuai
    $routes->group('admin', ['namespace' => 'App\Controllers\Api\Admin', 'filter' => 'jwtAuth'], function ($routes) {
        
        // Role Management
        $routes->group('roles', ['filter' => 'permission:manage-roles'], function ($routes) {
            $routes->get('/', 'RoleController::index');
            $routes->post('/', 'RoleController::create');
            $routes->get('(:num)', 'RoleController::show/$1');
            $routes->put('(:num)', 'RoleController::update/$1');
            $routes->delete('(:num)', 'RoleController::delete/$1');
            $routes->get('(:num)/permissions', 'RoleController::getPermissions/$1');
            $routes->post('(:num)/permissions', 'RoleController::assignPermissions/$1');
        });

        // Permission Management
        $routes->group('permissions', ['filter' => 'permission:manage-permissions'], function ($routes) {
            $routes->get('/', 'PermissionController::index');
            $routes->post('/', 'PermissionController::create');
            $routes->get('(:num)', 'PermissionController::show/$1');
            $routes->put('(:num)', 'PermissionController::update/$1');
            $routes->delete('(:num)', 'PermissionController::delete/$1');
        });
        
        // User Management (asumsi di UserController atau Admin\UserController)
        // Ganti 'AdminController' dengan 'UserController' jika Anda memindahkannya ke App\Controllers\Admin\UserController
        $controllerUser = 'UserController'; // 'App\Controllers\AdminController' atau '\App\Controllers\Admin\UserController'
        
        $routes->get('users', "{$controllerUser}::getUsers", ['filter' => 'permission:view-users']);
        $routes->post('users', "{$controllerUser}::createUser", ['filter' => 'permission:manage-users']);
        $routes->put('users/(:num)', "{$controllerUser}::updateUser/$1", ['filter' => 'permission:manage-users']);
        // $routes->delete('users/(:num)', "{$controllerUser}::deleteUser/$1", ['filter' => 'permission:manage-users']); // Jika ada delete
        $routes->get('users/(:num)/roles', "{$controllerUser}::getUserRoles/$1", ['filter' => 'permission:manage-users']); // atau manage-roles
        $routes->post('users/(:num)/roles', "{$controllerUser}::assignRolesToUser/$1", ['filter' => 'permission:manage-users']); // atau manage-roles

        // Category Management
        $routes->get('categories', "{$controllerUser}::getCategories", ['filter' => 'permission:view-categories']); // Admin bisa lihat semua
        $routes->post('categories', "{$controllerUser}::createCategory", ['filter' => 'permission:manage-categories']);
        $routes->put('categories/(:num)', "{$controllerUser}::updateCategory/$1", ['filter' => 'permission:manage-categories']);
        $routes->delete('categories/(:num)', "{$controllerUser}::deleteCategory/$1", ['filter' => 'permission:manage-categories']);

        // Product Management
        $routes->get('products', "{$controllerUser}::getProducts", ['filter' => 'permission:view-products']); // Admin bisa lihat semua
        $routes->post('products', "{$controllerUser}::createProduct", ['filter' => 'permission:manage-products']);
        $routes->get('products/(:num)', "{$controllerUser}::getProductById/$1", ['filter' => 'permission:view-products']);
        $routes->put('products/(:num)', "{$controllerUser}::updateProduct/$1", ['filter' => 'permission:manage-products']);
        $routes->delete('products/(:num)', "{$controllerUser}::deleteProduct/$1", ['filter' => 'permission:manage-products']);
        
        // API Key Management
        $routes->group('apikeys', ['filter' => 'permission:manage-api-keys'], function ($routes) {
            // $routes->get('/', "{$controllerUser}::getApiKeys" );
            $routes->get('/', 'UserController::getApiKeys');
            $routes->post('/', 'UserController::createApiKey');
            $routes->put('/(:num)', 'UserController::updateApiKey/$1');
            $routes->delete('/(:num)', 'UserController::deleteApiKey/$1');
        });

        // API Key Management
        // $routes->get('apikeys', 'AdminController::getApiKeys');
        // $routes->post('apikeys', 'AdminController::createApiKey');
        // $routes->put('apikeys/(:num)', 'AdminController::updateApiKey/$1');
        // $routes->delete('apikeys/(:num)', 'AdminController::deleteApiKey/$1');



        // $routes->get('apikeys', "{$controllerUser}::getApiKeys", ['filter' => 'permission:manage-api-keys']);
        // // ... CRUD API Keys lainnya dengan permission:manage-api-keys ...
        // $routes->post('apikeys', "{$controllerUser}::createApiKey" , ['filter' => 'permission:manage-api-keys']);
        // $routes->put('apikeys/(:num)', "{$controllerUser}::updateApiKey/$1" , ['filter' => 'permission:manage-api-keys']);
        // $routes->delete('apikeys/(:num)', "{$controllerUser}::deleteApiKey/$1" , ['filter' => 'permission:manage-api-keys']);

        // Transaction Management
        $routes->get('transactions', "{$controllerUser}::getAllTransactions", ['filter' => 'permission:manage-all-transactions']);
        // ... Detail dan update status transaksi lainnya dengan permission:manage-all-transactions ...
        $routes->get('transactions/(:segment)', "{$controllerUser}::getAdminTransactionDetail/$1", ['filter' => 'permission:manage-all-transactions']);
        $routes->put('transactions/(:segment)/status', "{$controllerUser}::updateTransactionStatus/$1", ['filter' => 'permission:manage-all-transactions']);


        $routes->get('karyawans', 'UserController::getKaryawan');
    });


});



// =================================================================
// WEB ROUTES (STATEFUL - SESSION AUTH)
// =================================================================

    // Auth untuk Web (Login Form, Logout)
    // Gunakan namespace agar rapi
    // $routes->group('', ['namespace' => 'App\Controllers\Web'], function ($routes) {
        // $routes->get('login', 'Web\AuthController::loginForm', ['as' => 'web.login.form']);
        // $routes->post('login', 'Web\AuthController::attemptLogin', ['as' => 'web.login.attempt']);
        // $routes->get('logout', 'Web\AuthController::logout', ['as' => 'web.logout']);
    // });

    // Grup untuk Dashboard Admin
    // Memerlukan login dan peran 'admin'
    $routes->group('admin', ['namespace' => 'App\Controllers\Web\Admin', 'filter' => 'webAuth:Super Admin'], function ($routes) {
        $routes->get('/', 'DashboardController::index', ['as' => 'admin.dashboard']);
        $routes->get('products', 'TransaksiController::index', ['as' => 'admin.products']);
        // ... Tambahkan rute admin lainnya (misal: untuk CRUD user, roles, dll)
    });

    // Grup untuk Dashboard Client
    // Memerlukan login dan peran 'client'
    $routes->group('client', ['namespace' => 'App\Controllers\Web\Client', 'filter' => 'webAuth:Client'], function ($routes) {
        $routes->get('/', 'DashboardController::index', ['as' => 'client.dashboard']);
        // ... Tambahkan rute client lainnya (misal: melihat detail transaksi)
    });

    // Halaman utama, jika sudah login akan diarahkan
    $routes->get('/direct_login', 'Web\DashboardRedirectController::index', ['filter' => 'webAuth']);





// Rute untuk login/logout admin (tidak perlu filter auth)
$routes->get('/login', 'Backend\AuthController::loginView');
$routes->post('/login', 'Backend\AuthController::loginAction');
$routes->get('/logout', 'Backend\AuthController::logout');

// Grup untuk semua halaman admin yang terproteksi
$routes->group('backend', ['namespace' => 'App\Controllers\Backend', 'filter' => 'adminAuth'], function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');

    // Rute untuk manajemen transaksi
    $routes->get('transaksi', 'TransaksiController::index');
    $routes->get('transaksi/detail/(:any)', 'TransaksiController::detail/$1');
    $routes->post('transaksi/update_status', 'TransaksiController::updateStatus');
    
    // Rute untuk manajemen pengguna
    $routes->get('users', 'UserController::index');

});






// $routes->set404Override(function(){
//     // Muat helper response kita yang sudah ada
//     helper('response');
    
//     // Gunakan helper api_error() untuk membuat objek Response yang konsisten
//     $response = api_error('Endpoint Not Found', \CodeIgniter\HTTP\ResponseInterface::HTTP_NOT_FOUND, [
//         'route' => 'The requested resource or endpoint does not exist.'
//     ]);
    
//     // Kirim respons (header dan body) secara manual ke browser
//     $response->send();
    
//     // Hentikan eksekusi skrip agar CodeIgniter tidak mencoba memproses lebih lanjut
//     exit();
// });


// Fallback untuk route tidak ditemukan (404 Not Found)
$routes->set404Override('App\Controllers\CustomErrors::show404');