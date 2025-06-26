<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait; // Untuk response yang lebih mudah
use CodeIgniter\I18n\Time;
use Config\Services; // Untuk mengambil user yang sedang login
use App\Services\JWTService;

use App\Models\UserModel;
use App\Models\RoleModel;


class AuthController extends BaseController
{
    use ResponseTrait; // Menggunakan ResponseTrait CI4

    protected $userModel;
    protected $roleModel;
    protected $jwtService;
    protected $currentUser; // Untuk menyimpan data user yang sedang login

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->jwtService = new JWTService();
        helper(['response', 'form', 'text']); // Muat helper 'text' untuk random_string

        $this->validator = \Config\Services::validation();


        // HINDARI BLOK INI DI CONSTRUCTOR JIKA BERGANTUNG PADA FILTER:
        /*
        // Mengambil user yang sudah diautentikasi oleh JWTAuthFilter jika endpoint memerlukan auth
        // Ini mungkin null jika constructor dipanggil untuk endpoint yang tidak di-filter (seperti login/register)
        $authenticatedUser = service('request')->user;
        if ($authenticatedUser) {
             $this->currentUser = $authenticatedUser; // Ini akan sering null di constructor
        }
        */
    }

    public function register()
    {

        $rules = [
            'name'             => 'required|min_length[3]|max_length[100]',
            'kode_customer'    => 'required|min_length[3]|is_unique[users.kode_customer]',
            'email'            => 'required|valid_email|is_unique[users.email]',
            'password'         => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]',

            // 'role'              => 'permit_empty|in_list[client,admin]' // Default 'client' di DB
        ];
        // Kolom role tidak lagi divalidasi di sini karena dihapus dari input user langsung

        // tesx($rules);

        // if (!$this->validate($rules)) {
        //     return api_error('Validation failed', $this->getResponse()->getStatusCode(), $this->validator->getErrors());
        // }

        $userData  = [
            'kode_customer' => $this->request->getVar('kode_customer'),
            'name'          => $this->request->getVar('name'),
            'email'         => $this->request->getVar('email'),
            'password'      => $this->request->getVar('password'), // Akan di-hash oleh model
            // 'role'          => $this->request->getVar('role') ?: 'client' // Default ke client jika kosong
        ];

        $db = \Config\Database::connect();
        $db->transStart();

            $userId = $this->userModel->insert($userData);

            tesx($this->userModel->errors());

            if ($userId === false) {
                $db->transRollback();
                return api_error('Failed to register user', 400, $this->userModel->errors());
            }

            // Assign default role 'Client'
            $clientRole = $this->roleModel->where('name', 'Client')->first();
            if ($clientRole) {
                $this->userModel->assignRole($userId, $clientRole['id']);
            } else {
                log_message('warning', "Default role 'Client' not found during registration for user ID: {$userId}");
                $db->transRollback();
                // Pertimbangkan apa yang harus dilakukan jika role default tidak ada, mungkin rollback atau log error fatal
            }
        
        $db->transComplete();

        if ($db->transStatus() === false) {
            return api_error('Failed to complete user registration with role assignment.', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $user = $this->userModel->select('id, name, email, created_at')->find($userId); // Password tidak dikirim
        // Anda bisa juga menambahkan roles & permissions user yang baru di-register ke response jika diperlukan
        // $user['roles'] = $this->userModel->getRoles($userId);
        // $user['permissions'] = $this->userModel->getPermissions($userId);

        return api_response($user, 'User registered successfully. Default role "Client" assigned.', 201);


        // $userId = $this->userModel->insert($data);
        // if ($userId === false) {
        //      return api_error('Failed to register user', 500, $this->userModel->errors());
        // }
        
        // $user = $this->userModel->find($userId);
        // unset($user['password']); // Jangan kirim password

        // return api_response($user, 'User registered successfully', 201);
    }

    public function login()
    {
        // ... (validasi input email dan password) ...
        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return api_error('Validation failed', $this->getResponse()->getStatusCode(), $this->validator->getErrors());
        }

        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return api_error('Invalid credentials', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Payload JWT tetap sederhana: user_id, email. Filter akan query roles/permissions.
        $payload = [
            'user_id' => $user['id'],
            'email'   => $user['email'],
            // Tidak perlu memasukkan roles/permissions di sini untuk menjaga token tetap kecil
            // dan data selalu up-to-date dari DB saat dicek oleh PermissionFilter.
        ];
        $token = $this->jwtService->encode($payload);

        // Siapkan data user untuk response (tanpa password)
        $userResponse = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'roles' => $this->userModel->getRoles($user['id']), // Kirim roles user saat login
            // 'permission' => $this->userModel->getPermissions($user['id']) // Opsional, bisa banyak
        ];

        return api_response(['token' => $token, 'user' => $userResponse], 'Login successful');


        // ----- OLD
            // $rules = [
            //     'email' => 'required|valid_email',
            //     'password' => 'required'
            // ];

            // if (!$this->validate($rules)) {
            //     return api_error('Validation failed', $this->getResponse()->getStatusCode(), $this->validator->getErrors());
            // }

            // $email = $this->request->getVar('email');
            // $password = $this->request->getVar('password');

            // $user = $this->userModel->findByEmail($email);

            // if (!$user || !password_verify($password, $user['password'])) {
            //     return api_error('Invalid credentials', 401);
            // }

            // // Jangan sertakan password dalam token payload
            // $payload = [
            //     'user_id' => $user['id'],
            //     'email' => $user['email'],
            //     'role' => $user['role']
            //     // Tambahkan data lain yang relevan jika perlu
            // ];
            // $token = $this->jwtService->encode($payload);

            // return api_response(['token' => $token, 'user' => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']]], 'Login successful');
        // ----- OLD
    }

    public function forgotPassword()
    {
        $rules = ['email' => 'required|valid_email'];
        if (!$this->validate($rules)) {
            return api_error('Validation failed', $this->getResponse()->getStatusCode(), $this->validator->getErrors());
        }

        $emailAddress = $this->request->getVar('email');
        $user = $this->userModel->findByEmail($emailAddress);

        if (!$user) {
            return api_error('Email not found', 404);
        }

        $token = random_string('alnum', 60); // Buat token acak
        $expires = Time::now()->addHours(1); // Token berlaku 1 jam

        $this->userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_expires' => $expires->toDateTimeString()
        ]);

        // Kirim email
        $email = \Config\Services::email();
        $email->setTo($user['email']);
        $email->setFrom(getenv('email.fromEmail'), getenv('email.fromName'));
        $email->setSubject('Password Reset Request');
        // Buat URL reset, misal: http://yourfrontend.com/reset-password?token=XYZ
        $resetLink = 'http://localhost:3000/reset-password?token=' . $token; // Ganti dengan URL frontend Anda
        $email->setMessage("To reset your password, please click the link below or paste it into your browser:\n\n" . $resetLink . "\n\nThis link will expire in 1 hour.");

        if ($email->send()) {
            return api_response(null, 'Password reset link sent to your email.');
        } else {
            log_message('error', $email->printDebugger(['headers']));
            return api_error('Failed to send password reset email.', 500);
        }
    }

    public function resetPassword()
    {
        $rules = [
            'token' => 'required',
            'password' => 'required|min_length[8]',
            'confirm_password' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return api_error('Validation failed', $this->getResponse()->getStatusCode(), $this->validator->getErrors());
        }

        $token = $this->request->getVar('token');
        $user = $this->userModel->findByResetToken($token);

        if (!$user) {
            return api_error('Invalid or expired reset token.', 400);
        }

        $this->userModel->update($user['id'], [
            'password' => $this->request->getVar('password'), // Akan di-hash oleh model
            'reset_token' => null,
            'reset_expires' => null
        ]);

        return api_response(null, 'Password has been reset successfully.');
    }

    public function logout()
    {
        // Dengan filter jwtAuth, $this->currentUser seharusnya sudah terisi
        // atau $request->user sudah tersedia.
        $userFromRequest = $this->request->user; // Di set oleh JWTAuthFilter

        if (!$userFromRequest) {
            // Ini sebagai fallback, seharusnya tidak terjadi jika filter jwtAuth bekerja
            log_message('error', '[AuthController::logout] User data not found on request object. Is JWTAuthFilter correctly applied and functioning for this route?');
            return api_error('User not authenticated or token processing issue.', ResponseInterface::HTTP_UNAUTHORIZED);
        }
        
        // Untuk pendekatan sederhana (client-side invalidation):
        // Server tidak perlu melakukan apa-apa selain mengakui permintaan logout.
        // Klien bertanggung jawab untuk menghapus token.

        // Jika Anda ingin implementasi server-side blacklisting (lebih kompleks):
        // 1. Ambil token dari header: $authHeader = $this->request->getHeaderLine('Authorization'); ... $token = $matches[1];
        // 2. Dapatkan JTI (JWT ID) dari $decodedToken->jti jika Anda menyertakannya saat pembuatan token.
        // 3. Simpan JTI atau token signature ke blacklist (misal: Redis, DB) dengan masa berlaku = sisa masa berlaku token.
        // 4. Modifikasi JWTAuthFilter untuk mengecek blacklist.
        // Contoh sederhana (tanpa blacklist):
        $userName = $userFromRequest->name ?? $userFromRequest->email ?? 'User';
        log_message('info', "User '{$userName}' (ID: {$userFromRequest->id}) logged out.");

        return api_response(null, 'Logout successful. Please discard your token.');
    }

}