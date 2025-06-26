<?php 

namespace App\Controllers\Api\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Models\RoleModel;
use App\Models\PermissionModel;

class RoleController extends BaseController
{
    protected $roleModel;
    protected $permissionModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        helper(['response', 'form']);
        $this->validator = \Config\Services::validation();
    }

    public function index()
    {
        $roles = $this->roleModel->findAll();
        return api_response($roles, 'Roles fetched successfully');
    }

    public function show($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            return api_error('Role not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        $role['permissions'] = $this->roleModel->getPermissions($id);
        return api_response($role, 'Role details fetched successfully');
    }

    public function create()
    {
        $rules = [
            'name'        => 'required|max_length[50]|is_unique[roles.name]',
            'description' => 'permit_empty|max_length[255]',
        ];
        if (!$this->validate($rules)) {
            return api_error('Validation failed', ResponseInterface::HTTP_BAD_REQUEST, $this->validator->getErrors());
        }

        $data = $this->request->getJSON(true) ?: $this->request->getPost();
        $roleId = $this->roleModel->insert($data);

        if (!$roleId) {
            return api_error('Failed to create role', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $this->roleModel->errors());
        }
        $newRole = $this->roleModel->find($roleId);
        return api_response($newRole, 'Role created successfully', ResponseInterface::HTTP_CREATED);
    }

    public function update($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            return api_error('Role not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        $rules = [
            'name'        => "if_exist|required|max_length[50]|is_unique[roles.name,id,{$id}]",
            'description' => 'if_exist|permit_empty|max_length[255]',
        ];
        
        $data = $this->request->getJSON(true) ?: $this->request->getRawInput(); // getRawInput for PUT
        if (empty($data)) {
             $data = $this->request->getPost(); // Fallback for form-data
        }
        
        // Validate only fields that are present in the input
        $validationRulesToApply = [];
        foreach ($rules as $field => $rule) {
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

        if ($this->roleModel->update($id, $data) === false) {
            return api_error('Failed to update role', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $this->roleModel->errors());
        }
        $updatedRole = $this->roleModel->find($id);
        return api_response($updatedRole, 'Role updated successfully');
    }

    public function delete($id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            return api_error('Role not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        // Tambahan: Cek apakah role ini masih digunakan user sebelum dihapus
        $userRoleModel = new \App\Models\UserRoleModel(); // Jika ada modelnya, atau query manual
        $isUsed = $this->db->table('user_roles')->where('role_id', $id)->countAllResults() > 0;
        if ($isUsed) {
            return api_error('Cannot delete role. It is currently assigned to users.', ResponseInterface::HTTP_CONFLICT);
        }

        if ($this->roleModel->delete($id) === false) {
            return api_error('Failed to delete role', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $this->roleModel->errors());
        }
        return api_response(null, 'Role deleted successfully');
    }

    public function getPermissions($roleId)
    {
        $role = $this->roleModel->find($roleId);
        if (!$role) {
            return api_error('Role not found', ResponseInterface::HTTP_NOT_FOUND);
        }
        $permissions = $this->roleModel->getPermissions($roleId);
        $allPermissions = $this->permissionModel->orderBy('name', 'ASC')->findAll();
        return api_response(['assigned' => $permissions, 'all' => $allPermissions], 'Permissions for role fetched');
    }

    public function assignPermissions($roleId)
    {
        $role = $this->roleModel->find($roleId);
        if (!$role) {
            return api_error('Role not found', ResponseInterface::HTTP_NOT_FOUND);
        }

        $permissionIds = $this->request->getJSONVar('permission_ids'); // Array of permission IDs
        if (!is_array($permissionIds)) {
            return api_error('Invalid input: permission_ids must be an array.', ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        // Validasi apakah semua permissionId ada
        foreach($permissionIds as $pId) {
            if(!$this->permissionModel->find($pId)) {
                return api_error("Permission with ID {$pId} not found.", ResponseInterface::HTTP_BAD_REQUEST);
            }
        }

        if ($this->roleModel->assignPermissions($roleId, $permissionIds)) {
            return api_response(null, 'Permissions assigned to role successfully');
        }
        return api_error('Failed to assign permissions to role', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
    }
}