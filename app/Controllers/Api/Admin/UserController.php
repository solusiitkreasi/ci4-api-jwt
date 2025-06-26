<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\BaseController;
use Config\Services; // untuk JWT
use CodeIgniter\API\ResponseTrait;

use App\Models\ApiKeyModel;
use App\Models\TransactionModel; // Untuk melihat transaksi
use App\Models\LogHistoryTransaksiModel;
use App\Models\UserModel;
use App\Models\RoleModel;

use App\Models\CategoryModel;
use App\Models\ProductModel;

//Test Get Karyawan
use App\Models\KaryawanModel;

class UserController extends BaseController
{
    use ResponseTrait;
    protected $categoryModel;
    protected $productModel;
    protected $apiKeyModel;
    protected $transactionModel;
    protected $logHistoryModel;
    protected $userModel;
    protected $roleModel;
    protected $karyawanModel;
    protected $currentUser;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        $this->apiKeyModel = new ApiKeyModel();
        $this->transactionModel = new TransactionModel();
        $this->logHistoryModel = new LogHistoryTransaksiModel();
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->karyawanModel = new KaryawanModel();

        helper(['response', 'text']);
        $this->currentUser = service('request')->user ?? Services::getSharedInstance('user');
        $this->validator = \Config\Services::validation(); // Jika belum di BaseController
    }

    // --- Role & Permission Management ---

        public function getUserRoles($userId)
        {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return api_error('User not found', ResponseInterface::HTTP_NOT_FOUND);
            }
            $roles = $this->userModel->getRoles($userId);
            $allRoles = $this->roleModel->orderBy('name', 'ASC')->findAll();
            return api_response(['assigned' => $roles, 'all' => $allRoles], 'Roles for user fetched');
        }

        public function assignRolesToUser($userId)
        {
            $user = $this->userModel->find($userId);
            if (!$user) {
                return api_error('User not found', ResponseInterface::HTTP_NOT_FOUND);
            }

            $roleIds = $this->request->getJSONVar('role_ids'); // Array of role IDs
            if (!is_array($roleIds)) {
                return api_error('Invalid input: role_ids must be an array.', ResponseInterface::HTTP_BAD_REQUEST);
            }
            
            // Validasi apakah semua roleId ada
            foreach($roleIds as $rId) {
                if(!$this->roleModel->find($rId)) {
                    return api_error("Role with ID {$rId} not found.", ResponseInterface::HTTP_BAD_REQUEST);
                }
            }

            if ($this->userModel->assignRoles($userId, $roleIds)) {
                // Ambil ulang data user dengan roles & permissions baru untuk dimasukkan ke JWT jika login ulang
                // Untuk saat ini, cukup response sukses
                return api_response(null, 'Roles assigned to user successfully');
            }
            return api_error('Failed to assign roles to user', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

    // --- End Role & Permission Management ---

    // --- Category Management ---
        public function getCategories()
        {
            $categories = $this->categoryModel->findAll();
            return api_response($categories, 'Categories fetched for admin');
        }

        public function createCategory()
        {
            $rules = [
                'name' => 'required|min_length[3]|max_length[100]|is_unique[categories.name]',
                'slug' => 'required|min_length[3]|max_length[120]|is_unique[categories.slug]|alpha_dash'
            ];

            if (!$this->validate($rules)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }

            $data = [
                'name' => $this->request->getVar('name'),
                'slug' => $this->request->getVar('slug'),
            ];

            $id = $this->categoryModel->insert($data);
            if (!$id) {
                return api_error('Failed to create category', 500, $this->categoryModel->errors());
            }
            $newCategory = $this->categoryModel->find($id);
            return api_response($newCategory, 'Category created successfully', 201);
        }

        public function updateCategory($id)
        {
            $category = $this->categoryModel->find($id);
            if (!$category) {
                return api_error('Category not found', 404);
            }

            $rules = [ // Perhatikan validasi is_unique jika nilai tidak berubah
                'name' => "required|min_length[3]|max_length[100]|is_unique[categories.name,id,{$id}]",
                'slug' => "required|min_length[3]|max_length[120]|is_unique[categories.slug,id,{$id}]|alpha_dash"
            ];

            if (!$this->validate($rules)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }
            
            $data = $this->request->getJSON(true); // Ambil data JSON dari body
            if (empty($data)) { // Fallback ke getVar jika bukan JSON
                $data = [
                    'name' => $this->request->getVar('name'),
                    'slug' => $this->request->getVar('slug'),
                ];
            }


            if ($this->categoryModel->update($id, $data) === false) {
                return api_error('Failed to update category', 500, $this->categoryModel->errors());
            }
            $updatedCategory = $this->categoryModel->find($id);
            return api_response($updatedCategory, 'Category updated successfully');
        }

        public function deleteCategory($id)
        {
            $category = $this->categoryModel->find($id);
            if (!$category) {
                return api_error('Category not found', 404);
            }

            // Cek apakah ada produk yang menggunakan kategori ini
            $productCount = $this->productModel->where('category_id', $id)->countAllResults();
            if ($productCount > 0) {
                return api_error('Cannot delete category. It is currently in use by products.', 409); // 409 Conflict
            }

            if ($this->categoryModel->delete($id) === false) {
                return api_error('Failed to delete category', 500, $this->categoryModel->errors());
            }
            return api_response(null, 'Category deleted successfully');
        }
    // --- End Category Management ---

    // --- API Key Management ---
        public function getApiKeys()
        {
            $apiKeys = $this->apiKeyModel->findAll();
            return api_response($apiKeys, 'API Keys fetched');
        }

        public function createApiKey()
        {
            $rules = [
                'client_name' => 'required|min_length[3]',
                'permissions' => 'permit_empty|valid_json', // e.g., ["read_products", "read_categories"]
                'status' => 'permit_empty|in_list[active,inactive]'
            ];
            if (!$this->validate($rules)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }

            $data = [
                'client_name' => $this->request->getVar('client_name'),
                'key_value' => bin2hex(random_bytes(32)), // Buat kunci unik
                'permissions' => $this->request->getVar('permissions') ?: json_encode([]),
                'status' => $this->request->getVar('status') ?: 'active',
            ];
            
            $id = $this->apiKeyModel->insert($data);
            if (!$id) {
                return api_error('Failed to create API Key', 500, $this->apiKeyModel->errors());
            }
            $newKey = $this->apiKeyModel->find($id);
            return api_response($newKey, 'API Key created successfully', 201);
        }
        
        public function updateApiKey($id)
        {
            $apiKey = $this->apiKeyModel->find($id);
            if (!$apiKey) {
                return api_error('API Key not found', 404);
            }

            $rules = [
                'client_name' => 'if_exist|required|min_length[3]',
                'permissions' => 'if_exist|permit_empty|valid_json',
                'status' => 'if_exist|in_list[active,inactive]'
            ];
            if (!$this->validate($rules)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }
            
            $inputData = $this->request->getJSON(true) ?: $this->request->getRawInput();

            $updateData = [];
            if (isset($inputData['client_name'])) $updateData['client_name'] = $inputData['client_name'];
            if (isset($inputData['permissions'])) $updateData['permissions'] = $inputData['permissions']; // Pastikan ini string JSON valid
            if (isset($inputData['status'])) $updateData['status'] = $inputData['status'];

            if (empty($updateData)) {
                return api_error('No data provided for update', 400);
            }

            if ($this->apiKeyModel->update($id, $updateData) === false) {
                return api_error('Failed to update API Key', 500, $this->apiKeyModel->errors());
            }
            $updatedKey = $this->apiKeyModel->find($id);
            return api_response($updatedKey, 'API Key updated successfully');
        }

        public function deleteApiKey($id)
        {
            $apiKey = $this->apiKeyModel->find($id);
            if (!$apiKey) {
                return api_error('API Key not found', 404);
            }
            if ($this->apiKeyModel->delete($id) === false) {
                return api_error('Failed to delete API Key', 500, $this->apiKeyModel->errors());
            }
            return api_response(null, 'API Key deleted successfully');
        }
    // --- End API Key Management ---

    // --- Transaction Management by Admin ---
        public function getAllTransactions()
        {
            // Dengan pagination
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('perPage') ?? 10;

            $transactions = $this->transactionModel
                ->select('transactions.*, users.name as user_name, users.email as user_email')
                ->join('users', 'users.id = transactions.user_id')
                ->orderBy('transactions.transaction_date', 'DESC')
                ->paginate($perPage, 'default', $page);
            
            $pager = $this->transactionModel->pager;
            $data = [
                'transactions' => $transactions,
                'pagination' => [
                    'total' => $pager->getTotal(),
                    'perPage' => $pager->getPerPage(),
                    'currentPage' => $pager->getCurrentPage(),
                    'lastPage' => $pager->getLastPage(),
                ]
            ];
            return api_response($data, 'All transactions fetched');
        }

        public function getAdminTransactionDetail($transactionCode)
        {
            $transaction = $this->transactionModel
                ->select('transactions.*, users.name as user_name, users.email as user_email')
                ->join('users', 'users.id = transactions.user_id')
                ->where('transactions.transaction_code', $transactionCode)
                ->first();

            if (!$transaction) {
                return api_error('Transaction not found.', 404);
            }

            // Mirip dengan ClientController, tapi tanpa filter user_id
            $transaction['details'] = model('TransactionDetailModel')
                ->select('transaction_details.*, products.name as product_name')
                ->join('products', 'products.id = transaction_details.product_id')
                ->where('transaction_id', $transaction['id'])->findAll();
            
            $transaction['payment'] = model('PaymentModel')->where('transaction_id', $transaction['id'])->first();
            $transaction['logs'] = $this->logHistoryModel->where('transaction_id', $transaction['id'])->orderBy('created_at', 'ASC')->findAll();

            return api_response($transaction, 'Transaction detail fetched by admin');
        }

        public function updateTransactionStatus($transactionCode)
        {
            $transaction = $this->transactionModel->where('transaction_code', $transactionCode)->first();
            if (!$transaction) {
                return api_error('Transaction not found', 404);
            }

            $rules = [
                'status' => 'required|in_list[pending,paid,failed,shipped,completed,cancelled]'
            ];
            if (!$this->validate($rules)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }

            $newStatus = $this->request->getVar('status');
            
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                $this->transactionModel->update($transaction['id'], ['status' => $newStatus]);

                // Catat log perubahan status
                $this->logHistoryModel->insert([
                    'transaction_id' => $transaction['id'],
                    'user_id' => $this->currentUser['id'], // Admin yang mengubah
                    'action' => 'Transaction Status Updated',
                    'details' => json_encode(['old_status' => $transaction['status'], 'new_status' => $newStatus])
                ]);

                // Tambahan logika jika status berubah ke 'cancelled', mungkin kembalikan stok produk
                if ($newStatus === 'cancelled' && $transaction['status'] !== 'cancelled') {
                    $details = model('TransactionDetailModel')->where('transaction_id', $transaction['id'])->findAll();
                    foreach($details as $item) {
                        $this->productModel->set('stock', "stock + {$item['quantity']}", false)->update($item['product_id']);
                    }
                    $this->logHistoryModel->insert([
                        'transaction_id' => $transaction['id'],
                        'user_id' => $this->currentUser['id'],
                        'action' => 'Stock Restored due to Cancellation',
                        'details' => json_encode(['message' => 'Stock for all items in this transaction has been restored.'])
                    ]);
                }


                $db->transCommit();
                $updatedTransaction = $this->transactionModel->find($transaction['id']);
                return api_response($updatedTransaction, 'Transaction status updated successfully.');

            } catch (\Exception $e) {
                $db->transRollback();
                log_message('error', '[ERROR] Update Transaction Status: ' . $e->getMessage());
                return api_error('An error occurred: ' . $e->getMessage(), 500);
            }
        }
    // --- End Transaction Management by Admin ---

    // --- Product Management ---
        public function getProducts()
        {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('perPage') ?? 10;
            $categoryId = $this->request->getGet('category_id');

            // 1. MULAI MEMBANGUN QUERY PADA INSTANCE MODEL
            // Simpan instance model yang sedang dibangun querynya ke dalam variabel jika perlu banyak kondisi
            $productQuery = $this->productModel;

            // 2. PILIH KOLOM YANG DIINGINKAN
            // Penting: Saat join dan ada nama kolom yang sama (misal 'id' atau 'name'),
            // beri alias pada kolom tersebut agar tidak ambigu.
            // 'products.*' akan mengambil semua kolom dari tabel products.
            $productQuery = $productQuery->select('products.*, categories.name as category_name, categories.slug as category_slug');
            
            // 3. JOIN DENGAN TABEL LAIN (jika perlu)
            $productQuery = $productQuery->join('categories', 'categories.id = products.category_id', 'left');

            // 4. TERAPKAN FILTER (jika ada)
            if ($categoryId) {
                $productQuery = $productQuery->where('products.category_id', $categoryId);
            }
            
            // 5. URUTKAN HASIL
            $productQuery = $productQuery->orderBy('products.id', 'DESC');

            // 6. PANGGIL paginate() PADA INSTANCE MODEL (atau objek query model yang sudah dibangun)
            //    Model akan menangani pembuatan query builder internal dan paginasi.
            $products = $productQuery->paginate($perPage, 'default', $page);

            // 7. DAPATKAN INSTANCE PAGER DARI MODEL ($this->productModel->pager)
            //    Ini tetap cara yang benar untuk mendapatkan metadata paginasi.
            $pager = $this->productModel->pager;

            if (empty($products) && $page > 1 && $pager->getTotal() > 0) {
                return api_error('No products found for this page.', 404);
            }

            $data = [
                'products' => $products,
                'pagination' => [
                    'total'       => $pager->getTotal(),
                    'perPage'     => $pager->getPerPage(),
                    'currentPage' => $pager->getCurrentPage(),
                    'lastPage'    => $pager->getLastPage(),
                ]
            ];
            return api_response($data, 'Products fetched for admin');
        }

        public function getProductById(int $id)
        {
            // Gunakan method dari ProductModel yang mengambil produk beserta kategori
            // Jika Anda memiliki method getProductsWithCategory($id) di ProductModel:
            $product = $this->productModel->getProductsWithCategory($id);

            // Alternatif jika Anda tidak memiliki method getProductsWithCategory($id)
            // atau ingin membangun query di sini:
            /*
            $product = $this->productModel
                            ->select('products.*, categories.name as category_name, categories.slug as category_slug')
                            ->join('categories', 'categories.id = products.category_id', 'left')
                            ->find($id);
            */

            if (!$product) {
                return api_error('Product not found', 404);
            }

            return api_response($product, 'Product details fetched successfully');
        }

        public function createProduct()
        {
            $rules = [
                'name'        => 'required|min_length[3]|max_length[150]',
                'category_id' => 'required|is_natural_no_zero|is_not_unique[categories.id]',
                'description' => 'permit_empty|max_length[1000]',
                'price'       => 'required|decimal',
                'stock'       => 'required|integer|greater_than_equal_to[0]',
            ];
            $messages = [
                'category_id' => [
                    'is_not_unique' => 'The selected category does not exist.'
                ]
            ];

            if (!$this->validate($rules, $messages)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }

            $data = [
                'name'        => $this->request->getVar('name'),
                'category_id' => $this->request->getVar('category_id'),
                'description' => $this->request->getVar('description'),
                'price'       => $this->request->getVar('price'),
                'stock'       => $this->request->getVar('stock'),
            ];

            $id = $this->productModel->insert($data);
            if (!$id) {
                return api_error('Failed to create product', 500, $this->productModel->errors());
            }
            $newProduct = $this->productModel->getProductsWithCategory($id); // Mengambil dengan info kategori
            return api_response($newProduct, 'Product created successfully', 201);
        }

        public function updateProduct($id)
        {
            $product = $this->productModel->find($id);
            if (!$product) {
                return api_error('Product not found', 404);
            }

            $rules = [
                'name'        => "if_exist|required|min_length[3]|max_length[150]",
                'category_id' => "if_exist|required|is_natural_no_zero|is_not_unique[categories.id]",
                'description' => 'if_exist|permit_empty|max_length[1000]',
                'price'       => 'if_exist|required|decimal',
                'stock'       => 'if_exist|required|integer|greater_than_equal_to[0]',
            ];
            $messages = [
                'category_id' => [
                    'is_not_unique' => 'The selected category does not exist.'
                ]
            ];

            // Gunakan getJSON untuk PUT/PATCH atau getVar untuk form-data
            $inputData = $this->request->is('json') ? $this->request->getJSON(true) : $this->request->getRawInput();
            if (empty($inputData)) { // Fallback jika tidak ada raw input (misal dari form-data x-www-form-urlencoded)
                $inputData = [];
                if ($this->request->getVar('name')) $inputData['name'] = $this->request->getVar('name');
                if ($this->request->getVar('category_id')) $inputData['category_id'] = $this->request->getVar('category_id');
                if ($this->request->getVar('description') !== null) $inputData['description'] = $this->request->getVar('description'); // allow empty string
                if ($this->request->getVar('price')) $inputData['price'] = $this->request->getVar('price');
                if ($this->request->getVar('stock') !== null) $inputData['stock'] = $this->request->getVar('stock'); // allow 0
            }
            
            // Hanya validasi field yang dikirim
            $validationRulesToApply = [];
            foreach ($rules as $field => $rule) {
                if (array_key_exists($field, $inputData)) {
                    $validationRulesToApply[$field] = $rule;
                }
            }
            
            if (!empty($validationRulesToApply) && !$this->validate($validationRulesToApply, $messages)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }

            if (empty($inputData)) {
                return api_error('No data provided for update', 400);
            }

            if ($this->productModel->update($id, $inputData) === false) {
                return api_error('Failed to update product', 500, $this->productModel->errors());
            }
            $updatedProduct = $this->productModel->getProductsWithCategory($id);
            return api_response($updatedProduct, 'Product updated successfully');
        }

        public function deleteProduct($id)
        {
            $product = $this->productModel->find($id);
            if (!$product) {
                return api_error('Product not found', 404);
            }

            // Opsional: Cek apakah produk ada di transaksi detail yang aktif/belum selesai
            $isInTransaction = model('TransactionDetailModel')->where('product_id', $id)->first();
            if ($isInTransaction) {
                return api_error('Cannot delete product. It is part of one or more transactions. Consider deactivating it instead.', 409); // Conflict
            }

            if ($this->productModel->delete($id) === false) {
                return api_error('Failed to delete product', 500, $this->productModel->errors());
            }
            return api_response(null, 'Product deleted successfully');
        }
    // --- End Product Management ---

    // --- User Management ---
        public function getUsers()
        {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('perPage') ?? 10;

            // Jangan pernah tampilkan password hash
            $users = $this->userModel->select('id, name, email, role, created_at, updated_at') 
                                    ->paginate($perPage, 'default', $page);

            $pager = $this->userModel->pager;
            
            $data = [
                'users' => $users,
                'pagination' => [
                    'total' => $pager->getTotal(),
                    'perPage' => $pager->getPerPage(),
                    'currentPage' => $pager->getCurrentPage(),
                    'lastPage' => $pager->getLastPage(),
                ]
            ];
            return api_response($data, 'Users fetched for admin');
        }

        public function createUser() // Admin membuat user baru
        {
            $rules = [
                'name'     => 'required|min_length[3]|max_length[100]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[8]',
                'role'     => 'required|in_list[admin,client]'
            ];

            if (!$this->validate($rules)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }

            $data = [
                'name'     => $this->request->getVar('name'),
                'email'    => $this->request->getVar('email'),
                'password' => $this->request->getVar('password'), // Akan di-hash oleh UserModel beforeInsert callback
                'role'     => $this->request->getVar('role'),
            ];

            $userId = $this->userModel->insert($data);
            if ($userId === false) {
                return api_error('Failed to create user', 500, $this->userModel->errors());
            }
            
            $newUser = $this->userModel->select('id, name, email, role, created_at')->find($userId);
            return api_response($newUser, 'User created successfully by admin', 201);
        }

        public function updateUser($id) // Admin mengubah data user
        {
            $user = $this->userModel->find($id);
            if (!$user) {
                return api_error('User not found', 404);
            }

            $rules = [
                'name'     => "if_exist|required|min_length[3]|max_length[100]",
                'email'    => "if_exist|required|valid_email|is_unique[users.email,id,{$id}]",
                'password' => 'if_exist|min_length[8]', // Password opsional, jika ada, update
                'role'     => "if_exist|required|in_list[admin,client]"
            ];
            
            $inputData = $this->request->is('json') ? $this->request->getJSON(true) : $this->request->getRawInput();
            if (empty($inputData)) { 
                $inputData = [];
                if ($this->request->getVar('name')) $inputData['name'] = $this->request->getVar('name');
                if ($this->request->getVar('email')) $inputData['email'] = $this->request->getVar('email');
                if ($this->request->getVar('password')) $inputData['password'] = $this->request->getVar('password');
                if ($this->request->getVar('role')) $inputData['role'] = $this->request->getVar('role');
            }
            
            // Hanya validasi field yang dikirim
            $validationRulesToApply = [];
            foreach ($rules as $field => $rule) {
                if (array_key_exists($field, $inputData)) {
                    $validationRulesToApply[$field] = $rule;
                }
            }
            
            if (!empty($validationRulesToApply) && !$this->validate($validationRulesToApply)) {
                return api_error('Validation failed', $this->getResponseStatusCode(), $this->validator->getErrors());
            }
            
            if (empty($inputData)) {
                return api_error('No data provided for update', 400);
            }

            // Password akan di-hash oleh UserModel beforeUpdate callback jika ada di $inputData
            if ($this->userModel->update($id, $inputData) === false) {
                return api_error('Failed to update user', 500, $this->userModel->errors());
            }
            
            $updatedUser = $this->userModel->select('id, name, email, role, updated_at')->find($id);
            return api_response($updatedUser, 'User updated successfully by admin');
        }
    // --- End User Management ---


    // --- Karyawan Management ---
        public function getKaryawan()
        {
            $page = $this->request->getGet('page') ?? 1;
            $perPage = $this->request->getGet('perPage') ?? 10;

            // Jangan pernah tampilkan password hash
            $karyawans = $this->karyawanModel->select('nip, nama_lengkap') 
                                    ->paginate($perPage, 'default', $page);

            $pager = $this->karyawanModel->pager;
            
            $data = [
                'karyawans' => $karyawans,
                'pagination' => [
                    'total'         => $pager->getTotal(),
                    'perPage'       => $pager->getPerPage(),
                    'currentPage'   => $pager->getCurrentPage(),
                    'lastPage'      => $pager->getLastPage(),
                ]
            ];
            return api_response($data, 'Karyawan fetched for admin');
        }
    // --- End Karyawan Management ---


}