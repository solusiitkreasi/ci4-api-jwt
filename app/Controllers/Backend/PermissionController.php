<?php
namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Services\RolePermissionService;
use App\Models\PermissionModel;

class PermissionController extends BaseController
{
    protected $service;

    public function __construct()
    {
        $this->service = new RolePermissionService();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Permission',
        ];
        return view('backend/pages/permission/list', $data);
    }

    public function create()
    {
        if (strtolower($this->request->getMethod()) === 'post') {
            $data = $this->request->getPost();
            $permissionModel = new PermissionModel();
            $isParent = ($data['tipe_permission'] ?? 'parent') === 'parent';
            $isSub = ($data['tipe_permission'] ?? '') === 'sub';
            $isPermission = ($data['tipe_permission'] ?? '') === 'permission';
            $menu_on = isset($data['menu_on']) ? 1 : 0;
            $parent_id = null;
            if ($isSub) {
                $parent_id = $data['parent_id'] ?? null;
            } elseif ($isPermission) {
                $parent_id = $data['parent_id'] ?? null;
            }
            $insertData = [
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'parent_id' => $isParent ? 0 : $parent_id,
                'menu_on' => $menu_on,
                'link' => strtolower($data['name']),
            ];
            $result = $permissionModel->insert($insertData);
            if ($result === false) {
                $validation = $permissionModel->errors();
                return view('backend/pages/permission/form', [
                    'validation' => $validation,
                    'permission' => $data
                ]);
            }
            // Jika SUBMENU, generate sub-permission otomatis
            if ($isSub) {
                $submenuId = $permissionModel->getInsertID();
                $submenuSlug = $data['slug'];
                $submenuName = $data['name'];
                $subPermissions = [
                    [
                        'name' => $submenuName.' Datatables',
                        'slug' => $submenuSlug.'-datatables',
                        'description' => 'Akses datatables '.$submenuName,
                        'parent_id' => $submenuId,
                        'menu_on' => 0,
                        'link' => strtolower($submenuName).'/datatables',
                    ],
                    [
                        'name' => $submenuName.' Create',
                        'slug' => $submenuSlug.'-create',
                        'description' => 'Akses tambah '.$submenuName,
                        'parent_id' => $submenuId,
                        'menu_on' => 0,
                        'link' => strtolower($submenuName).'/create',
                    ],
                    [
                        'name' => $submenuName.' Details',
                        'slug' => $submenuSlug.'-details',
                        'description' => 'Akses details '.$submenuName,
                        'parent_id' => $submenuId,
                        'menu_on' => 0,
                        'link' => strtolower($submenuName).'/details',
                    ],
                    [
                        'name' => $submenuName.' Edit',
                        'slug' => $submenuSlug.'-edit',
                        'description' => 'Akses edit '.$submenuName,
                        'parent_id' => $submenuId,
                        'menu_on' => 0,
                        'link' => strtolower($submenuName).'/edit',
                    ],
                    [
                        'name' => $submenuName.' Delete',
                        'slug' => $submenuSlug.'-delete',
                        'description' => 'Akses delete '.$submenuName,
                        'parent_id' => $submenuId,
                        'menu_on' => 0,
                        'link' => strtolower($submenuName).'/delete',
                    ],
                ];
                foreach ($subPermissions as $sub) {
                    $permissionModel->insert($sub);
                }
            }
            return redirect()->to('/backend/permission')->with('success', 'Permission berhasil ditambah');
        }
        return view('backend/pages/permission/form');
    }

    public function edit($id)
    {
        $permission = $this->service->getPermission($id);
        if (strtolower($this->request->getMethod()) === 'post') {
            $data = $this->request->getPost();
            // Validasi unique slug saat edit
            $permissionModel = new PermissionModel();
            $permissionModel->setValidationRule('name', 'required|max_length[100]');
            $permissionModel->setValidationRule('slug', 'required|max_length[100]|is_unique[permissions.slug,id,'.$id.']|alpha_dash');
            $permissionModel->setValidationRule('description', 'max_length[255]');
            $data['id'] = $id;
            $result = $permissionModel->update($id, $data);
            if ($result === false) {
                $validation = $permissionModel->errors();
                return view('backend/pages/permission/form', [
                    'validation' => $validation,
                    'permission' => array_merge($permission, $data)
                ]);
            }
            return redirect()->to('/backend/permission')->with('success', 'Permission berhasil diupdate');
        }
        return view('backend/pages/permission/form', compact('permission'));
    }

    public function delete($id)
    {
        $this->service->deletePermission($id);
        return redirect()->to('/backend/permission')->with('success', 'Permission berhasil dihapus');
    }

    public function datatables()
    {
        $request        = $this->request;
        $model          = new PermissionModel();
        $columns        = ['id', 'name', 'slug', 'description', 'id'];
        $draw           = (int) $request->getGet('draw');
        $start          = (int) $request->getGet('start');
        $length         = (int) $request->getGet('length');
        $search         = $request->getGet('search')['value'] ?? '';
        $orderColIdx    = (int) $request->getGet('order')[0]['column'] ?? 0;
        $orderCol       = $columns[$orderColIdx] ?? 'id';
        $orderDir       = $request->getGet('order')[0]['dir'] ?? 'asc';

        // Builder untuk total records (tanpa filter)
        $totalRecords   = $model->countAll();

        // Builder untuk filtered records
        $filteredBuilder = $model;
        if ($search) {
            $filteredBuilder = $filteredBuilder->groupStart()
                ->like('name', $search)
                ->orLike('slug', $search)
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
                return [
                    $no,
                    esc($row['name']),
                    esc($row['slug']),
                    esc($row['description'] ?? '-'),
                    '<a href="'.base_url('backend/permission/edit/'.$row['id']).'" class="btn btn-sm btn-warning">Edit</a> '
                    .'<a href="'.base_url('backend/permission/delete/'.$row['id']).'" class="btn btn-sm btn-danger" onclick="return confirm(\'Yakin hapus permission ini?\')">Hapus</a>'
                ];
            }, $data)
        ];
        return $this->response->setJSON($result);
    }
}
