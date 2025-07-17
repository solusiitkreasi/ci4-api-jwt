<?php

namespace App\Services;

use CodeIgniter\HTTP\ResponseInterface;

class ErrorHandlerService
{
    /**
     * Standardized API error response
     */
    public static function apiError(
        string $message, 
        int $statusCode = 400, 
        $errors = null, 
        ?string $errorCode = null
    ): ResponseInterface {
        $response = service('response');
        
        $output = [
            'success' => false,
            'status' => $statusCode,
            'message' => $message,
            'timestamp' => date('c'),
            'path' => service('request')->getPath()
        ];
        
        if ($errors !== null) {
            $output['errors'] = $errors;
        }
        
        if ($errorCode !== null) {
            $output['error_code'] = $errorCode;
        }
        
        // Log error for monitoring
        if ($statusCode >= 500) {
            log_message('error', "API Error [{$statusCode}]: {$message}", [
                'errors' => $errors,
                'path' => $output['path'],
                'user_agent' => service('request')->getUserAgent()->getAgentString()
            ]);
        }
        
        return $response->setStatusCode($statusCode)->setJSON($output);
    }
    
    /**
     * Standardized API success response
     */
    public static function apiSuccess(
        $data = null, 
        string $message = 'Success', 
        int $statusCode = 200,
        ?array $meta = null
    ): ResponseInterface {
        $response = service('response');
        
        $output = [
            'success' => true,
            'status' => $statusCode,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ];
        
        if ($meta !== null) {
            $output['meta'] = $meta;
        }
        
        return $response->setStatusCode($statusCode)->setJSON($output);
    }
    
    /**
     * Handle database transaction errors
     */
    public static function handleDatabaseError(\Throwable $e, string $operation = 'database operation'): ResponseInterface
    {
        // Log the actual error for debugging
        log_message('error', "Database Error in {$operation}: " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Return generic error to user
        return self::apiError(
            "An error occurred during {$operation}. Please try again.",
            500,
            null,
            'DATABASE_ERROR'
        );
    }
    
    /**
     * Handle validation errors
     */
    public static function handleValidationError(array $errors, string $message = 'Validation failed'): ResponseInterface
    {
        return self::apiError($message, 422, $errors, 'VALIDATION_ERROR');
    }
}
