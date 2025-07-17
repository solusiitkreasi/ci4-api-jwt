<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RateLimitFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $cache = \Config\Services::cache();
        $clientIP = $request->getIPAddress();
        $endpoint = $request->getPath();
        
        // Rate limit key
        $key = "rate_limit:{$clientIP}:{$endpoint}";
        
        // Get current count
        $count = $cache->get($key) ?? 0;
        
        // Define limits based on endpoint
        $limits = [
            '/api/auth/login' => ['max' => 5, 'window' => 900], // 5 attempts per 15 minutes
            '/api/auth/forgot-password' => ['max' => 3, 'window' => 3600], // 3 attempts per hour
            'default' => ['max' => 100, 'window' => 3600] // 100 requests per hour
        ];
        
        $limit = $limits[$endpoint] ?? $limits['default'];
        
        if ($count >= $limit['max']) {
            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'retry_after' => $limit['window']
                ]);
        }
        
        // Increment counter
        $cache->save($key, $count + 1, $limit['window']);
        
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
