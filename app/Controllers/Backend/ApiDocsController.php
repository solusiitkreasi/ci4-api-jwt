<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;

class ApiDocsController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'API Documentation',
            'api_docs_links' => [
                [
                    'title' => 'Swagger UI Documentation',
                    'description' => 'Interactive API documentation dengan Swagger UI',
                    'url' => base_url('/api/docs/'),
                    'icon' => 'code',
                    'color' => 'primary'
                ],
                [
                    'title' => 'Markdown Documentation',
                    'description' => 'Dokumentasi API dalam format Markdown yang mudah dibaca',
                    'url' => base_url('/api/docs/markdown'),
                    'icon' => 'file-text',
                    'color' => 'success'
                ],
                [
                    'title' => 'Postman Collection',
                    'description' => 'Download collection Postman untuk testing API',
                    'url' => base_url('/api/docs/postman'),
                    'icon' => 'download',
                    'color' => 'warning'
                ],
                [
                    'title' => 'OpenAPI Spec (JSON)',
                    'description' => 'Spesifikasi OpenAPI dalam format JSON',
                    'url' => base_url('/api/docs/openapi.json'),
                    'icon' => 'settings',
                    'color' => 'info'
                ]
            ]
        ];

        return view('backend/pages/api_docs', $data);
    }
}
