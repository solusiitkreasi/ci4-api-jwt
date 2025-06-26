<?php 

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Jika session 'is_logged_in' tidak ada atau tidak bernilai true
        if (!$session->get('is_logged_in')) {
            // Simpan URL yang sedang coba diakses agar bisa redirect kembali setelah login
            $session->set('redirect_url', current_url());
            
            // Redirect ke halaman login admin
            return redirect()->to('/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi
    }
}