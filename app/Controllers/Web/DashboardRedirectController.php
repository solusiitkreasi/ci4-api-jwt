<?php 

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class DashboardRedirectController extends BaseController
{
    public function index()
    {
        $session = session();
        $roles = $session->get('roles') ?? [];

        // Prioritaskan Super Admin
        $lowerCaseRoles = array_map('strtolower', $roles);
        if (in_array('Super Admin', $roles)) {
            return redirect()->to(route_to('admin.dashboard'));
        }
        
        if (in_array('Client', $roles)) {
            return redirect()->to(route_to('client.dashboard'));
        }

        // Jika tidak punya peran yang dikenali, logout saja
        return redirect()->to(route_to('web.logout'))->with('error', 'Anda tidak ada akses.');
    }
}