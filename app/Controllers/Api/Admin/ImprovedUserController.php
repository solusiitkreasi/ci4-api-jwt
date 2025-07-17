<?php

namespace App\Controllers\Api\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\ValidationService;
use App\Services\ErrorHandlerService;
use CodeIgniter\HTTP\ResponseInterface;

class ImprovedUserController extends BaseController
{
    protected $userModel;
    protected $validationService;
    protected $currentUser;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->validationService = new ValidationService();
        
        // Get current user from request (set by JWT filter)
        $this->currentUser = service('request')->user ?? null;
    }
    
    /**
     * Get paginated users with improved validation and error handling
     */
    public function getUsers()
    {
        try {
            // Validate pagination parameters
            $paginationParams = $this->request->getGet(['page', 'perPage']);
            $validation = $this->validationService->validatePagination($paginationParams);
            
            if (!$validation['valid']) {
                return ErrorHandlerService::handleValidationError($validation['errors']);
            }
            
            $page = $validation['page'];
            $perPage = $validation['perPage'];
            
            // Get users with proper selection (exclude password)
            $users = $this->userModel
                ->select('id, name, email, role, created_at, updated_at')
                ->paginate($perPage, 'default', $page);
                
            $pager = $this->userModel->pager;
            
            $meta = [
                'pagination' => [
                    'total' => $pager->getTotal(),
                    'perPage' => $pager->getPerPage(),
                    'currentPage' => $pager->getCurrentPage(),
                    'lastPage' => $pager->getLastPage(),
                    'hasNext' => $pager->hasNext(),
                    'hasPrevious' => $pager->hasPrevious()
                ]
            ];
            
            return ErrorHandlerService::apiSuccess($users, 'Users retrieved successfully', 200, $meta);
            
        } catch (\Throwable $e) {
            return ErrorHandlerService::handleDatabaseError($e, 'fetching users');
        }
    }
    
    /**
     * Create user with improved validation and transaction handling
     */
    public function createUser()
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Get and validate input data
            $data = $this->request->getJSON(true) ?? $this->request->getPost();
            
            $validation = $this->validationService->validateUser($data, 'create');
            if (!$validation['valid']) {
                return ErrorHandlerService::handleValidationError($validation['errors']);
            }
            
            // Check if current user has permission to create users with specified role
            if (!$this->canAssignRole($data['role'])) {
                return ErrorHandlerService::apiError(
                    'You do not have permission to assign this role',
                    403,
                    null,
                    'PERMISSION_DENIED'
                );
            }
            
            // Create user
            $userId = $this->userModel->insert([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // Will be hashed by model
                'role' => $data['role'],
                'created_by' => $this->currentUser['id'] ?? null
            ]);
            
            if (!$userId) {
                $db->transRollback();
                return ErrorHandlerService::handleDatabaseError(
                    new \Exception('Failed to create user'),
                    'user creation'
                );
            }
            
            $db->transComplete();
            
            // Get created user (without password)
            $newUser = $this->userModel
                ->select('id, name, email, role, created_at')
                ->find($userId);
                
            return ErrorHandlerService::apiSuccess(
                $newUser,
                'User created successfully',
                201
            );
            
        } catch (\Throwable $e) {
            $db->transRollback();
            return ErrorHandlerService::handleDatabaseError($e, 'user creation');
        }
    }
    
    /**
     * Update user with partial update support
     */
    public function updateUser($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Check if user exists
            $existingUser = $this->userModel->find($id);
            if (!$existingUser) {
                return ErrorHandlerService::apiError('User not found', 404, null, 'USER_NOT_FOUND');
            }
            
            // Get input data
            $data = $this->request->getJSON(true) ?? $this->request->getRawInput();
            
            if (empty($data)) {
                return ErrorHandlerService::apiError('No data provided for update', 400, null, 'NO_DATA');
            }
            
            // Validate input
            $validation = $this->validationService->validateUser($data, 'update', $id);
            if (!$validation['valid']) {
                return ErrorHandlerService::handleValidationError($validation['errors']);
            }
            
            // Check role assignment permission
            if (isset($data['role']) && !$this->canAssignRole($data['role'])) {
                return ErrorHandlerService::apiError(
                    'You do not have permission to assign this role',
                    403,
                    null,
                    'PERMISSION_DENIED'
                );
            }
            
            // Add metadata
            $data['updated_by'] = $this->currentUser['id'] ?? null;
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Update user
            if (!$this->userModel->update($id, $data)) {
                $db->transRollback();
                return ErrorHandlerService::handleDatabaseError(
                    new \Exception('Failed to update user'),
                    'user update'
                );
            }
            
            $db->transComplete();
            
            // Get updated user
            $updatedUser = $this->userModel
                ->select('id, name, email, role, updated_at')
                ->find($id);
                
            return ErrorHandlerService::apiSuccess(
                $updatedUser,
                'User updated successfully'
            );
            
        } catch (\Throwable $e) {
            $db->transRollback();
            return ErrorHandlerService::handleDatabaseError($e, 'user update');
        }
    }
    
    /**
     * Check if current user can assign specified role
     */
    private function canAssignRole(string $role): bool
    {
        if (!$this->currentUser) {
            return false;
        }
        
        // Super admin can assign any role
        if ($this->currentUser['role'] === 'super_admin') {
            return true;
        }
        
        // Admin can assign client role only
        if ($this->currentUser['role'] === 'admin' && $role === 'client') {
            return true;
        }
        
        return false;
    }
}
