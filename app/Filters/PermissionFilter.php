<?php 

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;
use App\Models\UserModel;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('response'); // Pastikan helper response termuat
        $authenticatedUser = $request->user ?? Services::getSharedInstance('user');

        if (!$authenticatedUser || !isset($authenticatedUser->id)) {
            // Ini seharusnya sudah ditangani oleh JWTAuthFilter
            return api_error('User not authenticated.', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (empty($arguments)) {
            // Jika tidak ada argumen permission, berarti hanya butuh login (sudah dicek JWTAuthFilter)
            return $request; // Loloskan jika tidak ada permission spesifik yang diminta
        }

        $userModel = new UserModel();
        // Cek apakah user memiliki SEMUA permission yang dibutuhkan (AND logic)
        // Atau ANY permission (OR logic), tergantung kebutuhan. Di sini kita pakai AND.
        $requiredPermissions = is_array($arguments) ? $arguments : [$arguments];
        
        foreach ($requiredPermissions as $permSlug) {
            if (!$userModel->hasPermission($authenticatedUser->id, (string)$permSlug)) {
                log_message('notice', "User ID {$authenticatedUser->id} denied access. Missing permission: {$permSlug}");
                return api_error(
                    "Access denied. You do not have the required permission ({$permSlug}).",
                    ResponseInterface::HTTP_FORBIDDEN
                );
            }
        }
        
        return $request; // User memiliki semua permission yang dibutuhkan
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }
}