<?php
namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\PaymentGatewayModel;

class PaymentGatewayController extends BaseController
{
    public function index()
    {
        $model = new PaymentGatewayModel();
        $data['gateways'] = $model->orderBy('provider')->findAll();
        return view('backend/pages/payment_gateway/list', $data);
    }

    public function create()
    {
        return view('backend/pages/payment_gateway/form', ['gateway' => null]);
    }

    public function store()
    {
        $model = new PaymentGatewayModel();
        $data = $this->request->getPost();
        // Validasi unik provider+mode
        if ($model->where('provider', $data['provider'])->where('mode', $data['mode'])->first()) {
            return redirect()->back()->with('error', 'Provider & mode sudah ada!');
        }
        $model->insert($data);
        return redirect()->to('/backend/payment-gateway')->with('success', 'Gateway berhasil ditambah.');
    }

    public function edit($id)
    {
        $model = new PaymentGatewayModel();
        $gateway = $model->find($id);
        return view('backend/pages/payment_gateway/form', ['gateway' => $gateway]);
    }

    public function update($id)
    {
        $model = new PaymentGatewayModel();
        $data = $this->request->getPost();
        // Validasi unik provider+mode (kecuali id ini)
        if ($model->where('provider', $data['provider'])->where('mode', $data['mode'])->where('id !=', $id)->first()) {
            return redirect()->back()->with('error', 'Provider & mode sudah ada!');
        }
        $model->update($id, $data);
        return redirect()->to('/backend/payment-gateway')->with('success', 'Gateway berhasil diupdate.');
    }

    public function delete($id)
    {
        $model = new PaymentGatewayModel();
        $model->delete($id);
        return redirect()->to('/backend/payment-gateway')->with('success', 'Gateway berhasil dihapus.');
    }

    public function toggle($id)
    {
        $model = new PaymentGatewayModel();
        $gateway = $model->find($id);
        if ($gateway) {
            $model->update($id, ['is_active' => $gateway['is_active'] ? 0 : 1]);
        }
        return redirect()->to('/backend/payment-gateway');
    }
}
