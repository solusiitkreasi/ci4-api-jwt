<?php 

namespace App\Models;

use CodeIgniter\Model;

class MenuModel extends Model 
{

    protected $tableName        = 'permissions';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name','slug', 'description', 'display_name', 'parent_id','sequence','link','icon'];
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
                ->whereNotIn('parent_id', array('0'))
                ->orderBy('sequence ASC')
                ->get();

        // die(nl2br( $this->db->getLastQuery() ));
        // die(nl2br($quer->getResultArray()));
        // tesx($quer->getResultArray());
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


    function get_permission_roles($id) {

        $query = $this->db->table('role_permissions')
                ->where('role_id', $id)
                ->get();
        return $query->getResultArray();

    }


    function generateTree($parent_id = 0)
    {

        $currentURI = service('request')->getUri();

        $items          = $this->get_items();

        $user_session   = session()->get('role_id');

        $permission = $this->get_permission_roles($user_session ?? '');

        $log_perm = array();
        foreach($permission as $k => $v) {

            $res = $v['permission_id'];
            array_push($log_perm, $res);
        }

        $tree = '<ul id="menu" class="menu">';

        for($i=0, $ni=count($items); $i < $ni; $i++){

            $idMenu = $items[$i]['id'];

            $headmenucheck = $this->menu_cek($log_perm, $idMenu);

            if($headmenucheck == TRUE){

                if($items[$i]['parent_id'] == $parent_id){

                    $pId = $items[$i]['id'];

                    $parents = $this->get_parent($pId);

                    $tree .= '<li >';

                    $parents_sub = $this->get_sub_count($pId);

                    // $urlm = $this->uri->segment(1);

                    $urlm = current_url(true);

                    $urlm = $urlm->getSegment(2);  

                    if($urlm == $parents['link']){
                        $activem = "active";
                    } else{
                        $activem = "";
                    }

                    $parent_link = $parents['link'];

                    if($parents_sub == 0){
                        $tree .= '<a href="'.$parent_link .'" class="'.$activem.'">
                                <i data-acorn-icon="'.$parents['icon'].'" class="icon" data-acorn-size="18"></i>';

                    }else{

                        $tree .= '<a href="#menu-'.$parents['id'].'" class="'.$activem.'" data-href="'.$parent_link .'" >
                                <i data-acorn-icon="'.$parents['icon'].'" class="icon" data-acorn-size="18"></i>';
                    }

                   

                    $tree .= '<span class="label">'.$parents['description'].'</span></a>';


                        $subtree = $this->get_sub($pId);

                        /**  This subtree */
                        if($subtree==TRUE){
                            $tree .= '<ul id="menu-'.$parents['id'].'">';
                                foreach($subtree as $sub){

                                    $subId = $sub['id'];

                                    $submenucheck = $this->menu_cek($log_perm, $subId);

                                    if($submenucheck == TRUE){

                                        $url = current_url(true);
                                        $url1 = $url->getSegment(1);
                                        $url2 = $url->getSegment(2);

                                        $surl = $url1.'/'.$url2;

                                        if($surl == $sub['link']){
                                            $active = "active";
                                        }else{
                                            $active = "";
                                        }

                                        // $currentURI->baseURL($sub['link'])
                                        $sub_link = $sub['link'];

                                        $tree .= '<li ><a href="'.$sub_link.'" class="'.$active.'">';
                                        $tree .= '<span class="label">'.$sub['description'].'</span></a>';
                                        $tree .= '</li>';

                                    }

                                }
                            $tree .= '</ul>';
                        }
                        /**  This subtree */

                    $tree .= '</li>';

                }

            }

        }

        // tesx($tree);

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
