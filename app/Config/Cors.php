<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    /**
     * The default CORS configuration.
     *
     * @var array{
     *      allowedOrigins: list<string>,
     *      allowedOriginsPatterns: list<string>,
     *      supportsCredentials: bool,
     *      allowedHeaders: list<string>,
     *      exposedHeaders: list<string>,
     *      allowedMethods: list<string>,
     *      maxAge: int,
     *  }
     */
    public array $default = [
        /**
         * Origins for the `Access-Control-Allow-Origin` header.
         * PRODUCTION: Specify exact domains
         */
        'allowedOrigins' => [
            'https://your-frontend-domain.com',
            'https://admin.your-domain.com',
        ],

        /**
         * Origin regex patterns for the `Access-Control-Allow-Origin` header.
         * PRODUCTION: Use specific patterns
         */
        'allowedOriginsPatterns' => [
            '#https://.*\.your-domain\.com#',
        ],

        /**
         * Weather to send the `Access-Control-Allow-Credentials` header.
         * PRODUCTION: Set to true for JWT/session based auth
         */
        'supportsCredentials' => true,

        /**
         * Set headers to allow.
         * PRODUCTION: Specify only needed headers
         */
        'allowedHeaders' => [
            'Accept',
            'Accept-Language',
            'Content-Language',
            'Content-Type',
            'Authorization',
            'X-Requested-With',
            'X-CSRF-TOKEN',
            'X-API-KEY',
        ],

        /**
         * Set headers to expose.
         * PRODUCTION: Minimal exposure
         */
        'exposedHeaders' => [
            'X-Total-Count',
            'X-Page-Count',
        ],

        /**
         * Set methods to allow.
         * PRODUCTION: Only needed methods
         */
        'allowedMethods' => [
            'GET',
            'POST',
            'PUT',
            'PATCH',
            'DELETE',
            'OPTIONS',
        ],

        /**
         * Set how many seconds the results of a preflight request can be cached.
         * PRODUCTION: Reasonable cache time
         */
        'maxAge' => 3600, // 1 hour
    ];
}
