<?php namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $allowedFields = ['category_id', 'name','slug', 'description', 'price', 'stock'];
    protected $useTimestamps = true;

    // Join dengan kategori untuk tampilan
    public function getProductsWithCategory($id = null)
    {
        $builder = $this->db->table('products p');
        $builder->select('p.*, c.name as category_name, c.slug as category_slug');
        $builder->join('categories c', 'c.id = p.category_id');
        if ($id) {
            return $builder->where('c.slug', $id)->get()->getRowArray();
        }
        return $builder->get()->getResultArray();
    }

    public function getProductsWithSlug($id = null)
    {
        $builder = $this->db->table('products p');
        $builder->select('p.*, c.name as category_name, c.slug as category_slug');
        $builder->join('categories c', 'c.id = p.category_id');
        if ($id) {
            return $builder->where('p.slug', $id)->get()->getRowArray();
        }
        return $builder->get()->getResultArray();
    }
}