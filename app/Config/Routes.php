<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index'); // Halaman default CI4, bisa dihapus/diubah

// =================================================================
// HEALTH CHECK ROUTES (Monitoring)
// =================================================================
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('health', 'HealthController::index');
    $routes->get('ping', 'HealthController::ping');
});


// =================================================================
// API ROUTES
// =================================================================

$routes->group('api/v1', function ($routes) {

    // Auth (Login, Register, Reset Password) - Tidak perlu filter auth di sini
    $routes->group('auth', ['namespace' => 'App\Controllers\Api'], function ($routes) {
        $routes->post('register', 'AuthController::register');
        $routes->post('login', 'AuthController::login');
        $routes->post('forgot-password', 'AuthController::forgotPassword');
        $routes->post('reset-password', 'AuthController::resetPassword'); // Biasanya ada token di URL atau body

        $routes->post('logout', 'AuthController::logout', ['filter' => 'jwtAuth']);
        $routes->post('refresh', 'AuthController::refreshToken');

        $routes->post('forgot-password-mail', 'AuthController::forgotPasswordMail');
        $routes->post('reset-password-mail', 'AuthController::resetPasswordMail');
        $routes->post('register-mail', 'AuthController::registerMail');
        $routes->get('activate', 'AuthController::activateAccount');
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

        // Transaction Management
        $routes->get('transactions', "{$controllerUser}::getAllTransactions", ['filter' => 'permission:manage-all-transactions']);
        // ... Detail dan update status transaksi lainnya dengan permission:manage-all-transactions ...
        $routes->get('transactions/(:segment)', "{$controllerUser}::getAdminTransactionDetail/$1", ['filter' => 'permission:manage-all-transactions']);
        $routes->put('transactions/(:segment)/status', "{$controllerUser}::updateTransactionStatus/$1", ['filter' => 'permission:manage-all-transactions']);


        $routes->get('karyawans', 'UserController::getKaryawan');
        
        // Audit Log Management
        $routes->group('audit-logs', ['filter' => 'permission:view-audit-logs'], function ($routes) {
            $routes->get('/', 'AuditController::getAuditLogs');
            $routes->get('stats', 'AuditController::getAuditStats');
            $routes->post('test', 'AuditController::testAuditLog');
        });
    });


});

// =================================================================
// WEB ROUTES (STATEFUL - SESSION AUTH)
// =================================================================

// Rute untuk login/logout admin (tidak perlu filter auth)
$routes->get('/login', 'Backend\AuthController::loginView');
$routes->post('/login', 'Backend\AuthController::loginAction');
$routes->get('/logout', 'Backend\AuthController::logout');

$routes->get('/forgot_password', 'Backend\AuthController::forgotView');
$routes->post('/forgot_password', 'Backend\AuthController::forgotAction');
$routes->get('/reset_password', 'Backend\AuthController::resetView');
$routes->post('/reset_password', 'Backend\AuthController::resetAction');

// Halaman dan Proses Registrasi
$routes->get('/register', 'Backend\AuthController::registerView');
$routes->post('/register', 'Backend\AuthController::registerAction');
$routes->get('/activate', 'Backend\AuthController::activateAccount');
$routes->post('/register/get-customer-by-group', 'Backend\AuthController::getCustomerByGroup');

// Session management routes (untuk AJAX calls)
$routes->get('backend/auth/check-session', 'Backend\AuthController::checkSession');
$routes->post('backend/auth/extend-session', 'Backend\AuthController::extendSession');

