<?php

namespace App\Controllers;

use App\Models\TenantModel;

/**
 * OnboardingController
 * Wizard post-verificación: el psicólogo nuevo configura su tenant.
 *   /onboarding/welcome   → bienvenida
 *   /onboarding/branding  → sube logo + colores
 *   /onboarding/finish    → todo listo, va al dashboard
 */
class OnboardingController extends BaseController
{
    protected TenantModel $tenantModel;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
        helper(['form', 'url']);
    }

    public function welcome()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        return view('onboarding/welcome', [
            'tenant' => tenant_context(),
        ]);
    }

    public function branding()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        return view('onboarding/branding', [
            'tenant' => tenant_context(),
        ]);
    }

    public function saveBranding()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return redirect()->to('/dashboard')
                ->with('error', 'No se pudo identificar tu tenant.');
        }

        $data = [
            'brand_primary_color'   => $this->request->getPost('brand_primary_color') ?: '#0066CC',
            'brand_secondary_color' => $this->request->getPost('brand_secondary_color') ?: '#003366',
            'website_url'           => $this->request->getPost('website_url'),
            'linkedin_url'          => $this->request->getPost('linkedin_url'),
            'address'               => $this->request->getPost('address'),
            'city'                  => $this->request->getPost('city'),
            'pdf_footer_text'       => $this->request->getPost('pdf_footer_text'),
        ];

        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
            if (!in_array(strtolower($logo->getExtension()), $allowed, true)) {
                return redirect()->back()->withInput()
                    ->with('error', 'Formato de logo no permitido. Usa PNG, JPG o WEBP.');
            }
            if ($logo->getSize() > 2 * 1024 * 1024) {
                return redirect()->back()->withInput()
                    ->with('error', 'El logo no debe pesar más de 2MB.');
            }
            $uploadPath = FCPATH . 'uploads/logos/tenants';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = 'tenant_' . $tenantId . '_' . time() . '.' . $logo->getExtension();
            $logo->move($uploadPath, $newName);
            $data['logo_path'] = 'uploads/logos/tenants/' . $newName;
        }

        $this->tenantModel->withoutTenantScope()->update($tenantId, $data);

        return redirect()->to('/onboarding/finish')
            ->with('success', 'Branding guardado correctamente.');
    }

    public function finish()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        return view('onboarding/finish', [
            'tenant' => tenant_context(),
        ]);
    }
}
