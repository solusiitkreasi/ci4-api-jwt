<?php
namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Services\RolePermissionService;
use App\Models\RoleModel;

class RoleController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new RolePermissionService();
    }

    // ===================== ROLE =====================
    public function index()
    {

        $data = [
            'title' => 'Manajemen Role',
        ];
        return view('backend/pages/role/list', $data);
    }

    public function datatables()
    {

        $request = $this->request;
        $model = new RoleModel();
        $columns = ['id', 'name', 'description', 'id'];
        $draw = (int) $request->getGet('draw');
        $start = (int) $request->getGet('start');
        $length = (int) $request->getGet('length');
        $search = $request->getGet('search')['value'] ?? '';
        $orderColIdx = (int) $request->getGet('order')[0]['column'] ?? 0;
        $orderCol = $columns[$orderColIdx] ?? 'id';
        $orderDir = $request->getGet('order')[0]['dir'] ?? 'asc';

        // Builder untuk total records (tanpa filter)
        $totalRecords = $model->countAll();

        // Builder untuk filtered records
        $filteredBuilder = $model;
        if ($search) {
            $filteredBuilder = $filteredBuilder->groupStart()
                ->like('name', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }
        $recordsFiltered = $filteredBuilder->countAllResults(false);

        // Ambil data dengan filter, order, dan limit
        $filteredBuilder = $filteredBuilder->orderBy($orderCol, $orderDir)
            ->limit($length, $start);
        $data = $filteredBuilder->findAll();

        $result = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => array_map(function($row) use (&$start) {
                static $i = 0;
                $no = $start + (++$i);
                $isSuperAdmin = strtolower($row['name']) === 'super admin';
                $editBtn = '<a href="'.base_url('backend/role/edit/'.$row['id']).'" class="btn btn-sm btn-warning">Edit</a> ';
                $deleteBtn = $isSuperAdmin ? '' : '<a href="'.base_url('backend/role/delete/'.$row['id']).'" class="btn btn-sm btn-danger btn-delete" data-id="'.$row['id'].'">Hapus</a> ';
                $permissionBtn = '<a href="'.base_url('backend/role/permissions/'.$row['id']).'" class="btn btn-sm btn-info">Permission</a>';
                return [
                    $no,
                    esc($row['name']),
                    esc($row['description'] ?? '-'),
                    $editBtn . $deleteBtn . $permissionBtn
                ];
            }, $data)
        ];
        return $this->response->setJSON($result);
    }

    public function create()
    {

        if (strtolower($this->request->getMethod()) === 'post') {
            $data = $this->request->getPost();
            if (!isset($data['id'])) {
                $data['id'] = null;
            }
            // Gunakan model yang sama agar error validasi bisa diambil
            $roleModel = new RoleModel();
            $result = $roleModel->insert($data);
            if ($result === false) {
                $validation = $roleModel->errors();
                return view('backend/pages/role/form', [
                    'validation' => $validation,
                    'role' => $data
                ]);
            }
            return redirect()->to('/backend/role')->with('success', 'Role berhasil ditambah');
        }
        return view('backend/pages/role/form');
    }

    public function edit($id)
    {

        $role = $this->service->getRole($id);
        if (strtolower($this->request->getMethod()) === 'post') {
            $data = $this->request->getPost();
            // Validasi unique name saat edit
            $roleModel = new RoleModel();
            $roleModel->setValidationRule('name', 'required|max_length[100]|is_unique[roles.name,id,'.$id.']');
            $roleModel->setValidationRule('description', 'max_length[255]');
            $data['id'] = $id;
            $result = $roleModel->update($id, $data);
            if ($result === false) {
                $validation = $roleModel->errors();
                return view('backend/pages/role/form', [
                    'validation' => $validation,
                    'role' => array_merge($role, $data)
                ]);
            }
            return redirect()->to('/backend/role')->with('success', 'Role berhasil diupdate');
        }
        return view('backend/pages/role/form', compact('role'));
    }

    public function delete($id)
    {

        $model = new RoleModel();
        $role = $model->find($id);
        if ($role && strtolower($role['name']) === 'super admin') {
            return redirect()->to('/backend/role')->with('error', 'Role Super Admin tidak boleh dihapus!');
        }else if($role && strtolower($role['name']) === 'client'){
            return redirect()->to('/backend/role')->with('error', 'Role Client tidak boleh dihapus!');
        }
        $this->service->deleteRole($id);
        return redirect()->to('/backend/role')->with('success', 'Role berhasil dihapus');
    }

    public function permissions($id)
    {
        $role = $this->service->getRole($id);
        if (!$role) {
            return redirect()->to('/backend/role')->with('error', 'Role tidak ditemukan.');
        }
        $permissions = $this->service->listPermissionsTree();
        $role_permission_ids = array_column($this->service->getRolePermissions($id), 'id');
        if (strtolower($this->request->getMethod()) === 'post') {
            $permissionIds = $this->request->getPost('permission_ids') ?? [];
            $permSequence = $this->request->getPost('perm_sequence') ?? [];
            // Simpan urutan dan parent_id jika ada perubahan
            if (!empty($permSequence)) {
                $permissionModel = new \App\Models\PermissionModel();
                foreach ($permSequence as $idPerm => $val) {
                    list($sequence, $parent_id) = explode('|', $val);
                    $permissionModel->update($idPerm, [
                        'sequence' => (int)$sequence,
                        'parent_id' => (int)$parent_id
                    ]);
                }
            }
            $result = $this->service->assignPermissionsToRole($id, $permissionIds);
            if ($result) {
                return redirect()->to('/backend/role')->with('success', 'Permission berhasil disimpan & urutan menu diupdate');
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan permission. Pastikan data valid dan tidak ada error database.');
            }
        }
        return view('backend/pages/role/permissions', compact('role', 'permissions', 'role_permission_ids'));
    }
}
