<?php 

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\UserModel;

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
        if (!in_array('manage-users', $userPermissions)) { // Contoh permission admin
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
            'is_logged_in'  => true
        ];

        #---  Arahkan ke dashboard yang sesuai
        // if($getRoles[0]['id'] == '2'){
            // $sessionData['is_logged_in'] = true;
            // Redirect ke dashboard atau ke URL yang disimpan sebelumnya
            $redirectUrl = $session->get('redirect_url') ?? '/backend/dashboard';
        // }else{
        //     // $sessionData['is_client_logged_in'] = true;
        //     $redirectUrl = $session->get('redirect_url') ?? '/client/dashboard';
        // }

        $session->set($sessionData);


        $session->remove('redirect_url');
        
        return redirect()->to($redirectUrl);
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
}