<?php 

namespace App\Controllers\Api\Client;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

use App\Models\Master\LensaModel;

class MasterController extends BaseController
{
    protected $lensaModel;

    public function __construct()
    {
        $this->lensaModel = new LensaModel();
        helper(['response', 'form']);
        $this->validator = \Config\Services::validation();
    }

    public function getKodeBrand()
    {
        // $kodebrand = $this->masterModel->findAll();

        $kodebrand = [
            'CZ' => 'Carl Zeiss', 
            'HB' => 'House Brand', 
            'SC' => 'Synchroni'
        ];
        return api_response($kodebrand, 'Kode Brand fetched successfully');
    }

    public function getGroupLensa()
    {
        // $grouplensa = $this->masterModel->findAll();

        $grouplensa = [
            'BF'    => 'BIFOCAL', 
            'OL'    => 'OFFICELENS', 
            'PG'    => 'PROGRESSIVE',
            'PF'    => 'PROGRESSIVE FREE FORM',
            'SV'    => 'SINGLE VISION',
            'SF'    => 'SINGLE VISION FREE FORM'
        ];
        return api_response($grouplensa, 'Group Lensa fetched successfully');
    }

    public function getLensa()
    {
        // Query Parameter 
        $kodebrand      = $this->request->getVar('kode_brand');
        $jenis_lensa    = $this->request->getVar('jenis_lensa'); 

        if (!$kodebrand){
            return api_error('Kode Brand not found, select kode brand before.', 404);
        }

        if (!$jenis_lensa) {
            return api_error('Kode Group Lensa not found, select kode group lensa before.', 404);
        }

        // Pagination
        $page       = $this->request->getGet('page') ?? 1;
        $perPage    = $this->request->getGet('perPage') ?? 10;
        
        // Get data from model
        $lensa = $this->lensaModel
                ->select('kode_lensa_5digit as kode_lensa, nama_lensa_5digit as nama_lensa,
                        jenis_lensa, kd_brand')
                ->whereIn('type_customer', array('I','A'));


        // Filtering (by id)
        if ($kodebrand) {
            $lensa = $this->lensaModel->where('kd_brand', $kodebrand);
        }

        if ($jenis_lensa) {
            $lensa = $this->lensaModel->where('jenis_lensa', $jenis_lensa);
        }

        // $products = $this->productModel->findAll();
        // tesx($this->productModel->getLastQuery() );
        
        $lensa      = $this->lensaModel->paginate($perPage, 'default', $page);
        $pager      = $this->lensaModel->pager;

        $data = [
            'lensa'             => $lensa,
            'pagination'        => [
                'total'         => $pager->getTotal(),
                'perPage'       => $pager->getPerPage(),
                'currentPage'   => $pager->getCurrentPage(),
                'lastPage'      => $pager->getLastPage(),
            ]
        ];

        return api_response($data, 'Data Lensa fetched successfully');
    }

    public function getSpheris()
    {
        
        $this->db = \Config\Database::connect('db_tol');
        // Get data from model
        $sql = "SELECT DISTINCT TRIM(nama_jpower) AS kode_spheris
                FROM db_tol.mst_jpower
                WHERE jenis_jpower = 'S' AND aktif = 1
                AND CAST(nama_jpower AS DECIMAL(6,2)) < 0
            UNION ALL
                SELECT '0.00' AS kode_spheris
            UNION ALL
                SELECT DISTINCT TRIM(nama_jpower) AS kode_spheris
                FROM db_tol.mst_jpower
                WHERE jenis_jpower = 'S' AND aktif = 1
                AND CAST(nama_jpower AS DECIMAL(6,2)) > 0
            ORDER BY CAST(kode_spheris AS DECIMAL(6,2)) DESC
        ";

        $query = $this->db->query($sql)->getResultArray(); // atau getResultArray()

        return api_response($query, 'Data Spheris fetched successfully');
    }

    public function getCylinder()
    {

        $kode_lensa   = $this->request->getVar('kode_lensa'); //'68484'; 
        $kode_spheris = $this->request->getVar('kode_spheris');

        if (!$kode_lensa){
            return api_error('Kode Cylinder not found, select kode lensa before.', 404);
        }

        if (!$kode_spheris) {
            return api_error('Kode Cylinder not found, select kode spheris before.', 404);
        }
        
        $this->db = \Config\Database::connect('db_tol');
        // Get data from model
        $sql = "SELECT kode_jcylinder AS kode_cylinder 
            FROM 
                db_tol.mst_over_power 
            WHERE 
                kode_lensa_5digit='$kode_lensa' 
                AND kode_jspheris='$kode_spheris' 
                AND aktif='1'
        ";

        $query = $this->db->query($sql)->getResultArray(); // atau getResultArray()

        return api_response($query, 'Data Cylinder fetched successfully');
    }

    public function getCylinderWithPaging()
    {
        // 1. Inisialisasi
        $db = \Config\Database::connect('db_tol');
        $pager = \Config\Services::pager();

        // 2. Ambil halaman saat ini
        // $page = (int)($this->request->getVar('page') ?? 1);
        $page       = (int)$this->request->getGet('page') ?? 1;
        
        // 3. Tentukan item per halaman
        $perPage    = (int)$this->request->getGet('perPage') ?? 10;
      
        // 4. Hitung offset
        $offset = ($page - 1) * $perPage;
        
        // Parameter untuk query
        // $kode_lensa_5digit  ='68484';
        // $kode_jspheris      ='-0.50';

        $kode_lensa   = $this->request->getVar('kode_lensa'); //'68484'; 
        $kode_spheris = $this->request->getVar('kode_spheris');

        // 5. Query untuk MENGAMBIL DATA PER HALAMAN
        $dataQuery = $db->query("SELECT kode_lensa_5digit,kode_jspheris,kode_jcylinder AS kode_cyl 
            FROM  db_tol.mst_over_power 
            WHERE kode_lensa_5digit=? AND kode_jspheris='$kode_jspheris'
            AND aktif='1' 
            ORDER BY kode_cyl ASC
            LIMIT ? OFFSET ?
        ", [$kode_lensa_5digit, $perPage, $offset]);

        // 6. Query untuk TOTAL DATA
        $totalQuery = $db->query(" SELECT COUNT(kode_lensa_5digit) as total, 
            kode_lensa_5digit,kode_jspheris,kode_jcylinder AS kode_cyl 
            FROM  db_tol.mst_over_power 
            WHERE kode_lensa_5digit=? AND kode_jspheris='$kode_jspheris'
            AND aktif='1' 
        ", [$kode_lensa_5digit]);
       
        $cylinder = $dataQuery->getResult();

        $total = $totalQuery->getRow()->total;

        // $total = $dataQuery->getNumRows();

        // 7. Buat link paginasi
        // $pager_links = 
        $pager->makeLinks($page, $perPage, $total, 'default_full');

        // 8. Kirim data
        $data = [
            'cylinder'          => $cylinder,
            'pagination'        => [
                'total'         => $total,
                'perPage'       => $perPage,
                'currentPage'   => $pager->getCurrentPage(),
                'lastPage'      => $pager->getLastPage(),
            ]
        ];

        return api_response($data, 'Data Cylinder fetched successfully');
    }

    public function getAxis()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT DISTINCT nama_jpower as kode_axis
                    FROM db_tol.mst_jpower
                    WHERE jenis_jpower="A" AND aktif=1
                ORDER BY CAST(nama_jpower AS DECIMAL(6,2))
            ');

        $getAxis = $sql->getResult();
        
        return api_response($getAxis, 'Data Axis fetched successfully');
    }

    public function getAdditional()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT DISTINCT TRIM(nama_jpower) AS kode_additional
                FROM db_tol.mst_jpower 
                WHERE jenis_jpower="D" 
                AND aktif=1 
                ORDER BY CAST(nama_jpower AS DECIMAL(6,2))
            ');

        $getAdditional = $sql->getResult();
        
        return api_response($getAdditional, 'Data Additional fetched successfully');
    }

    public function getModel()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT * FROM db_tol.mst_model');

        $getModel = $sql->getResult();

        $dataWithBase64 = [];
        foreach ($getModel as $model) {
            // Misalkan 'gambar' adalah kolom BLOB yang berisi data biner
            if (!empty($model->gambar)) {
                // Encode data biner menjadi string Base64
                $model->gambar_m = "data:image/png;base64,".base64_encode($model->gambar);
            } else {
                $model->gambar_m = null;
            }
            // Hapus field data biner asli sebelum dikirim, dan nama field baru gambar_m
            unset($model->gambar); 
            
            $dataWithBase64[] = $model;
        }
        
        return api_response($dataWithBase64, 'Data Model fetched successfully');
    }

    public function getJasa()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT kode_jasa, nama_jasa 
                            FROM db_tol.mst_jjasa 
                            WHERE kode_jasa<>"" AND aktif=1 ');

        $getJasa = $sql->getResult();
        
        return api_response($getJasa, 'Data Jasa fetched successfully');
    }

    public function getBase()
    {

        $log_base = array();
        for ($i=0; $i < 16 ; $i++) { 
               $i;
               array_push($log_base, $i);
        }
        $kodebase = $log_base;

        return api_response($kodebase, 'Data Base fetched successfully');
    }

    public function getPrisma()
    {

        $log_prisma = array();
        for ($i=0; $i < 400 ; $i++) { 
               $i;
               array_push($log_prisma, $i);
        }
        $kodeprisma = $log_prisma;

        return api_response($kodeprisma, 'Data Prisma fetched successfully');
    }

}