// Grup untuk semua halaman admin yang terproteksi
$routes->group('backend', ['namespace' => 'App\Controllers\Backend', 'filter' => 'adminAuth'], function ($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/getChartData', 'DashboardController::getChartData');

    // Ganti Password
    $routes->get('change_password', 'AuthController::changePasswordView');
    $routes->post('change_password', 'AuthController::changePasswordAction');

    // Rute untuk manajemen transaksi
    $routes->group('transaksi', function($routes) {
        $routes->get('/', 'TransaksiController::index');
        $routes->get('detail/(:any)', 'TransaksiController::detail/$1');
        $routes->post('update_status', 'TransaksiController::updateStatus');
        $routes->get('datatables', 'TransaksiController::datatables');
        $routes->get('export_csv', 'TransaksiController::exportCsv');
        $routes->get('export_excel', 'TransaksiController::exportExcel');
        $routes->get('create', 'TransaksiController::create');
        $routes->post('create', 'TransaksiController::create');
        
        $routes->get('datalensa', 'LensaController::datatables');
        $routes->get('getspheris', 'LensaController::getspheris');
        $routes->get('getcylinder', 'LensaController::getcylinder');
        $routes->get('getaxis', 'LensaController::getaxis');
        $routes->get('getadditional', 'LensaController::getadditional');
        $routes->get('getbase', 'LensaController::getbase');
        $routes->get('getprisma', 'LensaController::getprisma');

        // $routes->get('getwa', 'LensaController::getwa');

    });
    

    // Role Management
    $routes->group('role', function($routes) {
        $routes->get('/', 'RoleController::index');
        $routes->get('create', 'RoleController::create');
        $routes->post('create', 'RoleController::create');
        $routes->get('edit/(:num)', 'RoleController::edit/$1');
        $routes->post('edit/(:num)', 'RoleController::edit/$1');
        $routes->get('delete/(:num)', 'RoleController::delete/$1');
        $routes->get('permissions/(:num)', 'RoleController::permissions/$1');
        $routes->post('permissions/(:num)', 'RoleController::permissions/$1');
        $routes->get('datatables', 'RoleController::datatables');
    });

    // Permission Management
    $routes->group('permission', function($routes) {
        $routes->get('/', 'PermissionController::index');
        $routes->get('create', 'PermissionController::create');
        $routes->post('create', 'PermissionController::create');
        $routes->get('edit/(:num)', 'PermissionController::edit/$1');
        $routes->post('edit/(:num)', 'PermissionController::edit/$1');
        $routes->get('delete/(:num)', 'PermissionController::delete/$1');
        $routes->get('datatables', 'PermissionController::datatables');
    });

    // Rute untuk manajemen payment gateway
    $routes->group('payment', function($routes) {
        $routes->get('/', 'PaymentGatewayController::index');
        $routes->get('create', 'PaymentGatewayController::create');
        $routes->post('create', 'PaymentGatewayController::store');
        $routes->get('edit/(:num)', 'PaymentGatewayController::edit/$1');
        $routes->post('update/(:num)', 'PaymentGatewayController::update/$1');
        $routes->post('delete/(:num)', 'PaymentGatewayController::delete/$1');
        $routes->post('toggle/(:num)', 'PaymentGatewayController::toggle/$1');
    });

    // Manajemen User
    $routes->group('user', function($routes) {
        $routes->get('/', 'UserController::index');
        $routes->get('datatables', 'UserController::datatables'); // Rute untuk DataTables
        $routes->get('create', 'UserController::create');
        $routes->post('create', 'UserController::store');
        $routes->post('get_stores_group', 'UserController::getStoresByGroup'); // Rute AJAX
        $routes->get('edit/(:num)', 'UserController::edit/$1');
        $routes->post('edit/(:num)', 'UserController::update/$1');
        $routes->get('delete/(:num)', 'UserController::delete/$1');

    });

    
    
    $routes->group('log-activity', 'Backend\LogActivityController::index');
});

// =================================================================
// API ROUTES (STATELESS - JWT/API Key AUTH)
// =================================================================

// Testing routes (temporary)
$routes->group('test', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('audit-log', 'TestController::testAuditLog');
    $routes->get('check-tables', 'TestController::checkTables');
});

// =================================================================
// API DOCUMENTATION ROUTES
// =================================================================
$routes->group('api/docs', function ($routes) {
    $routes->get('/', 'Api\DocumentationController::swaggerUI');
    $routes->get('openapi.json', 'Api\DocumentationController::getOpenAPISpec');
    $routes->get('markdown', 'Api\DocumentationController::markdownDocs');
    $routes->get('postman', 'Api\DocumentationController::postmanCollection');
});

// =================================================================
// Fallback untuk route tidak ditemukan (404 Not Found)
// =================================================================
$routes->set404Override('App\Controllers\CustomErrors::show404');

// Test routes
$routes->get('test/superadmin-permissions', 'TestController::checkSuperAdminPermissions');

// =================================================================

