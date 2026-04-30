<?php

namespace App\Controllers;

use App\Models\TenantModel;

/**
 * TenantController
 * CRUD de tenants para el platform admin (rol superadmin).
 * Cualquier otro rol queda fuera.
 */
class TenantController extends BaseController
{
    protected TenantModel $tenantModel;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
        helper(['form', 'url']);
    }

    private function requireSuperadmin()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        if (session()->get('role_name') !== 'superadmin') {
            return redirect()->to('/dashboard')
                ->with('error', 'Solo el platform admin puede gestionar tenants.');
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->requireSuperadmin()) return $r;

        $tenants = $this->tenantModel->withoutTenantScope()
            ->orderBy('id', 'ASC')->findAll();

        return view('tenants/index', [
            'title'   => 'Gestion de Tenants',
            'tenants' => $tenants,
        ]);
    }

    public function create()
    {
        if ($r = $this->requireSuperadmin()) return $r;
        return view('tenants/create', ['title' => 'Crear Tenant']);
    }

    public function store()
    {
        if ($r = $this->requireSuperadmin()) return $r;

        $rules = [
            'slug'          => 'required|alpha_dash|min_length[2]|max_length[64]|is_unique[tenants.slug]',
            'legal_name'    => 'required|min_length[3]|max_length[255]',
            'trade_name'    => 'required|min_length[2]|max_length[255]',
            'nit'           => 'required|max_length[20]|is_unique[tenants.nit]',
            'contact_name'  => 'required|min_length[3]|max_length[255]',
            'contact_email' => 'required|valid_email|max_length[255]',
            'plan'          => 'required|in_list[inicial,profesional,empresarial,custom]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'slug'           => $this->request->getPost('slug'),
            'legal_name'     => $this->request->getPost('legal_name'),
            'trade_name'     => $this->request->getPost('trade_name'),
            'nit'            => preg_replace('/\D/', '', (string) $this->request->getPost('nit')),
            'contact_name'   => $this->request->getPost('contact_name'),
            'contact_email'  => $this->request->getPost('contact_email'),
            'contact_phone'  => $this->request->getPost('contact_phone'),
            'address'        => $this->request->getPost('address'),
            'city'           => $this->request->getPost('city'),
            'plan'           => $this->request->getPost('plan'),
            'status'         => $this->request->getPost('status') ?: 'trial',
            'created_by'     => session()->get('id'),
        ];

        $this->tenantModel->withoutTenantScope()->insert($data);

        return redirect()->to('/tenants')
            ->with('success', 'Tenant creado.');
    }

    public function edit($id)
    {
        if ($r = $this->requireSuperadmin()) return $r;

        $tenant = $this->tenantModel->withoutTenantScope()->find($id);
        if (!$tenant) {
            return redirect()->to('/tenants')->with('error', 'Tenant no encontrado.');
        }

        return view('tenants/edit', [
            'title'  => 'Editar Tenant',
            'tenant' => $tenant,
        ]);
    }

    public function update($id)
    {
        if ($r = $this->requireSuperadmin()) return $r;

        $tenant = $this->tenantModel->withoutTenantScope()->find($id);
        if (!$tenant) {
            return redirect()->to('/tenants')->with('error', 'Tenant no encontrado.');
        }

        $allowed = ['legal_name','trade_name','contact_name','contact_email','contact_phone',
            'address','city','plan','status','credits_balance','credits_included_monthly',
            'monthly_fee_cop','extra_credit_price_cop','trial_ends_at',
            'current_period_start','current_period_end'];
        $data = [];
        foreach ($allowed as $f) {
            $v = $this->request->getPost($f);
            if ($v !== null && $v !== '') $data[$f] = $v;
        }

        $this->tenantModel->withoutTenantScope()->update($id, $data);

        return redirect()->to('/tenants')->with('success', 'Tenant actualizado.');
    }

    public function suspend($id)
    {
        if ($r = $this->requireSuperadmin()) return $r;
        $this->tenantModel->withoutTenantScope()->update($id, ['status' => 'suspended']);
        return redirect()->to('/tenants')->with('success', 'Tenant suspendido.');
    }

    public function activate($id)
    {
        if ($r = $this->requireSuperadmin()) return $r;
        $this->tenantModel->withoutTenantScope()->update($id, ['status' => 'active']);
        return redirect()->to('/tenants')->with('success', 'Tenant activado.');
    }
}
