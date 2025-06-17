<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Services\JWTService;
use Config\Services;
use App\Models\UserModel;

class JWTAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $jwtService = new JWTService();
        
        $authHeader = $request->getHeaderLine('Authorization');

        // Kasus 1: Header Authorization tidak ada (dianggap belum login)
        if (!$authHeader) {
            // Menggunakan helper response_helper.php
            return api_error('Authorization header required', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Kasus 2: Token tidak ditemukan atau format header salah (dianggap belum login/salah)
        if (!$token) {
            return api_error('Token not found or invalid format in Authorization header', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $decodedToken = $jwtService->decode($token);

        // Kasus 3: Token tidak valid (kedaluwarsa, signature salah, dll.)
        if (!$decodedToken) {
            return api_error('Invalid or expired token.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Kasus 4: user_id tidak ada di payload token
        if (!isset($decodedToken->user_id)) {
            // ... (log error) ...
            log_message('error', '[JWTAuthFilter] user_id not found in JWT payload. Payload: ' . json_encode($decodedToken));
            return api_error('Token payload is invalid (missing user_id).', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Kasus 5: User tidak ditemukan di database berdasarkan user_id dari token
        $userModel = new UserModel();
        $user = $userModel->find($decodedToken->user_id);
        if (!$user) {
            // ... (log warning) ...
            return api_error('User associated with this token not found.', ResponseInterface::HTTP_UNAUTHORIZED);
        }


        // Hapus password dari data user yang akan disimpan di request
        unset($user['password']);
        if (isset($user['reset_token'])) unset($user['reset_token']);
        if (isset($user['reset_expires'])) unset($user['reset_expires']);

        // Jika semua valid, request dilanjutkan ...
        $request->user = (object) $user; // Simpan sebagai objek agar konsisten dengan $decodedToken
        Services::injectMock('user', (object) $user); // Untuk akses via service('user')


        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}