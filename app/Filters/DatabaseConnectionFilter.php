<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;

class DatabaseConnectionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            // Coba koneksi ke database yang dibutuhkan
            \Config\Database::connect()->connect();
            // Jika perlu cek grup lain: \Config\Database::connect('db_tol')->connect();
            \Config\Database::connect('db_tol')->connect();
        } catch (DatabaseException $e) {
            log_message('critical', 'Database Connection Filter Error: ' . $e->getMessage());
            
            helper('response'); // Muat helper jika belum global


            $response = api_error(
                'Layanan sedang tidak tersedia.',
                ResponseInterface::HTTP_SERVICE_UNAVAILABLE,
                ['database' => 'Tidak dapat terhubung ke server database.']
            );
            
            // Filter tidak bisa langsung send(). Return response object.
            // Atau untuk memastikan, kita bisa send dan exit.
            $response->send();
            exit();
        }
        
        // Jika koneksi berhasil, lanjutkan ke filter berikutnya atau controller
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi yang perlu dilakukan setelahnya
    }
}