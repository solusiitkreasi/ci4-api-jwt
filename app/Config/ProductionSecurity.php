<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Production Security Configuration
 * Enhanced security settings for production environment
 */
class ProductionSecurity extends BaseConfig
{
    /**
     * Security Headers for Production
     */
    public array $securityHeaders = [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
    ];

    /**
     * Rate Limiting Configuration
     */
    public array $rateLimit = [
        'api' => [
            'requests' => 1000,
            'window' => 3600, // 1 hour
        ],
        'auth' => [
            'requests' => 5,
            'window' => 900, // 15 minutes
        ],
        'public' => [
            'requests' => 100,
            'window' => 300, // 5 minutes
        ],
    ];

    /**
     * JWT Configuration for Production
     */
    public array $jwt = [
        'accessTokenExpiry' => 3600, // 1 hour
        'refreshTokenExpiry' => 86400, // 24 hours
        'algorithm' => 'HS256',
        'issuer' => 'tol-api-system',
        'audience' => 'tol-api-clients',
    ];

    /**
     * API Key Configuration
     */
    public array $apiKey = [
        'header' => 'X-API-KEY',
        'keyLength' => 64,
        'expiry' => 0, // No expiry for API keys
    ];

    /**
     * Password Policy
     */
    public array $passwordPolicy = [
        'minLength' => 8,
        'requireUppercase' => true,
        'requireLowercase' => true,
        'requireNumbers' => true,
        'requireSpecialChars' => true,
        'maxAge' => 7776000, // 90 days
        'preventReuse' => 5, // Last 5 passwords
    ];

    /**
     * Session Security
     */
    public array $session = [
        'driver' => 'CodeIgniter\Session\Handlers\DatabaseHandler',
        'cookieName' => 'tol_session',
        'expiration' => 7200, // 2 hours
        'savePath' => null,
        'matchIP' => false,
        'timeToUpdate' => 300,
        'regenerateDestroy' => true,
        'cookieHttpOnly' => true,
        'cookieSecure' => true,
        'cookieSameSite' => 'Strict',
    ];

    /**
     * File Upload Security
     */
    public array $fileUpload = [
        'maxSize' => 10485760, // 10MB
        'allowedTypes' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'],
        'uploadPath' => FCPATH . 'uploads/',
        'encryptNames' => true,
        'removeSpaces' => true,
        'detectMime' => true,
    ];

    /**
     * Database Security
     */
    public array $database = [
        'queryLog' => true,
        'slowQueryThreshold' => 2000, // 2 seconds
        'connectionTimeout' => 30,
        'readTimeout' => 30,
        'writeTimeout' => 30,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ];

    /**
     * Logging Configuration
     */
    public array $logging = [
        'threshold' => 4, // ERROR level and above
        'maxFiles' => 30,
        'maxSize' => 104857600, // 100MB
        'logQueries' => false, // Only in development
        'logRequests' => true,
        'logErrors' => true,
    ];
}
