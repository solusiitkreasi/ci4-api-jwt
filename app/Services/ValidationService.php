<?php

namespace App\Services;

use CodeIgniter\HTTP\ResponseInterface;

class ValidationService
{
    protected $validator;
    
    public function __construct()
    {
        $this->validator = \Config\Services::validation();
    }
    
    /**
     * Standardized validation for user operations
     */
    public function validateUser(array $data, string $operation = 'create', ?int $userId = null): array
    {
        $rules = [
            'create' => [
                'name' => 'required|min_length[3]|max_length[100]',
                'email' => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[8]',
                'role' => 'required|in_list[admin,client]'
            ],
            'update' => [
                'name' => 'if_exist|required|min_length[3]|max_length[100]',
                'email' => $userId ? "if_exist|required|valid_email|is_unique[users.email,id,{$userId}]" : 'if_exist|required|valid_email',
                'password' => 'if_exist|min_length[8]',
                'role' => 'if_exist|required|in_list[admin,client]'
            ]
        ];
        
        $currentRules = $rules[$operation] ?? $rules['create'];
        
        // Filter rules based on provided data
        $applicableRules = [];
        foreach ($currentRules as $field => $rule) {
            if ($operation === 'create' || array_key_exists($field, $data)) {
                $applicableRules[$field] = $rule;
            }
        }
        
        if (!$this->validator->setRules($applicableRules)->run($data)) {
            return [
                'valid' => false,
                'errors' => $this->validator->getErrors()
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate pagination parameters
     */
    public function validatePagination(array $params): array
    {
        $rules = [
            'page' => 'if_exist|is_natural_no_zero|less_than_equal_to[1000]',
            'perPage' => 'if_exist|is_natural_no_zero|less_than_equal_to[100]'
        ];
        
        if (!$this->validator->setRules($rules)->run($params)) {
            return [
                'valid' => false,
                'errors' => $this->validator->getErrors()
            ];
        }
        
        return [
            'valid' => true,
            'page' => (int)($params['page'] ?? 1),
            'perPage' => (int)($params['perPage'] ?? 10)
        ];
    }
    
    /**
     * Sanitize and validate business logic for lensa
     */
    public function validateLensaData(array $lensaData): array
    {
        $kodeSpecial = ['68539', '68540', '68541', '68542'];
        $errors = [];
        
        if (isset($lensaData['r_lensa']) && in_array($lensaData['r_lensa'], $kodeSpecial)) {
            $maxId = (int)($lensaData['max_id'] ?? 0);
            if ($maxId > 999) {
                $errors[] = 'Max ID untuk kode lensa khusus tidak boleh lebih dari 3 digit (999)';
            }
        } elseif (isset($lensaData['max_id'])) {
            $maxId = (int)$lensaData['max_id'];
            if ($maxId < 1001) {
                $errors[] = 'Max ID untuk kode lensa umum harus dimulai dari 1001';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
