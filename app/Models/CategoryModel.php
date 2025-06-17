<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table            = 'categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['name', 'slug'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation (bisa juga didefinisikan di controller)
    protected $validationRules      = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[categories.name,id,{id}]',
        'slug' => 'required|min_length[3]|max_length[120]|alpha_dash|is_unique[categories.slug,id,{id}]'
    ];
    protected $validationMessages   = [
        'name' => [
            'is_unique' => 'Sorry. That category name has already been taken. Please choose another.',
        ],
        'slug' => [
            'is_unique' => 'Sorry. That slug has already been taken. Please choose another.',
            'alpha_dash' => 'Slug can only contain alpha-numeric characters, dashes, or underscores.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}