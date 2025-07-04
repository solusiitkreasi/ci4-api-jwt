<?php

namespace App\Controllers;

use App\Models\PasswordResetModel;
use App\Models\UserModel;

use App\Controllers\BaseController;

class ResetPasswordController extends BaseController
{
    public function form()
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

    public function submit()
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
}