<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\API\ResponseTrait;

class PublicController extends BaseController
{
    use ResponseTrait;
    protected $categoryModel;
    protected $productModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->productModel = new ProductModel();
        helper('response');
    }

    public function getCategories()
    {
        // Contoh: Cek permission API Key jika dikonfigurasi di filter
        // $apiKeyData = $this->request->apiKeyData; // Diset oleh ApiKeyAuthFilter
        // $permissions = json_decode($apiKeyData['permissions'] ?? '[]', true);
        // if (!in_array('read_categories', $permissions)) {
        //     return api_error('API Key does not have permission to read categories', 403);
        // }
        
        $categories = $this->categoryModel->findAll();
        return api_response($categories, 'Categories fetched successfully');
    }

    public function getCategory($id = null)
    {
        $category = $this->categoryModel->find($id);
        if (!$category) {
            return api_error('Category not found', 404);
        }
        return api_response($category, 'Category fetched successfully');
    }

    public function getProducts()
    {
        // Pagination example
        $page       = $this->request->getGet('page') ?? 1;
        $perPage    = $this->request->getGet('perPage') ?? 10;
        
        // Filtering example (by category_id)
        $categoryId = $this->request->getGet('category_id');
        
        $products   = $this->productModel
                        ->select('products.*, c.name as category_name, c.slug as category_slug')
                        ->join('categories c', 'c.id = products.category_id','left');

        if ($categoryId) {
            $products = $this->productModel->where('products.category_id', $categoryId);
        }

        // $products = $this->productModel->findAll();

        // tesx($this->productModel->getLastQuery() );
        
        $products   = $this->productModel->paginate($perPage, 'default', $page);
        
        $pager      = $this->productModel->pager;

        $data = [
            'products'          => $products,
            'pagination'        => [
                'total'         => $pager->getTotal(),
                'perPage'       => $pager->getPerPage(),
                'currentPage'   => $pager->getCurrentPage(),
                'lastPage'      => $pager->getLastPage(),
            ]
        ];
        return api_response($data, 'Products fetched successfully');
    }

    public function getProduct($id = null)
    {
        // $product = $this->productModel->getProductsWithCategory($id); // Menggunakan method join dari model
        
        $product = $this->productModel->getProductsWithSlug($id);
        if (!$product) {
            return api_error('Product not found', 404);
        }
        return api_response($product, 'Product fetched successfully');
    }
}