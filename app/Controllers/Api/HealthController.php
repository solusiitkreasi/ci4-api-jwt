<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class HealthController extends BaseController
{
    /**
     * Health check endpoint untuk monitoring
     * 
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => ENVIRONMENT,
            'version' => '1.0.0',
            'checks' => []
        ];

        // Database health check
        try {
            $db = \Config\Database::connect();
            $db->query('SELECT 1');
            $health['checks']['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $health['checks']['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
            $health['status'] = 'unhealthy';
        }

        // Cache health check
        try {
            $cache = \Config\Services::cache();
            $cache->save('health_check', 'test', 10);
            $testValue = $cache->get('health_check');
            
            if ($testValue === 'test') {
                $health['checks']['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Cache is working'
                ];
            } else {
                throw new \Exception('Cache test failed');
            }
        } catch (\Exception $e) {
            $health['checks']['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Cache failed: ' . $e->getMessage()
            ];
        }

        // File system health check
        try {
            $writablePath = WRITEPATH . 'health_check.txt';
            file_put_contents($writablePath, 'test');
            
            if (file_exists($writablePath)) {
                unlink($writablePath);
                $health['checks']['filesystem'] = [
                    'status' => 'healthy',
                    'message' => 'File system is writable'
                ];
            } else {
                throw new \Exception('Write test failed');
            }
        } catch (\Exception $e) {
            $health['checks']['filesystem'] = [
                'status' => 'unhealthy',
                'message' => 'File system error: ' . $e->getMessage()
            ];
            $health['status'] = 'unhealthy';
        }

        // Memory usage check
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        if ($memoryLimit !== '-1') {
            $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
            $memoryUsagePercent = ($memoryUsage / $memoryLimitBytes) * 100;
            
            if ($memoryUsagePercent < 80) {
                $health['checks']['memory'] = [
                    'status' => 'healthy',
                    'usage' => $this->formatBytes($memoryUsage),
                    'limit' => $memoryLimit,
                    'percentage' => round($memoryUsagePercent, 2) . '%'
                ];
            } else {
                $health['checks']['memory'] = [
                    'status' => 'warning',
                    'usage' => $this->formatBytes($memoryUsage),
                    'limit' => $memoryLimit,
                    'percentage' => round($memoryUsagePercent, 2) . '%'
                ];
            }
        } else {
            $health['checks']['memory'] = [
                'status' => 'healthy',
                'usage' => $this->formatBytes($memoryUsage),
                'limit' => 'unlimited'
            ];
        }

        // Set HTTP status based on health
        $httpStatus = ($health['status'] === 'healthy') ? 200 : 503;

        return $this->response
            ->setStatusCode($httpStatus)
            ->setJSON($health);
    }

    /**
     * Simple ping endpoint
     */
    public function ping(): ResponseInterface
    {
        return $this->response->setJSON([
            'message' => 'pong',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
