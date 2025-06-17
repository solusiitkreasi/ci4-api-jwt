<?php 

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionController extends BaseController
{
    protected $permissionModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        helper(['response', 'form']);
        $this->validator = \Config\Services::validation();
    }

    public function index()
    {
        $permissions = $this->permissionModel->orderBy('name', 'ASC')->findAll();
        return api_response($permissions, 'Permissions fetched successfully');
    }

    public function show($id)
    {
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            return api_error('Permission not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        return api_response($permission, 'Permission details fetched successfully');
    }

    public function create()
    {
        $data = $this->request->getJSON(true) ?: $this->request->getPost();
        
        // Menggunakan validasi dari model jika ada, atau definisikan rules di sini
        if (!$this->permissionModel->validate($data)) {
             return api_error('Validation failed', ResponseInterface::HTTP_BAD_REQUEST, $this->permissionModel->errors());
        }

        $permissionId = $this->permissionModel->insert($data);
        if (!$permissionId) {
            return api_error('Failed to create permission', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $this->permissionModel->errors());
        }
        $newPermission = $this->permissionModel->find($permissionId);
        return api_response($newPermission, 'Permission created successfully', ResponseInterface::HTTP_CREATED);
    }

    public function update($id)
    {
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            return api_error('Permission not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        
        $data = $this->request->getJSON(true) ?: $this->request->getRawInput();
         if (empty($data)) {
             $data = $this->request->getPost();
        }
        
        // Untuk update, slug harus unik kecuali untuk ID yang sama
        $validationRules = [
            'name' => "if_exist|required|max_length[100]",
            'slug' => "if_exist|required|max_length[100]|is_unique[permissions.slug,id,{$id}]|alpha_dash",
            'description' => 'if_exist|permit_empty|max_length[255]'
        ];
        
        $validationRulesToApply = [];
        foreach ($validationRules as $field => $rule) {
            if (array_key_exists($field, $data)) {
                $validationRulesToApply[$field] = $rule;
            }
        }

        if (!empty($validationRulesToApply) && !$this->validate($validationRulesToApply)) {
            return api_error('Validation failed', ResponseInterface::HTTP_BAD_REQUEST, $this->validator->getErrors());
        }
        
        if (empty($data)) {
            return api_error('No data provided for update', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($this->permissionModel->update($id, $data) === false) {
            return api_error('Failed to update permission', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $this->permissionModel->errors());
        }
        $updatedPermission = $this->permissionModel->find($id);
        return api_response($updatedPermission, 'Permission updated successfully');
    }

    public function delete($id)
    {
        $permission = $this->permissionModel->find($id);
        if (!$permission) {
            return api_error('Permission not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        // Tambahan: Cek apakah permission ini masih digunakan role sebelum dihapus
        $isUsed = $this->db->table('role_permissions')->where('permission_id', $id)->countAllResults() > 0;
        if ($isUsed) {
            return api_error('Cannot delete permission. It is currently assigned to roles.', ResponseInterface::HTTP_CONFLICT);
        }

        if ($this->permissionModel->delete($id) === false) {
            return api_error('Failed to delete permission', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $this->permissionModel->errors());
        }
        return api_response(null, 'Permission deleted successfully');
    }
}