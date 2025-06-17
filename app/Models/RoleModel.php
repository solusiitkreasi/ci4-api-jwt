<?php 

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['name', 'description'];

    public function getPermissions(int $roleId): array
    {
        return $this->db->table('permissions p')
            ->join('role_permissions rp', 'rp.permission_id = p.id')
            ->where('rp.role_id', $roleId)
            ->get()->getResultArray();
    }

    public function assignPermission(int $roleId, int $permissionId): bool
    {
        try {
            $this->db->table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);
            return true;
        } catch (\Exception $e) {
            // Handle duplicate entry, etc.
            log_message('error', '[RoleModel] Assign Permission Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function assignPermissions(int $roleId, array $permissionIds): bool
    {
        $this->db->transStart();
        $this->db->table('role_permissions')->where('role_id', $roleId)->delete(); // Hapus yang lama dulu
        if (!empty($permissionIds)) {
            $batchData = [];
            foreach ($permissionIds as $permissionId) {
                $batchData[] = [
                    'role_id' => $roleId,
                    'permission_id' => (int)$permissionId
                ];
            }
            $this->db->table('role_permissions')->insertBatch($batchData);
        }
        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function revokePermission(int $roleId, int $permissionId): bool
    {
        return $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete() ? true : false;
    }
}