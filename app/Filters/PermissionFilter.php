<?php 

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Services;
use App\Models\UserModel;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userId = $session->get('user_id');
        $roleId = $session->get('role_id');
        if (!$userId || !$roleId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $uri = service('uri');
        $seg2 = $uri->getSegment(2);
        $seg3 = $uri->getSegment(3);
        $link = $seg3 ? strtolower($seg2.'/'.$seg3) : strtolower($seg2);
        $db = \Config\Database::connect();
        $hasAccess = $db->table('role_permissions rp')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->where('p.link', $link)
            ->countAllResults() > 0;
        if (!$hasAccess) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }
}