<?php 

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class WebAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Jika user belum login, arahkan ke halaman login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(route_to('web.login.form'));

            // Simpan URL yang sedang coba diakses agar bisa redirect kembali setelah login
            $session->set('redirect_url', current_url());
            
            // Redirect ke halaman login admin
            return redirect()->to('/login');
        }

        // Jika filter diberi argumen peran (e.g., 'webAuth:Super Admin')
        if (!empty($arguments)) {
            $userRoles = $session->get('roles') ?? []; // Ambil peran dari session
            $hasPermission = false;
            foreach ($arguments as $requiredRole) {
                // Cek apakah user memiliki salah satu peran yang dibutuhkan
                if (in_array($requiredRole, $userRoles)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                // Jika tidak punya peran, arahkan ke halaman utama dengan pesan error
                return redirect()->to('/login')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
            }
        }
        
        // Jika lolos semua, lanjutkan
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ...
    }
}