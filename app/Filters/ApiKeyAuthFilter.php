<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\ApiKeyModel; // Anda perlu membuat model ini

class ApiKeyAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKeyHeader = $request->getHeaderLine('X-API-KEY');

        if (!$apiKeyHeader) {
            return api_error('API Key required', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $apiKeyModel = new ApiKeyModel();
        $apiKeyData = $apiKeyModel->where('key_value', $apiKeyHeader)
                                  ->where('status', 'active')
                                  ->first();

        if (!$apiKeyData) {
            return api_error('Invalid or inactive API Key', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Opsional: Cek permission jika diperlukan di sini
        if ($arguments && !empty($arguments)) {
            $permissions = json_decode($apiKeyData['permissions'] ?? '[]', true);
            foreach ($arguments as $requiredPermission) {
                if (!in_array($requiredPermission, $permissions)) {
                    return api_error('Insufficient API Key permissions', ResponseInterface::HTTP_FORBIDDEN);
                }
            }
        }
        
        // Simpan data API Key ke request jika perlu
        $request->apiKeyData = $apiKeyData;

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}