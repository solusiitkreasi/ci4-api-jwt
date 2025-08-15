<?php
namespace App\Controllers\Backend;
use App\Controllers\BaseController;
use App\Models\Master\LensaModel;

class LensaController extends BaseController
{
    public function datatables()
    {
        $request        = $this->request;
        $model          = new LensaModel();
        $start          = (int) $request->getGet('start');
        $length         = (int) $request->getGet('length');
        $search_custom  = $request->getGet('search_custom') ?? '';
        $kd_brand       = $request->getGet('kd_brand') ?? '';
        $jenis_lensa    = $request->getGet('jenis_lensa') ?? '';


        // Filter wajib: kode brand dan jenis lensa
        if (empty($kd_brand) || empty($jenis_lensa)) {
            return $this->response->setJSON([
                'draw' => (int) $request->getGet('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $builder =  $model->select('kode_lensa_5digit, nama_lensa_5digit,
                    jenis_lensa, kd_brand')
                ->whereIn('type_customer', array('I','A'))
                ->where('kd_brand', $kd_brand)
                ->where('jenis_lensa', $jenis_lensa)
                ->where('aktif', 1)
                ->where('hargaidp >', 0)
                ->where('kode_lensa_5digit !=', '');
        
        if ($search_custom) {
            $builder->groupStart()
                ->like('kode_lensa_5digit', '%'.$search_custom.'%')
                ->orLike('nama_lensa_5digit', '%'.$search_custom.'%')
                ->groupEnd();
        }

        // Hitung total data tanpa filter (untuk paging awal)
        $totalRecords = $model->countAllResults(false);

        // Hitung total data hasil filter/search (untuk paging hasil pencarian)
        $recordsFiltered = $builder->countAllResults(false);

        $builder->limit($length, $start);
        $data       = $builder->get()->getResultArray();

        $resultData = [];
        if($data){
            // tesx($data);
            foreach ($data as $row) {
                if($row['kode_lensa_5digit'] != ""){
                    $resultData[] = [
                        $row['kode_lensa_5digit'],
                        $row['nama_lensa_5digit'],
                        '<button type="button" class="btn btn-sm btn-success" onclick="selectLensa(\'' . esc($row['kode_lensa_5digit']) . '\',\'' . esc(addslashes($row['nama_lensa_5digit'])) . '\')">Pilih</button>'
                    ];
                }
            }
        }
        return $this->response->setJSON([
            'draw'              => (int) $request->getGet('draw'),
            'recordsTotal'      => $totalRecords,
            'recordsFiltered'   => $recordsFiltered,
            'data'              => $resultData
        ]);




    }

    public function getspheris()
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

        $query = $this->db->query($sql)->getResultArray();

        return $this->response->setJSON($query);
    }

    public function getcylinder()
    {

        $kode_lensa   = $this->request->getVar('kode_lensa');
        $kode_spheris = $this->request->getVar('kode_spheris');

        
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

        $query = $this->db->query($sql)->getResultArray();

        return $this->response->setJSON($query);
    }

    public function getaxis()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT DISTINCT nama_jpower as kode_axis
                    FROM db_tol.mst_jpower
                    WHERE jenis_jpower="A" AND aktif=1
                ORDER BY CAST(nama_jpower AS DECIMAL(6,2))
            ');
        $query = $sql->getResult();
        
        return $this->response->setJSON($query);
    }

    public function getadditional()
    {
        $db  = \Config\Database::connect('db_tol');
        $sql = $db->query('SELECT DISTINCT TRIM(nama_jpower) AS kode_additional
                FROM db_tol.mst_jpower 
                WHERE jenis_jpower="D" 
                AND aktif=1 
                ORDER BY CAST(nama_jpower AS DECIMAL(6,2))
            ');

        $query = $sql->getResult();
        
        return $this->response->setJSON($query);
    }

    public function getbase()
    {

        $log_base = array();
        for ($i=0; $i < 360 ; $i++) { 
               $i;
               array_push($log_base, $i);
        }
        $kodebase = $log_base;
        
        return $this->response->setJSON($kodebase);
    }

    public function getprisma()
    {

        $log_prisma = array();
        for ($i=0; $i < 16 ; $i++) { 
               $i;
               array_push($log_prisma, $i);
        }
        $kodeprisma = $log_prisma;

        // tesx($kodeprisma);
        
        return $this->response->setJSON($kodeprisma);
    }

    // ---- FREE FORM
    
}
