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
        }
        
        // Cek user, password, dan permission
        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Email atau password salah.');
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
            'name'          => $user['name'],
            'email'         => $user['email'],
            'role_id'       => $getRoles[0]['id'],
            'roles'         => $getRoles[0]['name'],
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
        return view('backend/auth/register');
    }

    public function registerAction()
    {
        $session = session();
        $userModel = new \App\Models\UserModel();

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $password_confirm = $this->request->getPost('password_confirm');

        // Validasi sederhana
        if (!$name || !$email || !$password || !$password_confirm) {
            return redirect()->back()->withInput()->with('error', 'Semua field wajib diisi.');
        }
        if ($password !== $password_confirm) {
            return redirect()->back()->withInput()->with('error', 'Konfirmasi password tidak cocok.');
        }
        if ($userModel->where('email', $email)->first()) {
            return redirect()->back()->withInput()->with('error', 'Email sudah terdaftar.');
        }

        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'is_active' => 0 // default belum aktif
        ];
        $userModel->insert($userData);
        $userId = $userModel->getInsertID();

        // Generate token aktivasi dan simpan ke tabel user_activations
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));
        $activationModel = new \App\Models\UserActivationModel();
        $activationModel->insert([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Kirim email aktivasi
        $mailService = new \App\Services\MailService();
        $mailService->sendActivation($email, $token);

        return redirect()->to('/login')->with('success', 'Registrasi berhasil! Silakan cek email untuk aktivasi akun.');
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
}