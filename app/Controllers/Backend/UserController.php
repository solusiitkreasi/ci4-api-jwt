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
        // Panggil method baru yang efisien untuk mengambil user beserta roles-nya
        $users = $userModel->getUsersWithRoles();
        //, [ 'users' => $users ]
        return view('backend/pages/user/list');
    }

    public function create()
    {
        $roleModel = new RoleModel();
        $allRoles = $roleModel->findAll();

        // Ambil data group store dari db_tol
        $db_tol  = \Config\Database::connect('db_tol');
        $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group IS NOT NULL AND kode_group != '' ORDER BY nama_group ASC")->getResult();

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

        return redirect()->to('backend/user')->with('success', 'User berhasil ditambah.');
    }

    public function edit($id)
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $user = $userModel->find($id);

        // Ambil semua role dari tabel roles
        $allRoles = $roleModel->findAll();

        // Ambil role user dari tabel user_roles
        $userRoles = $userModel->getRoles($id);
        $selectedRoles = array_column($userRoles, 'id');

        // Ambil data group store dari db_tol
        $db_tol  = \Config\Database::connect('db_tol');
        $group_customer = $db_tol->query("SELECT * FROM db_tol.mst_group_customer WHERE aktif = 1 AND kode_group IS NOT NULL AND kode_group != '' ORDER BY nama_group ASC")->getResult();

        // Ambil data store untuk group yang sudah dipilih user
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
            'errors' => session('errors') // Ambil error validasi dari session
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

        // Update roles di tabel user_roles
        $roles = (array) $this->request->getPost('roles');
        $userModel->assignRoles($id, $roles);

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
        return redirect()->to('/backend/user')->with('success', 'User berhasil dihapus.');
    }

    public function datatables()
    {
        $request = $this->request;
        $userModel = new UserModel();

        // 1. Query dasar untuk PENCARIAN dan PENGHITUNGAN
        $builder = $userModel->builder();
        $builder->select('id, name, email, is_active');

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

        // 4. Format data untuk response DataTables
        $data = [];
        $no = $start + 1;
        foreach ($users as $user) {
            // Ambil data role untuk user ini dari map, atau array kosong jika tidak ada
            $userRoles = $rolesByUserId[$user['id']] ?? [];

            $data[] = [
                'no' => $no++,
                'name' => esc($user['name']),
                'email' => esc($user['email']),
                'roles' => esc(implode(' | ', $userRoles)), // Gabungkan array role menjadi string
                'is_active' => $user['is_active'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Nonaktif</span>',
                'action' => '<a href="' . base_url('backend/user/edit/' . $user['id']) . '" class="btn btn-sm btn-warning">Edit</a> ' .
                            '<a href="' . base_url('backend/user/delete/' . $user['id']) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Yakin hapus user ini?\')">Hapus</a>'
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

