<?php 

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $allowedFields    = ['name', 'slug', 'description', 'parent_id', 'sequence', 'menu_on', 'link', 'icon'];

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'slug' => 'required|max_length[100]|is_unique[permissions.slug]|alpha_dash',
        'description' => 'max_length[255]',
    ];

    protected $validationMessages = [
        'slug' => [
            'is_unique' => 'Slug permission sudah digunakan, silakan pilih slug lain.'
        ]
    ];

    /**
     * Ambil semua permission dalam bentuk tree, terurut sequence.
     */
    public function getPermissionTree($parentId = null)
    {
        $builder = $this->builder();
        $builder->orderBy('sequence', 'ASC');
        $permissions = $builder->get()->getResultArray();
        return $this->buildTree($permissions, $parentId);
    }

    private function buildTree($elements, $parentId = null)
    {
        $branch = [];
        foreach ($elements as $element) {
            // Perbandingan lebih fleksibel: null, 0, '', '0', int 0
            if (empty($element['parent_id']) && empty($parentId) || $element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}