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

    protected $validationRules = [
        'name' => 'required|max_length[100]|is_unique[roles.name]',
        'description' => 'max_length[255]',
    ];

    protected $validationMessages = [
        'name' => [
            'is_unique' => 'Nama role sudah digunakan, silakan pilih nama lain.'
        ]
    ];

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
        $deleteResult = $this->db->table('role_permissions')->where('role_id', $roleId)->delete();
        log_message('debug', '[RoleModel] assignPermissions: deleteResult=' . var_export($deleteResult, true));
        $afterDelete = $this->db->table('role_permissions')->where('role_id', $roleId)->countAllResults();
        log_message('debug', '[RoleModel] assignPermissions: afterDelete count=' . $afterDelete);
        if (!empty($permissionIds)) {
            // Ambil semua permission id yang valid
            $validIds = array_column($this->db->table('permissions')->select('id')->get()->getResultArray(), 'id');
            $permissionIds = array_intersect($permissionIds, $validIds);
            $batchData = [];
            foreach ($permissionIds as $permissionId) {
                $batchData[] = [
                    'role_id' => $roleId,
                    'permission_id' => (int)$permissionId
                ];
            }
            if (!empty($batchData)) {
                log_message('debug', '[RoleModel] assignPermissions: batchData=' . var_export($batchData, true));
                try {
                    $this->db->table('role_permissions')->insertBatch($batchData);
                } catch (\Exception $e) {
                    log_message('error', '[RoleModel] assignPermissions error: ' . $e->getMessage());
                    $this->db->transRollback();
                    session()->setFlashdata('error', 'DB Error: ' . $e->getMessage());
                    return false;
                }
            }
        }
        $this->db->transComplete();
        if (!$this->db->transStatus()) {
            $error = $this->db->error();
            log_message('error', '[RoleModel] assignPermissions DB error: ' . print_r($error, true));
            session()->setFlashdata('error', 'DB Error: ' . ($error['message'] ?? 'Unknown error'));
            return false;
        }
        return true;
    }

    public function revokePermission(int $roleId, int $permissionId): bool
    {
        return $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete() ? true : false;
    }
}