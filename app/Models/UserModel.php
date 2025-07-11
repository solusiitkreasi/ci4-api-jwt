<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Atau 'object'
    protected $useSoftDeletes   = false; // Jika Anda ingin soft delete, set true dan tambahkan 'deleted_at'
    // protected $allowedFields    = ['name', 'kode_customer',  'email', 'password', 'role', 'api_key', 'reset_token', 'reset_expires'];

    protected $allowedFields    = ['kode_group','kode_customer','name', 'email', 'password', 'api_key', 'reset_token', 'reset_expires', 'is_active'];


    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Untuk soft deletes

    // Validation (bisa didefinisikan di sini atau di controller)
    // protected $validationRules      = [];
    // protected $validationMessages   = [];
    // protected $skipValidation       = false;
    // protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Mengambil semua pengguna beserta peran mereka secara efisien untuk menghindari N+1 query.
     * @return array Daftar pengguna dengan array 'roles' di dalamnya.
     */
    public function getUsersWithRoles(): array
    {
        // 1. Ambil semua pengguna
        $users = $this->findAll();
        if (empty($users)) {
            return [];
        }

        // 2. Kumpulkan semua ID pengguna
        $userIds = array_column($users, 'id');

        // 3. Ambil semua role untuk ID pengguna tersebut dalam satu query
        $rolesData = $this->db->table('roles r')
            ->select('r.name, ur.user_id')
            ->join('user_roles ur', 'ur.role_id = r.id')
            ->whereIn('ur.user_id', $userIds)
            ->get()
            ->getResultArray();

        // 4. Petakan peran ke setiap ID pengguna untuk pencarian cepat
        $rolesByUserId = [];
        foreach ($rolesData as $role) {
            $rolesByUserId[$role['user_id']][] = $role['name'];
        }

        // 5. Gabungkan data peran ke dalam data pengguna
        foreach ($users as &$user) {
            $user['roles'] = $rolesByUserId[$user['id']] ?? [];
        }

        return $users;
    }

    // Start New Update Role & Permission
    public function getRoles(int $userId): array
    {
        return $this->db->table('roles r')
            ->join('user_roles ur', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->get()->getResultArray();
    }

    public function assignRole(int $userId, int $roleId): bool
    {
        try {
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
            return true;
        } catch (\Exception $e) {
            log_message('error', '[UserModel] Assign Role Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function assignRoles(int $userId, array $roleIds): bool
    {
        $this->db->transStart();
        $this->db->table('user_roles')->where('user_id', $userId)->delete(); // Hapus yang lama dulu
        if (!empty($roleIds)) {
            $batchData = [];
            foreach ($roleIds as $roleId) {
                $batchData[] = [
                    'user_id' => $userId,
                    'role_id' => (int)$roleId
                ];
            }
            $this->db->table('user_roles')->insertBatch($batchData);
        }
        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function revokeRole(int $userId, int $roleId): bool
    {
        return $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete() ? true : false;
    }

    public function getPermissions(int $userId): array
    {
        // Query untuk mendapatkan semua permission slug unik untuk seorang user
        $builder = $this->db->table('user_roles ur');
        $builder->select('p.slug');
        $builder->join('role_permissions rp', 'rp.role_id = ur.role_id');
        $builder->join('permissions p', 'p.id = rp.permission_id');
        $builder->where('ur.user_id', $userId);
        
        $results = $builder->get()->getResultArray();

        
        return array_column($results, 'slug'); // Mengembalikan array slug permission
    }

    public function hasPermission(int $userId, string $permissionSlug): bool
    {
        $permissions = $this->getPermissions($userId);
        return in_array($permissionSlug, $permissions);
    }
    // End New Update Role & Permission



    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    public function findByResetToken(string $token)
    {
        return $this->where('reset_token', $token)
                    ->where('reset_expires >', date('Y-m-d H:i:s'))
                    ->first();
    }
}