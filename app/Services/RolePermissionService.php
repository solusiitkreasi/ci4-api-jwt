<?php
namespace App\Services;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\PermissionModel;

class RolePermissionService
{
    protected $userModel;
    protected $roleModel;
    protected $permissionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
    }

    // ===================== ROLE =====================
    public function createRole($data)
    {
        return $this->roleModel->insert($data);
    }

    public function updateRole($id, $data)
    {
        return $this->roleModel->update($id, $data);
    }

    public function deleteRole($id)
    {
        return $this->roleModel->delete($id);
    }

    public function getRole($id)
    {
        return $this->roleModel->find($id);
    }

    public function listRoles()
    {
        return $this->roleModel->findAll();
    }

    public function listRolesPaginated($perPage = 10)
    {
        return $this->roleModel->paginate($perPage);
    }

    public function getRolePager()
    {
        return $this->roleModel->pager;
    }

    // ===================== PERMISSION =====================
    public function createPermission($data)
    {
        return $this->permissionModel->insert($data);
    }

    public function updatePermission($id, $data)
    {
        return $this->permissionModel->update($id, $data);
    }

    public function deletePermission($id)
    {
        // Hapus sub-permission (anak) terlebih dahulu
        $this->permissionModel->where('parent_id', $id)->delete();
        return $this->permissionModel->delete($id);
    }

    public function getPermission($id)
    {
        return $this->permissionModel->find($id);
    }

    public function listPermissions()
    {
        return $this->permissionModel->findAll();
    }

    public function listPermissionsPaginated($perPage = 10)
    {
        return $this->permissionModel->paginate($perPage);
    }

    public function getPermissionPager()
    {
        return $this->permissionModel->pager;
    }

    public function listPermissionsTree($parentId = null)
    {
        return $this->permissionModel->getPermissionTree($parentId);
    }

    // ===================== ASSIGNMENT =====================
    public function assignRoleToUser($userId, $roleId)
    {
        return $this->userModel->assignRole($userId, $roleId);
    }

    public function assignRolesToUser($userId, array $roleIds)
    {
        return $this->userModel->assignRoles($userId, $roleIds);
    }

    public function getUserRoles($userId)
    {
        return $this->userModel->getRoles($userId);
    }

    public function getUserPermissions($userId)
    {
        return $this->userModel->getPermissions($userId);
    }

    public function assignPermissionsToRole($roleId, array $permissionIds)
    {
        return $this->roleModel->assignPermissions($roleId, $permissionIds);
    }

    public function getRolePermissions($roleId)
    {
        return $this->roleModel->getPermissions($roleId);
    }

    public function userHasPermission($userId, $permissionSlug)
    {
        return $this->userModel->hasPermission($userId, $permissionSlug);
    }
}
