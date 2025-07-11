<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CustomErrors extends Controller
{
    /**
     * Method ini akan dipanggil setiap kali terjadi error 404.
     */
    public function show404()
    {
        // Pengecekan inti ada di sini.
        // Kita menggunakan metode is() dari Request untuk mengecek apakah URI
        // cocok dengan pola 'api/*' (dimulai dengan 'api/').

        $firstSegment = $this->request->getUri()->getSegment(1);

        if ($firstSegment === 'api') {
            
            // JIKA INI ADALAH REQUEST API:
            // Kirim response dalam format JSON.
            
            // Set status code HTTP ke 404 Not Found
            $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);

            // Siapkan data error dalam format array
            $responseData = [
                'status'   => ResponseInterface::HTTP_NOT_FOUND,
                'error'    => 'Not Found 404',
                'messages' =>  'The requested resource or endpoint does not exist.',
            ];

            // Kembalikan response sebagai JSON
            return $this->response->setJSON($responseData);

        } elseif ($firstSegment === 'backend') {
            // Cek session login backend

            $session = session()->get('is_logged_in');
            if (!$session) {
                // Redirect ke halaman login backend jika session habis
                return redirect()->to('/login');
            }

            $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
            $menuModel = model('App\\Models\\MenuModel');
            $data = [
                'title' => '404 Not Found',
                'message' => 'Maaf, halaman yang Anda cari tidak dapat ditemukan.',
                'menu_sidebar' => $menuModel->generateTree(),
            ];
            return view('errors/html/error_404_backend', $data);
        } else {
            
            $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
            
            // ====================================================================
            // INI BAGIAN YANG DIPERBAIKI
            // ====================================================================
            // Siapkan data untuk view, termasuk variabel $message
            $data = [
                // Anda bisa mengosongkan pesannya atau mengisi dengan pesan kustom
                'message' => 'Maaf, halaman yang Anda cari tidak dapat ditemukan.'
            ];

            // Kirim array $data sebagai argumen kedua ke fungsi view()
            return view('errors/html/error_404', $data);
        }
    }
}