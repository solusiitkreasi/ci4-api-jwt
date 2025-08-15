<?php 

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PasswordResetModel;

class AuthController extends BaseController
{
    public function loginView()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('is_logged_in')) {
            return redirect()->to('/backend/dashboard');
        }
        return view('backend/auth/login');
    }

    public function loginAction()
    {
        $session = session();
        $userModel = new UserModel();

        $email      = $this->request->getPost('email');
        $password   = $this->request->getPost('password');

        $user = $userModel->where('email', $email)->first();

        if($user) {
            $getRoles = $userModel->getRoles($user['id']);
            $roleIds = array_column($getRoles, 'id');
            $roleNames = array_column($getRoles, 'name');
        }
        
        // Cek user, password, dan permission
        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Email atau password salah.');
        }

        if (!$user['is_active']) {
            return redirect()->back()->with('error', 'Akun anda belum aktif, silahkan hubungi pic.');
        }

        // Cek apakah user punya hak akses ke dashboard (misal, punya role 'Super Admin')
        // Ini menggunakan model yang sudah ada dari RBAC
        $userPermissions = $userModel->getPermissions($user['id']);
        if (!in_array('dashboard', $userPermissions)) { // Contoh permission admin
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk login.');

            // $data = [
            //     // Anda bisa mengosongkan pesannya atau mengisi dengan pesan kustom
            //     'message' => 'Maaf, Anda tidak memiliki hak akses untuk login.'
            // ];

            // // Kirim array $data sebagai argumen kedua ke fungsi view()
            // return view('errors/html/error_404', $data);
        }

        // Set session data
        $sessionData = [
            'user_id'       => $user['id'],
            'kode_group'    => $user['kode_group'],
            'kode_customer' => $user['kode_customer'],
            'name'          => $user['name'],
            'email'         => $user['email'],
            'role_id'       => $roleIds, // array semua role id
            'roles'         => $roleNames, // array semua role name
            'is_logged_in'  => true
        ];

        // tesx($sessionData);

        #---  Arahkan ke dashboard yang sesuai
        // if($getRoles[0]['id'] == '2'){
            // $sessionData['is_logged_in'] = true;
            // Redirect ke dashboard atau ke URL yang disimpan sebelumnya
            // $redirectUrl = $session->get('redirect_url') ?? '/backend/dashboard';
        // }else{
        //     // $sessionData['is_client_logged_in'] = true;
        //     $redirectUrl = $session->get('redirect_url') ?? '/client/dashboard';
        // }

        $redirectUrl = $session->get('redirect_url') ?? '/backend/dashboard';

        $session->set($sessionData);

        $session->remove('redirect_url');
        
        return redirect()->to($redirectUrl);
    }

    public function forgotView()
    {
        return view('backend/auth/forgot_password');
    }

    public function forgotAction()
    {
        $email = $this->request->getPost('email');
        $data = ['email' => $email];

        if (!$email) {
            $data['error'] = 'Alamat email wajib diisi.';
            return view('backend/auth/forgot_password', $data);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (!$user) {
            $data['error'] = 'Email tidak terdaftar.';
            return view('backend/auth/forgot_password', $data);
        }

        // Generate token & simpan ke tabel password_reset
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $resetModel = new PasswordResetModel();
        $resetModel->insert([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt
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

        // Render view
        $emailBody = view('emails/reset_password', $data);

        $send_mail = send_email($email, 'Reset Password', $emailBody);

        $data['success'] = 'Link reset password sudah dikirim ke email Anda.';
        return view('backend/auth/forgot_password', $data);
    }

    public function resetView()
    {
        $token = $this->request->getGet('token');
        $data = ['token' => $token];
        // Optional: Cek token valid/expired sebelum tampil form
        $resetModel = new PasswordResetModel();
        $reset = $resetModel->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
        $data['is_valid'] = $reset ? true : false;

        return view('backend/auth/reset_password', $data);
    }

    public function resetAction()
    {
        $token    = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $password2 = $this->request->getPost('password2');
        $data = ['token' => $token];

        if (!$token || !$password || !$password2) {
            $data['error'] = 'Semua field wajib diisi.';
            return view('backend/auth/reset_password', $data);
        }

        if ($password !== $password2) {
            $data['error'] = 'Password tidak sama.';
            return view('backend/auth/reset_password', $data);
        }

        $resetModel = new PasswordResetModel();
        $reset = $resetModel->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$reset) {
            $data['error'] = 'Token tidak valid atau sudah kadaluwarsa.';
            return view('backend/auth/reset_password', $data);
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $reset['email'])->first();
        if (!$user) {
            $data['error'] = 'User tidak ditemukan.';
            return view('backend/auth/reset_password', $data);
        }


        $userModel->update($user['id'], [
            // 'password' => password_hash($password, PASSWORD_DEFAULT)
            'password' => $password
        ]);
        $resetModel->where('token', $token)->delete();

        $data['success'] = 'Password berhasil direset. Silakan <a href="/login">login</a> dengan password baru Anda.';
        return view('backend/auth/reset_password', $data);
    }

    public function logout()
    {
        // Dapatkan instance layanan sesi
        $session = session();

        // Dapatkan ID sesi saat ini SEBELUM dihancurkan
        // Ini penting karena setelah destroy(), ID sesi akan diregenerasi atau dihapus dari objek sesi
        $sessionId = $session->session_id; // Perhatikan bahwa properti ini bisa bervariasi
                                         // Terkadang $session->getId() juga bisa digunakan, tergantung versi CI.
                                         // Di CI4, $session->getId() adalah cara yang lebih tepat.

        // Periksa apakah ID sesi ada (untuk keamanan)
        if (!empty($sessionId)) {
            // Dapatkan instance Database
            $db = \Config\Database::connect();

            // Tentukan nama tabel sesi dari konfigurasi
            // Pastikan $session->sessionSavePath cocok dengan nama tabel di database Anda
            $sessionTable = 'ci_sessions'; // Biasanya 'ci_sessions'

            $iD = 'ci_sessions:'.$sessionId;

            // Jalankan query untuk menghapus baris sesi dari database
            $db->table($sessionTable)->where('id',$iD)->delete();

            // Menghancurkan semua data sesi dari objek sesi dan cookie
            $session->destroy();

            
        }
        
        return redirect()->to('/login');
    }

    public function registerView()
    {
        // Jika sudah login, redirect ke dashboard
        if (session()->get('is_logged_in')) {
            return redirect()->to('/backend/dashboard');
        }

        $db  = \Config\Database::connect('db_tol');

        $get_mst_group_customer = $db->query("SELECT * FROM db_tol.mst_group_customer 
                                    WHERE aktif = 1 
                                    AND kode_group IS NOT NULL 
                                    AND kode_group != '' 
                                    ORDER BY nama_group ASC
                                ")
                                ->getResult();

        $data['group_customer'] = $get_mst_group_customer;

        // Jika ada old('group_store'), ambil customer sesuai group lama
        $oldGroup = old('group_store');
        if ($oldGroup) {
            $get_mst_customer = $db->query("SELECT * FROM db_tol.mst_customer WHERE group_customer= ?", [$oldGroup])->getResult();
            $data['customer'] = $get_mst_customer;
        }

        return view('backend/auth/register', $data);
    }

    public function registerAction()
    {
        $session = session();
        $userModel = new \App\Models\UserModel();
        $roleModel = new \App\Models\RoleModel();

        $group_store        = $this->request->getPost('group_store');
        $store              = $this->request->getPost('store');
        $name               = $this->request->getPost('name');
        $email              = $this->request->getPost('email');
        $password           = $this->request->getPost('password');
        $password_confirm   = $this->request->getPost('password_confirm');

        // Validasi sederhana
        if (!$group_store || !$store || !$name || !$email || !$password || !$password_confirm) {
            return redirect()->back()->withInput()->with('error', 'Semua field wajib diisi.');
        }
        if ($password !== $password_confirm) {
            return redirect()->back()->withInput()->with('error', 'Konfirmasi password tidak cocok.');
        }
        if ($userModel->where('email', $email)->first()) {
            return redirect()->back()->withInput()->with('error', 'Email sudah terdaftar.');
        }

        $userData = [
            'kode_group'    => $group_store,
            'kode_customer' => $store,
            'name'          => $name,
            'email'         => $email,
            'password'      => $password,
            'is_active'     => 0 // default belum aktif
        ];

        $userModel->insert($userData);
        $userId = $userModel->getInsertID();

         // Assign default role 'Client'
        $clientRole = $roleModel->where('name', 'Client')->first();
        if ($clientRole) {
            $userModel->assignRole($userId, $clientRole['id']);
        }

        // Generate token aktivasi dan simpan ke tabel user_activations
        $token              = bin2hex(random_bytes(32));
        $expiresAt          = date('Y-m-d H:i:s', strtotime('+1 day'));
        $activationModel    = new \App\Models\UserActivationModel();
        $activationModel->insert([
            'user_id'       => $userId,
            'token'         => $token,
            'expires_at'    => $expiresAt,
            'created_at'    => date('Y-m-d H:i:s')
        ]);
        // get Mail Service
        $mailService = new \App\Services\MailService();

        $db_group_cst   = \Config\Database::connect();
        $group_customer =   $db_group_cst->query("SELECT * FROM user_groups WHERE kode_group = '$group_store' ")
                            ->getRow();

        // Kirim email aktivasi ke pic
        if($group_customer){
            $mailService->sendActivationPic($group_customer->email, $token, $userData);
        }
        // Kirim email aktivasi
        $mailService->sendActivation($email, $token);

        return redirect()->to('/login')->with('success', 'Registrasi berhasil !! Silakan hubungi pic untuk aktivasi akun.');
    }

    public function activateAccount()
    {
        $token = $this->request->getGet('token');
        if (!$token) {
            return redirect()->to('/login')->with('error', 'Token aktivasi tidak ditemukan.');
        }
        $activationModel = new \App\Models\UserActivationModel();
        $activation = $activationModel->where('token', $token)->first();
        if (!$activation) {
            return redirect()->to('/login')->with('error', 'Token aktivasi tidak valid.');
        }
        if (strtotime($activation['expires_at']) < time()) {
            return redirect()->to('/login')->with('error', 'Token aktivasi sudah kadaluarsa.');
        }
        $userModel = new \App\Models\UserModel();
        $userModel->update($activation['user_id'], ['is_active' => 1]);
        $activationModel->delete($activation['id']);
        return redirect()->to('/login')->with('success', 'Akun Anda berhasil diaktivasi. Silakan login.');
    }

    public function changePasswordView()
    {
        return view('backend/auth/change_password');
    }

    public function changePasswordAction()
    {
        $userId = session('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        $oldPassword = $this->request->getPost('old_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');
        $data = [];

        if (!$oldPassword || !$newPassword || !$confirmPassword) {
            $data['error'] = 'Semua field wajib diisi.';
            return view('backend/auth/change_password', $data);
        }
        if (!password_verify($oldPassword, $user['password'])) {
            $data['error'] = 'Password lama salah.';
            return view('backend/auth/change_password', $data);
        }
        if ($newPassword !== $confirmPassword) {
            $data['error'] = 'Password baru dan konfirmasi tidak sama.';
            return view('backend/auth/change_password', $data);
        }
        if (strlen($newPassword) < 6) {
            $data['error'] = 'Password baru minimal 6 karakter.';
            return view('backend/auth/change_password', $data);
        }
        // Update password
        $userModel->update($userId, ['password' => $newPassword]);
        $data['success'] = 'Password berhasil diubah.';
        return view('backend/auth/change_password', $data);
    }

    public function getCustomerByGroup()
    {
        $group = $this->request->getPost('group');
        $db  = \Config\Database::connect('db_tol');
        $customers = $db->query("SELECT * FROM db_tol.mst_customer WHERE group_customer = ?", [$group])->getResult();
        return $this->response->setJSON($customers);
    }

    /**
     * Check session status untuk AJAX request
     */
    public function checkSession()
    {
        $session = session();
        
        if (!$session->get('is_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'authenticated' => false,
                'message' => 'Session expired'
            ]);
        }

        // Hitung waktu tersisa session
        $lastActivity = $session->get('last_activity') ?? time();
        $sessionLifetime = config('Session')->expiration ?: 7200; // Default 2 hours
        $currentTime = time();
        $timeLeft = $sessionLifetime - ($currentTime - $lastActivity);

        if ($timeLeft <= 0) {
            // Session expired
            $session->destroy();
            return $this->response->setJSON([
                'success' => false,
                'authenticated' => false,
                'message' => 'Session expired'
            ]);
        }

        // Update last activity
        $session->set('last_activity', $currentTime);

        return $this->response->setJSON([
            'success' => true,
            'authenticated' => true,
            'time_left' => $timeLeft,
            'message' => 'Session active'
        ]);
    }

    /**
     * Extend session untuk user yang masih aktif
     */
    public function extendSession()
    {
        $session = session();
        
        if (!$session->get('is_logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }

        // Update last activity untuk memperpanjang session
        $session->set('last_activity', time());

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Session extended successfully'
        ]);
    }

}