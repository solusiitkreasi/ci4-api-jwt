<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = $request->user ?? Services::getSharedInstance('user'); // Ambil dari request atau service

        if (!$user) {
            // Ini seharusnya sudah ditangani oleh JWTAuthFilter, tapi sebagai fallback
            return api_error('User not authenticated', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (empty($arguments)) {
            // Jika tidak ada argumen role, berarti hanya butuh login
            return $request;
        }

        $userRole = $user['role']; // Asumsi 'role' adalah nama kolom di tabel user

        if (!in_array($userRole, $arguments)) {
            return api_error('Access denied. Insufficient role.', ResponseInterface::HTTP_FORBIDDEN);
        }
        
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}