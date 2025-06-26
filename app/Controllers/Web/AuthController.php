<?php 

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function loginForm()
    {

        // $session = session()->get('isLoggedIn'); 
        // tesx('test', $session);
       // Jika sudah login, redirect ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/direct_login');
        }
        return view('backend/auth/login');
    }

    public function attemptLogin()
    {
        $session = session();
        $userModel = new UserModel();
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $userModel->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau Password salah.');
        }

        $userPermissions = $userModel->getPermissions($user['id']);
        if (!in_array('manage-users', $userPermissions)) { // Contoh permission admin
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk login.');
        }

        // Ambil peran user
        $roles = $userModel->getRoles($user['id']);
        $roleNames = array_column($roles, 'name'); // e.g., ['Super Admin', 'Another Role']

        // Simpan data ke session
        $sessionData = [
            'user_id'    => $user['id'],
            'name'       => $user['name'],
            'email'      => $user['email'],
            'roles'      => $roleNames, // Kita ingin cek isi variabel ini
            'isLoggedIn' => true,
        ];

        $redirectUrl = $session->get('redirect_url') ?? '/direct_login';

        $session->set($sessionData);

        $session->remove('redirect_url');
        
        return redirect()->to($redirectUrl);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(route_to('web.login.form'))->with('success', 'Anda berhasil logout.');
    }
}