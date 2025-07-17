<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class DocumentationController extends BaseController
{
    /**
     * Generate OpenAPI/Swagger documentation
     */
    public function getOpenAPISpec()
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'TOL API',
                'description' => 'API Documentation for TOL Application',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'API Support',
                    'email' => 'support@tol-api.com'
                ]
            ],
            'servers' => [
                [
                    'url' => base_url('/api'),
                    'description' => 'Development server'
                ]
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'
                    ],
                    'apiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-KEY'
                    ]
                ],
                'schemas' => $this->getSchemas(),
                'responses' => $this->getStandardResponses()
            ],
            'paths' => $this->getPaths(),
            'security' => [
                ['bearerAuth' => []],
                ['apiKeyAuth' => []]
            ]
        ];
        
        return $this->response->setJSON($spec);
    }
    
    /**
     * Serve Swagger UI
     */
    public function swaggerUI()
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>TOL API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui.css" />
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@3.52.5/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: "' . base_url('/api/docs/openapi.json') . '",
            dom_id: "#swagger-ui",
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIBundle.presets.standalone
            ]
        });
    </script>
</body>
</html>';
        
        return $this->response->setContentType('text/html')->setBody($html);
    }
    
    /**
     * Serve Markdown Documentation
     */
    public function markdownDocs()
    {
        $markdownPath = ROOTPATH . 'zzbc/API_DOCUMENTATION.md';
        
        if (!file_exists($markdownPath)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'API Documentation file not found'
            ]);
        }
        
        $markdown = file_get_contents($markdownPath);
        
        // Simple markdown to HTML conversion for basic display
        $html = $this->markdownToHtml($markdown);
        
        $fullHtml = '<!DOCTYPE html>
<html>
<head>
    <title>TOL API Documentation</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        code { background-color: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
        .method-get { color: #28a745; font-weight: bold; }
        .method-post { color: #007bff; font-weight: bold; }
        .method-put { color: #ffc107; font-weight: bold; }
        .method-delete { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    ' . $html . '
    <hr>
    <p><strong>Akses Swagger UI:</strong> <a href="' . base_url('/api/docs/') . '" target="_blank">Swagger Documentation</a></p>
    <p><strong>Download Postman Collection:</strong> <a href="' . base_url('/api/docs/postman') . '" target="_blank">Postman Collection</a></p>
</body>
</html>';
        
        return $this->response->setContentType('text/html')->setBody($fullHtml);
    }
    
    /**
     * Serve Postman Collection
     */
    public function postmanCollection()
    {
        $postmanPath = ROOTPATH . 'zzbc/TOL-API-V1.0.json';
        
        if (!file_exists($postmanPath)) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Postman Collection file not found'
            ]);
        }
        
        $collection = file_get_contents($postmanPath);
        
        return $this->response
            ->setContentType('application/json')
            ->setHeader('Content-Disposition', 'attachment; filename="TOL-API-V1.0.json"')
            ->setBody($collection);
    }
    
    private function getSchemas(): array
    {
        return [
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'role' => ['type' => 'string', 'enum' => ['admin', 'client']],
                    'created_at' => ['type' => 'string', 'format' => 'date-time'],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time']
                ]
            ],
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'message' => ['type' => 'string'],
                    'errors' => ['type' => 'object'],
                    'timestamp' => ['type' => 'string', 'format' => 'date-time']
                ]
            ]
        ];
    }
    
    private function getStandardResponses(): array
    {
        return [
            'BadRequest' => [
                'description' => 'Bad Request',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/Error']
                    ]
                ]
            ],
            'Unauthorized' => [
                'description' => 'Unauthorized',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/Error']
                    ]
                ]
            ],
            'NotFound' => [
                'description' => 'Not Found',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/Error']
                    ]
                ]
            ]
        ];
    }
    
    private function getPaths(): array
    {
        return [
            '/auth/login' => [
                'post' => [
                    'tags' => ['Authentication'],
                    'summary' => 'User login',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['email', 'password'],
                                    'properties' => [
                                        'email' => ['type' => 'string', 'format' => 'email'],
                                        'password' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Login successful',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'token' => ['type' => 'string'],
                                            'user' => ['$ref' => '#/components/schemas/User']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        '401' => ['$ref' => '#/components/responses/Unauthorized']
                    ]
                ]
            ]
            // Add more paths as needed
        ];
    }
    
    /**
     * Simple Markdown to HTML converter
     */
    private function markdownToHtml($markdown): string
    {
        // Basic markdown conversion - you can use a library like Parsedown for more advanced features
        $html = $markdown;
        
        // Headers
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
        
        // Bold
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        
        // Code blocks
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        
        // Tables - basic support
        $html = preg_replace('/\|/', '|', $html);
        
        // Convert table rows
        $html = preg_replace_callback(
            '/\|(.+)\|\n\|([-\s\|]+)\|\n((\|.+\|\n?)+)/',
            function($matches) {
                $header = trim($matches[1]);
                $rows = trim($matches[3]);
                
                $headerCells = array_map('trim', explode('|', $header));
                $headerCells = array_filter($headerCells);
                
                $table = '<table><thead><tr>';
                foreach ($headerCells as $cell) {
                    $table .= '<th>' . htmlspecialchars($cell) . '</th>';
                }
                $table .= '</tr></thead><tbody>';
                
                $rowLines = explode("\n", $rows);
                foreach ($rowLines as $row) {
                    if (trim($row) && strpos($row, '|') !== false) {
                        $cells = array_map('trim', explode('|', trim($row, '|')));
                        $table .= '<tr>';
                        foreach ($cells as $cell) {
                            $cell = trim($cell);
                            // Color code HTTP methods
                            if (in_array($cell, ['GET', 'POST', 'PUT', 'DELETE'])) {
                                $class = 'method-' . strtolower($cell);
                                $table .= '<td><span class="' . $class . '">' . htmlspecialchars($cell) . '</span></td>';
                            } else {
                                $table .= '<td>' . htmlspecialchars($cell) . '</td>';
                            }
                        }
                        $table .= '</tr>';
                    }
                }
                
                $table .= '</tbody></table>';
                return $table;
            },
            $html
        );
        
        // Line breaks
        $html = nl2br($html);
        
        return $html;
    }
}
