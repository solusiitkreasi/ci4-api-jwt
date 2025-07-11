<?php 

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model 
{

    protected $tableName        = 'permissions';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name','slug', 'description', 'display_name', 'parent_id','sequence','link','icon','menu_on'];
    protected $useTimestamps    = true;


    function get_items() {
        $query = $this->db->table('permissions')
                ->where('parent_id', '0')
                ->where('menu_on', '1')
                ->orderBy('parent_id ASC, sequence ASC')
                ->get();

        // die(nl2br( $this->db->getLastQuery() ));
        // die(nl2br($query->getResultArray()));
        // tesx($query->getResultArray());
        return $query->getResultArray();
    }

    function get_parent($id){
        $query = $this->db->table('permissions')
                ->where('id', $id)
                ->orderBy('sequence ASC')
                ->get();

        // die(nl2br( $this->db->getLastQuery() ));
        // die(nl2br($quer->getRowArray()));
        // tesx($query->getRowArray());
        return $query->getRowArray();

    }

    function get_sub($id) {

        $quer = $this->db->table('permissions')
                ->where('parent_id', $id)
                ->where('menu_on', '1')
                ->orderBy('sequence ASC')
                ->get();
        return $quer->getResultArray();

    }

    function get_sub_count($id) {

        $query = $this->db->table('permissions')
                ->where('parent_id', $id)
                ->orderBy('sequence ASC')
                ->get()->getNumRows();

        return $query;

    }

    // function get_permission() {
    //     $this->db->select('*');
    //     $this->db->from('permission');
    //     $query = $this->db->get();
    //     // die(nl2br($this->db->last_query()));
    //     return $query->result_array();
    // }


    function get_permission_roles($roleIds) {
        // Ambil slug permission dari semua role user (array)
        if (!is_array($roleIds)) {
            $roleIds = [$roleIds];
        }
        $query = $this->db->table('role_permissions rp')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->whereIn('rp.role_id', $roleIds)
            ->select('p.slug')
            ->get();
        $result = $query->getResultArray();
        return array_unique(array_column($result, 'slug'));
    }


    function generateTree($parent_id = 0)
    {
        $currentURI = service('request')->getUri();
        $currentPath = trim($currentURI->getPath(), '/');
        $segments = $currentURI->getSegments();
        $seg2 = $segments[1] ?? '';
        $items = $this->get_items();
        $user_session = session()->get('role_id');
        // Pastikan $user_session selalu array dan tidak kosong
        if (empty($user_session)) {
            $permission_slugs = [];
        } else {
            $roleIds = is_array($user_session) ? $user_session : [$user_session];
            $permission_slugs = $this->get_permission_roles($roleIds);
        }
        $tree = '<ul id="menu" class="menu">';
        for($i=0, $ni=count($items); $i < $ni; $i++){
            $parent = $items[$i];
            // Cek permission parent
            $parentPerm = $parent['slug'] ?? null;
            $showParent = in_array($parentPerm, $permission_slugs);
            $subtree = $this->get_sub($parent['id']);
            $showSub = false;
            if($subtree) {
                foreach($subtree as $sub) {
                    if (in_array($sub['slug'], $permission_slugs)) {
                        $showSub = true;
                        break;
                    }
                }
            }
            // Tambahkan pengecekan menu_on parent
            if((!$showParent && !$showSub) || empty($parent['menu_on']) || $parent['menu_on'] != '1') continue;
            $pId = $parent['id'];
            $parents_sub = $this->get_sub_count($pId);
            $parent_link = base_url('backend/'.$parent['link']);
            $isActiveParent = ($seg2 === $parent['link'] && $currentPath === 'backend/'.$parent['link']);
            $isActiveSub = false;
            if($subtree) {
                foreach($subtree as $sub){
                    $sub_link_segment = explode('/', $sub['link'])[0];
                    if($seg2 === $sub_link_segment) {
                        $isActiveSub = true;
                        break;
                    }
                }
            }
            $liClass = ($isActiveParent || $isActiveSub) ? 'open show' : '';
            if($isActiveParent) $liClass = 'active open show';
            $aClass = $isActiveParent ? 'active' : '';
            $tree .= '<li class="'.$liClass.'">';
            if($parents_sub == 0){
                $tree .= '<a href="'.$parent_link .'" class="'.$aClass.'">
                        <i data-acorn-icon="'.$parent['icon'].'" class="icon" data-acorn-size="18"></i>';
            }else{
                $tree .= '<a href="#menu-'.$parent['id'].'" class="'.$aClass.'" data-href="'.$parent_link .'" >
                        <i data-acorn-icon="'.$parent['icon'].'" class="icon" data-acorn-size="18"></i>';
            }
            $tree .= '<span class="label">'.$parent['description'].'</span></a>';
            if($subtree) {
                $ulClass = ($isActiveParent || $isActiveSub) ? 'style="display:block"' : '';
                $tree .= '<ul id="menu-'.$parent['id'].'" '.$ulClass.'>';
                foreach($subtree as $sub){
                    if (!in_array($sub['slug'], $permission_slugs) || empty($sub['menu_on']) || $sub['menu_on'] != '1') continue;
                    $sub_link = base_url('backend/'.$sub['link']);
                    $subPath = 'backend/'.$sub['link'];
                    $active = (strpos($currentPath, $subPath) === 0) ? 'active' : '';
                    $tree .= '<li><a href="'.$sub_link.'" class="'.$active.'">';
                    $tree .= '<span class="label">'.$sub['description'].'</span></a>';
                    $tree .= '</li>';
                }
                $tree .= '</ul>';
            }
            $tree .= '</li>';
        }
        $tree .= '</ul>';
        return $tree;
    }

    function menu_cek($array, $id)
	{

		$array = array_values($array); // get value

        $cek = array();
        foreach($array as $val){

            if($val == $id)
            {
                // Array has
                return TRUE;
            }
            array_push($cek, $id);

        }

        return $cek;

	}


} // End of Model Class
