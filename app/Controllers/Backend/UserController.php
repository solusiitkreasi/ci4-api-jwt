<?php
namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;

class UserController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        // Ambil data group store dari db_tol
        $db_tol  = \Config\Database::connect('db_tol');
        $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group IS NOT NULL AND kode_group != '' ORDER BY nama_group ASC")->getResult();
        return view('backend/pages/user/list', [
            'group_customer' => $group_customer
        ]);
    }

    public function create()
    {
        $roleModel = new RoleModel();
        $allRoles = $roleModel->findAll();
        $db_tol  = \Config\Database::connect('db_tol');
        $userModel = new UserModel();
        $currentUserId = session()->get('user_id');
        $currentUserRoles = $userModel->getRoles($currentUserId);
        $isSuperAdmin = false;
        $isStorePi = false;
        $currentGroup = null;
        foreach ($currentUserRoles as $role) {
            $roleName = strtolower(trim($role['name']));
            if ($roleName === 'super admin') $isSuperAdmin = true;
            if ($roleName === 'admin') $isSuperAdmin = true;
            if ($roleName === 'store pic') $isStorePi = true;
        }
        if ($isStorePi) {
            $currentUser = $userModel->find($currentUserId);
            $currentGroup = $currentUser['kode_group'] ?? null;
        }
        if ($isSuperAdmin) {
            $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group IS NOT NULL AND kode_group != '' ORDER BY nama_group ASC")->getResult();
        } elseif ($isStorePi && $currentGroup) {
            $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group = ? ORDER BY nama_group ASC", [$currentGroup])->getResult();
        } else {
            $group_customer = [];
        }
        return view('backend/pages/user/form', [ 
            'allRoles' => $allRoles,
            'group_customer' => $group_customer
        ]);
    }

    public function store()
    {
        $validation =  \Config\Services::validation();

        $rules = [
            'group_store'   => 'required',
            'store'         => 'required',
            'name'          => 'required|min_length[3]',
            'email'         => 'required|valid_email|is_unique[users.email]',
            'roles'         => 'required',
            'password'      => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'kode_group' => $this->request->getPost('group_store'),
            'kode_customer' => $this->request->getPost('store'),
            'is_active' => $this->request->getPost('is_active'),
            'password' => $this->request->getPost('password'),
        ];
        $userModel->insert($data);
        $userId = $userModel->getInsertID();

        // Assign roles
        $roles = (array) $this->request->getPost('roles');
        if (!empty($roles)) {
            $userModel->assignRoles($userId, $roles);
        }

        // Jika role Store Pic dipilih dan yang login Super Admin/Admin, insert ke user_groups
        $roleModel = new RoleModel();
        $storePicRole = $roleModel->where('name', 'Store Pic')->first();
        $isStorePic = in_array($storePicRole['id'] ?? 0, $roles);
        $currentUserId = session()->get('user_id');
        $currentUserRoles = $userModel->getRoles($currentUserId);
        $isSuperAdmin = false;
        $isAdmin = false;
        foreach ($currentUserRoles as $role) {
            $roleName = strtolower(trim($role['name']));
            if ($roleName === 'super admin') $isSuperAdmin = true;
            if ($roleName === 'admin') $isAdmin = true;
        }
        if (($isSuperAdmin || $isAdmin) && $isStorePic) {
            $db = \Config\Database::connect();
            $exists = $db->table('user_groups')->where('user_id', $userId)->countAllResults();
            if ($exists == 0) {
                $db->table('user_groups')->insert([
                    'user_id' => $userId,
                    'kode_group' => $data['kode_group'],
                    'email' => $data['email'],
                    'is_aktif' => $data['is_active']
                ]);
            }
        }

        return redirect()->to('backend/user')->with('success', 'User berhasil ditambah.');
    }

    public function edit($id)
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $user = $userModel->find($id);
        $allRoles = $roleModel->findAll();
        $userRoles = $userModel->getRoles($id);
        $selectedRoles = array_column($userRoles, 'id');
        $db_tol  = \Config\Database::connect('db_tol');
        $currentUserId = session()->get('user_id');
        $currentUserRoles = $userModel->getRoles($currentUserId);
        $isSuperAdmin = false;
        $isStorePi = false;
        $currentGroup = null;
        foreach ($currentUserRoles as $role) {
            $roleName = strtolower(trim($role['name']));
            if ($roleName === 'super admin') $isSuperAdmin = true;
            if ($roleName === 'admin') $isSuperAdmin = true;
            if ($roleName === 'store pic') $isStorePi = true;
        }
        if ($isStorePi) {
            $currentUser = $userModel->find($currentUserId);
            $currentGroup = $currentUser['kode_group'] ?? null;
        }
        if ($isSuperAdmin) {
            $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group IS NOT NULL AND kode_group != '' ORDER BY nama_group ASC")->getResult();
        } elseif ($isStorePi && $currentGroup) {
            $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group = ? ORDER BY nama_group ASC", [$currentGroup])->getResult();
        } else {
            $group_customer = [];
        }
        $stores = [];
        if (!empty($user['kode_group'])) {
            $stores = $db_tol->query("SELECT * FROM db_tol.mst_customer WHERE group_customer = ?", [$user['kode_group']])->getResult();
        }
        return view('backend/pages/user/form', [ 
            'user' => $user, 
            'allRoles' => $allRoles, 
            'selectedRoles' => $selectedRoles,
            'group_customer' => $group_customer,
            'stores' => $stores,
            'errors' => session('errors')
        ]);
    }

    public function update($id)
    {
        $validation =  \Config\Services::validation();

        // Aturan is_unique untuk email harus mengabaikan ID user saat ini
        $rules = [
            'group_store'   => 'required',
            'store'         => 'required',
            'name'          => 'required|min_length[3]',
            'email'         => "required|valid_email|is_unique[users.email,id,{$id}]",
            'roles'         => 'required',
        ];

        // Validasi password hanya jika diisi
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'kode_group' => $this->request->getPost('group_store'),
            'kode_customer' => $this->request->getPost('store'),
            'is_active' => $this->request->getPost('is_active'),
        ];
        $password = $this->request->getPost('password');
        if ($password) {
            $data['password'] = $password;
        }
        // Update data user (tanpa kolom roles)
        $userModel->update($id, $data);

        // Cek role Store Pic SEBELUM update role
        $roleModel = new RoleModel();
        $storePicRole = $roleModel->where('name', 'Store Pic')->first();
        $roles = (array) $this->request->getPost('roles');
        $userRolesBefore = $userModel->getRoles($id);
        $wasStorePic = false;
        foreach ($userRolesBefore as $r) {
            if (strtolower(trim($r['name'])) === 'store pic') {
                $wasStorePic = true;
                break;
            }
        }
        // Update roles di tabel user_roles
        $userModel->assignRoles($id, $roles);

        $isStorePic = in_array($storePicRole['id'] ?? 0, $roles);
        $currentUserId = session()->get('user_id');
        $currentUserRoles = $userModel->getRoles($currentUserId);
        $isSuperAdmin = false;
        $isAdmin = false;
        foreach ($currentUserRoles as $role) {
            $roleName = strtolower(trim($role['name']));
            if ($roleName === 'super admin') $isSuperAdmin = true;
            if ($roleName === 'admin') $isAdmin = true;
        }
        $db = \Config\Database::connect();

        if (($isSuperAdmin || $isAdmin) && $isStorePic) {
            // Insert ke user_groups hanya jika user_id belum ada
            $exists = $db->table('user_groups')->where('user_id', $id)->countAllResults();
            if ($exists == 0) {
                $db->table('user_groups')->insert([
                    'user_id' => $id,
                    'kode_group' => $data['kode_group'],
                    'email' => $data['email'],
                    'is_aktif' => $data['is_active']
                ]);
            }

            
        } 

        if (($isSuperAdmin || $isAdmin) && !$isStorePic && $wasStorePic) {

            // Jika sebelumnya Store Pic lalu diganti, hapus dari user_groups berdasarkan email lama
            $db->table('user_groups')->where('user_id', $id)->delete();
        }


        // Kirim email hanya jika status aktif
        if ($data['is_active']) {
            helper('email');
            $subject = 'Akun Anda Telah Diupdate';
            $message = 'Halo ' . esc($data['name']) . ',<br>Akun Anda telah diupdate dan sekarang statusnya <b>Aktif</b>.';
            send_email($data['email'], $subject, $message);
        }

        return redirect()->to('/backend/user')->with('success', 'User berhasil diupdate.');
    }

    public function getStoresByGroup()
    {
        $group = $this->request->getPost('group');
        $db_tol  = \Config\Database::connect('db_tol');
        $customers = $db_tol->query("SELECT * FROM db_tol.mst_customer WHERE group_customer = ?", [$group])->getResult();
        
        // Siapkan data response
        $response = [
            'stores' => $customers,
            'csrf_hash' => csrf_hash() // Kirim kembali hash CSRF yang baru
        ];

        return $this->response->setJSON($response);
    }

    public function delete($id)
    {
        $userModel = new UserModel();
        $userModel->delete($id);
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User berhasil dihapus.'
            ]);
        } else {
            return redirect()->to('/backend/user')->with('success', 'User berhasil dihapus.');
        }
    }

    public function datatables()
    {
        $request = $this->request;
        $userModel = new UserModel();

        // 1. Query dasar untuk PENCARIAN dan PENGHITUNGAN
        $builder = $userModel->builder();
        $builder->select('id, name, email, is_active, kode_group');

        // Filter group dan store
        $filterGroup = $request->getGet('filter_group') ?? '';
        $filterStore = $request->getGet('filter_store') ?? '';
        $userModel = new UserModel();
        $currentUserId = session()->get('user_id');
        $currentUserRoles = $userModel->getRoles($currentUserId);
        $isSuperAdmin = false;
        $isStorePic = false;
        $currentGroup = null;
        foreach ($currentUserRoles as $role) {
            $roleName = strtolower(trim($role['name']));
            if ($roleName === 'super admin') $isSuperAdmin = true;
            if ($roleName === 'store pic') $isStorePic = true;
        }
        if ($isStorePic) {
            $currentUser = $userModel->find($currentUserId);
            $currentGroup = $currentUser['kode_group'] ?? null;
            $builder->where('kode_group', $currentGroup);
            if ($filterStore) {
                $builder->where('kode_customer', $filterStore);
            }
        } else {
            if ($filterGroup) {
                $builder->where('kode_group', $filterGroup);
            }
            if ($filterStore) {
                $builder->where('kode_customer', $filterStore);
            }
        }

        // Handle pencarian
        $searchValue = $request->getGet('search')['value'] ?? '';
        if ($searchValue) {
            $builder->groupStart();
            $builder->like('name', $searchValue);
            $builder->orLike('email', $searchValue);
            $builder->groupEnd();
        }

        // Dapatkan total record setelah filter (untuk pagination)
        $recordsFiltered = $builder->countAllResults(false);
        $totalRecords = $userModel->countAll();

        // 2. Query utama untuk mendapatkan DATA dengan SORTING dan PAGINATION
        $order = $request->getGet('order')[0] ?? [];
        if ($order) {
            $orderColumn = $request->getGet('columns')[$order['column']]['data'] ?? 'name';
            $builder->orderBy($orderColumn, $order['dir'] ?? 'asc');
        }

        $length = $request->getGet('length');
        $start = $request->getGet('start');
        if ($length !== -1) {
            $builder->limit($length, $start);
        }

        $users = $builder->get()->getResultArray();

        // 3. Ambil roles untuk user yang ditampilkan (setelah paginasi)
        $userIds = array_column($users, 'id');
        $rolesByUserId = [];
        if (!empty($userIds)) {
            $rolesData = $userModel->db->table('roles r')
                ->select('r.name, ur.user_id')
                ->join('user_roles ur', 'ur.role_id = r.id')
                ->whereIn('ur.user_id', $userIds)
                ->get()->getResultArray();

            foreach ($rolesData as $role) {
                $rolesByUserId[$role['user_id']][] = $role['name'];
            }
        }

        // Jika yang login adalah Admin, filter user yang punya role Super Admin
        $isAdmin = false;
        foreach ($currentUserRoles as $role) {
            if (strtolower(trim($role['name'])) === 'admin') {
                $isAdmin = true;
                break;
            }
        }

        $data = [];
        $no = $start + 1;
        foreach ($users as $user) {
            $userRoles = $rolesByUserId[$user['id']] ?? [];
            if ($isAdmin && in_array('Super Admin', $userRoles)) {
                continue; // Jangan tampilkan user dengan role Super Admin
            }
            $isStorePic = false;
            foreach ($currentUserRoles as $role) {
                if (strtolower(trim($role['name'])) === 'store pic') {
                    $isStorePic = true;
                    break;
                }
            }
            $actionBtn = '<a href="' . base_url('backend/user/edit/' . $user['id']) . '" class="btn btn-sm btn-warning">Edit</a> ';
            if ($isSuperAdmin) {
                $actionBtn .= '<button class="btn btn-sm btn-danger btn-delete-user" data-id="' . $user['id'] . '" data-name="' . esc($user['name']) . '">Hapus</button>';
            }
            $data[] = [
                'no' => $no++,
                'name' => esc($user['name']),
                'email' => esc($user['email']),
                'roles' => esc(implode(' | ', $userRoles)),
                'is_active' => $user['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>',
                'action' => $actionBtn
            ];
        }

        return $this->response->setJSON([
            'draw' => $request->getGet('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}

