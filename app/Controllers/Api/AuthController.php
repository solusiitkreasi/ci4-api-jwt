<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait; // Untuk response yang lebih mudah
use CodeIgniter\I18n\Time;
use Config\Services; // Untuk mengambil user yang sedang login
use App\Services\JWTService;

use App\Models\UserModel;
use App\Models\RoleModel;

use App\Models\PasswordResetModel;
use App\Models\UserActivationModel;


class AuthController extends BaseController
{
    use ResponseTrait; // Menggunakan ResponseTrait CI4

    protected $userModel;
    protected $roleModel;

    protected $passwordResetModel;
    protected $userActivationModel;

    protected $jwtService;
    protected $currentUser; // Untuk menyimpan data user yang sedang login

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();

        $this->passwordResetModel = new PasswordResetModel();
        $this->userActivationModel = new UserActivationModel();

        $this->jwtService = new JWTService();
        $this->auditLogService = new \App\Services\AuditLogService();
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

            // tesx($this->userModel->errors());

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

        if(empty($user)) {
            return api_error('User not found',404);
        }
        
        if (!$user || !password_verify($password, $user['password'])) {
            return api_error('Invalid credentials', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Payload JWT tetap sederhana: user_id, email. Filter akan query roles/permissions.
        $payload = [
            'user_id' => $user['id'],
            'email'   => $user['email'],
        ];
        $token = $this->jwtService->encode($payload);

        // Generate refresh token
        $refreshModel = new \App\Models\JwtRefreshTokenModel();
        $refreshToken = bin2hex(random_bytes(40));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $refreshModel->insert([
            'user_id' => $user['id'],
            'refresh_token' => $refreshToken,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
            'revoked' => false,
        ]);

        // Log successful login
        if (function_exists('log_audit')) {
            log_audit($user['id'], 'LOGIN', 'User login');
        }

        // Siapkan data user untuk response (tanpa password)
        $userResponse = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'roles' => $this->userModel->getRoles($user['id']), // Kirim roles user saat login
        ];

        return api_response([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => $userResponse
        ], 'Login successful');
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
        $userFromRequest = $this->request->user; // Di set oleh JWTAuthFilter

        if (!$userFromRequest) {
            log_message('error', '[AuthController::logout] User data not found on request object. Is JWTAuthFilter correctly applied and functioning for this route?');
            return api_error('User not authenticated or token processing issue.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Ambil token dari header Authorization
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = null;
        if (preg_match('/Bearer\s(.*)/', $authHeader, $matches)) {
            $token = $matches[1];
        }
        if ($token) {
            // Blacklist token (revoke)
            $revokedModel = new \App\Models\JwtRevokedTokenModel();
            $revokedModel->insert([
                'token' => $token,
                'revoked_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $userName = $userFromRequest->name ?? $userFromRequest->email ?? 'User';
        log_message('info', "User '{$userName}' (ID: {$userFromRequest->id}) logged out.");

        // Log logout activity
        if (function_exists('log_audit')) {
            log_audit($userFromRequest->id, 'LOGOUT', 'User logout');
        }

        return api_response(null, 'Logout successful. Please discard your token.');
    }



    //  New Endpoint With Send Emai;
    // POST /api/auth/forgot-password
    public function forgotPasswordMail()
    {
        $email = $this->request->getVar('email');

        if (!$email) {
            return $this->api_error('Email wajib diisi');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (!$user) {
            // Jangan bocorkan jika email tidak ada
            return api_error('email tidak ditemukan ');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $resetModel = new PasswordResetModel();
        $resetModel->insert([
            'email'      => $email,
            'token'      => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Kirim email link reset
        $resetLink = site_url('reset_password?token=' . $token);
        // $resetLink = 'https://apitol.topopticallab.co.id/reset_password?token=' . $token;

        $data = [
            'subject'       => 'Reset Password',
            'title'         => 'Reset Password',
            'message'       => '<p>Silakan klik tombol di bawah untuk mengganti password Anda:</p>',
            'action_button' => '<a class="button " href="'.$resetLink.'">Reset Password</a>',
            'year'          => date('Y'),
        ];

        // $emailBody = "Klik link berikut untuk reset password:\n$resetLink\nBerlaku 30 menit.";
        // Render view
        $emailBody = view('emails/reset_password', $data);

        $send_mail = send_email($email, 'Reset Password', $emailBody);
   
        return api_response(['status' => 'success'], 'Link Reset password terkirim ke email');
    }

    // POST /api/auth/reset-password
    public function resetPasswordMail()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        if (!$token || !$password) {
            return $this->api_error('Token dan password wajib diisi');
        }

        $resetModel = new PasswordResetModel();
        $reset = $resetModel->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$reset) {
            return $this->api_error('Token tidak valid/expired');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $reset['email'])->first();
        if (!$user) {
            return $this->api_error('User tidak ditemukan');
        }

        $userModel->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);

        // Hapus token setelah pakai
        $resetModel->where('token', $token)->delete();

        return $this->respond(['status' => 'success', 'message' => 'Password berhasil direset']);
    }

    // GET /api/auth/activate?token=xxx
    public function activateAccount()
    {
        $token = $this->request->getGet('token');
        if (!$token) {
            return $this->api_error('Token wajib diisi');
        }

        $activationModel = new UserActivationModel();
        $activation = $activationModel->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$activation) {
            return $this->api_error('Token tidak valid/expired');
        }

        $userModel = new UserModel();
        $userModel->update($activation['user_id'], ['is_active' => 1]);
        $activationModel->where('token', $token)->delete();

        return $this->respond(['status' => 'success', 'message' => 'Akun berhasil diaktivasi, silakan login.']);
    }

    // POST /api/auth/register (tambahan aktivasi email)
    public function registerMail()
    {
        $userModel = new UserModel();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        // Validasi dsb...

        $userId = $userModel->insert([
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'is_active' => 0,
            // Data lain...
        ]);

        // Generate token aktivasi
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
        $activationModel = new UserActivationModel();
        $activationModel->insert([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $activationLink = site_url('api/auth/activate?token=' . $token);
        $emailBody = "Aktifkan akun Anda dengan klik link berikut:\n$activationLink\nBerlaku 1 hari.";
        send_email($email, 'Aktivasi Akun', $emailBody);

        return $this->respond(['status' => 'success', 'message' => 'Silakan cek email untuk aktivasi akun.']);
    }

    /**
     * Refresh JWT access token using a valid refresh token
     * POST /api/v1/auth/refresh
     * Body: { refresh_token: string }
     */
    public function refreshToken()
    {
        $refreshToken = $this->request->getVar('refresh_token');
        if (!$refreshToken) {
            return api_error('Refresh token is required', 400);
        }

        $refreshModel = new \App\Models\JwtRefreshTokenModel();
        $revokedModel = new \App\Models\JwtRevokedTokenModel();
        $userModel = new \App\Models\UserModel();

        $tokenRow = $refreshModel->where('refresh_token', $refreshToken)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->where('revoked', false)
            ->first();

        if (!$tokenRow) {
            return api_error('Invalid or expired refresh token', 401);
        }

        // Optional: Cek apakah refresh token sudah direvoke (extra safety)
        $revoked = $revokedModel->where('token', $refreshToken)->first();
        if ($revoked) {
            return api_error('Refresh token has been revoked', 401);
        }

        // Ambil user
        $user = $userModel->find($tokenRow['user_id']);
        if (!$user) {
            return api_error('User not found', 404);
        }

        // Generate access token baru
        $payload = [
            'user_id' => $user['id'],
            'email'   => $user['email'],
        ];
        $accessToken = $this->jwtService->encode($payload);

        // Generate refresh token baru
        $newRefreshToken = bin2hex(random_bytes(40));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
        $refreshModel->insert([
            'user_id' => $user['id'],
            'refresh_token' => $newRefreshToken,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
            'revoked' => false,
        ]);

        // Tandai refresh token lama sebagai revoked
        $refreshModel->update($tokenRow['id'], ['revoked' => true]);
        $revokedModel->insert([
            'token' => $refreshToken,
            'revoked_at' => date('Y-m-d H:i:s'),
        ]);

        // Log aktivitas refresh
        if (function_exists('log_audit')) {
            log_audit($user['id'], 'REFRESH_TOKEN', 'User refreshed JWT token');
        }

        return api_response([
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_in' => getenv('jwt.expirationTime') ?: 3600,
        ], 'Token refreshed successfully');
    }

}