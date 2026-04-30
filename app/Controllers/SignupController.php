<?php

namespace App\Controllers;

use App\Libraries\EmailService;
use App\Models\TenantModel;
use App\Models\TenantSignupModel;
use App\Models\UserModel;

/**
 * SignupController
 * Flujo público de auto-registro de psicólogos como tenants.
 *
 *   GET  /signup                → formulario
 *   POST /signup                → crea tenant_signup pending + envía email
 *   GET  /signup/verify/{token} → verifica + crea tenant + crea user owner + login
 *   GET  /signup/pending        → "revisa tu correo"
 */
class SignupController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    public function index()
    {
        // Si ya está logueado, redirigir
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $plans = $this->getPlans();
        return view('signup/index', ['plans' => $plans]);
    }

    public function submit()
    {
        $rules = [
            'legal_name'    => 'required|min_length[3]|max_length[255]',
            'trade_name'    => 'permit_empty|max_length[255]',
            'nit'           => 'required|numeric|min_length[6]|max_length[20]',
            'contact_name'  => 'required|min_length[3]|max_length[255]',
            'contact_email' => 'required|valid_email|max_length[255]',
            'contact_phone' => 'permit_empty|max_length[20]',
            'password'      => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'plan'          => 'required|in_list[inicial,profesional,empresarial]',
            'accept_terms'  => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $signupModel = new TenantSignupModel();

        $email = strtolower(trim($this->request->getPost('contact_email')));
        $nit = preg_replace('/\D/', '', (string) $this->request->getPost('nit'));

        if ($signupModel->emailAlreadyUsed($email)) {
            return redirect()->back()->withInput()
                ->with('error', 'Este email ya está en uso por otra cuenta.');
        }

        if ($signupModel->nitAlreadyUsed($nit)) {
            return redirect()->back()->withInput()
                ->with('error', 'Este NIT ya está registrado en la plataforma.');
        }

        $token = bin2hex(random_bytes(32));

        $data = [
            'legal_name'              => $this->request->getPost('legal_name'),
            'trade_name'              => $this->request->getPost('trade_name') ?: $this->request->getPost('legal_name'),
            'nit'                     => $nit,
            'contact_name'            => $this->request->getPost('contact_name'),
            'contact_email'           => $email,
            'contact_phone'           => $this->request->getPost('contact_phone'),
            'password_hash'           => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'plan'                    => $this->request->getPost('plan'),
            'verification_token'      => hash('sha256', $token),
            'verification_expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'signup_ip'               => $this->request->getIPAddress(),
            'signup_user_agent'       => substr((string) $this->request->getUserAgent(), 0, 500),
        ];

        if (!$signupModel->insert($data)) {
            return redirect()->back()->withInput()
                ->with('error', 'No fue posible registrar la cuenta. Intenta de nuevo.');
        }

        $this->sendVerificationEmail($email, $data['contact_name'], $token);

        return redirect()->to('/signup/pending')
            ->with('email', $email);
    }

    public function pending()
    {
        return view('signup/pending', ['email' => session()->getFlashdata('email')]);
    }

    public function verify(string $token)
    {
        $signupModel = new TenantSignupModel();
        $signup = $signupModel->findByToken($token);

        if (!$signup) {
            return view('signup/invalid_token');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $tenantModel = new TenantModel();
        $plansConfig = $this->getPlans()[$signup['plan']];

        $tenantId = $tenantModel->withoutTenantScope()->insert([
            'slug'                     => $this->generateSlug($signup['trade_name'] ?: $signup['legal_name']),
            'legal_name'               => $signup['legal_name'],
            'trade_name'               => $signup['trade_name'] ?: $signup['legal_name'],
            'nit'                      => $signup['nit'],
            'contact_name'             => $signup['contact_name'],
            'contact_email'            => $signup['contact_email'],
            'contact_phone'            => $signup['contact_phone'],
            'plan'                     => $signup['plan'],
            'status'                   => 'trial',
            'trial_ends_at'            => date('Y-m-d', strtotime('+14 days')),
            'current_period_start'     => date('Y-m-d'),
            'current_period_end'       => date('Y-m-d', strtotime('+1 month')),
            'credits_balance'          => $plansConfig['credits'],
            'credits_included_monthly' => $plansConfig['credits'],
            'monthly_fee_cop'          => $plansConfig['monthly_fee'],
            'extra_credit_price_cop'   => $plansConfig['extra_credit'],
        ]);

        $userModel = new UserModel();
        $userId = $userModel->withoutTenantScope()->insert([
            'tenant_id' => $tenantId,
            'email'     => $signup['contact_email'],
            'password'  => 'placeholder',
            'role_id'   => 2,
            'name'      => $signup['contact_name'],
            'status'    => 'active',
        ]);

        $db->table('users')
            ->where('id', $userId)
            ->update(['password' => $signup['password_hash']]);

        $signupModel->update($signup['id'], [
            'status'      => 'verified',
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_ip' => $this->request->getIPAddress(),
            'tenant_id'   => $tenantId,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return view('signup/error', [
                'message' => 'Error al activar la cuenta. Contacta al soporte.'
            ]);
        }

        $session = session();
        $session->set([
            'id'         => $userId,
            'name'       => $signup['contact_name'],
            'email'      => $signup['contact_email'],
            'role_id'    => 2,
            'role_name'  => 'consultor',
            'tenant_id'  => $tenantId,
            'company_id' => null,
            'isLoggedIn' => true,
        ]);

        return redirect()->to('/onboarding/welcome')
            ->with('success', '¡Bienvenido a psyrisk! Tu cuenta está activada.');
    }

    private function getPlans(): array
    {
        return [
            'inicial' => [
                'label'        => 'Inicial',
                'monthly_fee'  => 79000,
                'credits'      => 10,
                'extra_credit' => 3800,
                'description'  => '10 evaluaciones/mes incluidas',
            ],
            'profesional' => [
                'label'        => 'Profesional',
                'monthly_fee'  => 199000,
                'credits'      => 50,
                'extra_credit' => 3500,
                'description'  => '50 evaluaciones/mes incluidas',
            ],
            'empresarial' => [
                'label'        => 'Empresarial',
                'monthly_fee'  => 499000,
                'credits'      => 200,
                'extra_credit' => 3000,
                'description'  => '200 evaluaciones/mes incluidas',
            ],
        ];
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        $slug = substr($slug, 0, 50);

        $tenantModel = new TenantModel();
        $base = $slug;
        $i = 1;
        while ($tenantModel->withoutTenantScope()->where('slug', $slug)->first()) {
            $slug = $base . '-' . $i;
            $i++;
            if ($i > 99) {
                $slug = $base . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
                break;
            }
        }
        return $slug;
    }

    private function sendVerificationEmail(string $email, string $name, string $token): bool
    {
        $verifyUrl = base_url('signup/verify/' . $token);
        $expires = '24 horas';

        $html = view('emails/tenant_verification', [
            'name'       => $name,
            'verifyUrl'  => $verifyUrl,
            'expiresIn'  => $expires,
        ]);

        try {
            $emailService = new EmailService();
            $reflection = new \ReflectionClass($emailService);
            $sendMethod = $reflection->getMethod('sendViaSendGrid');
            $sendMethod->setAccessible(true);
            return (bool) $sendMethod->invoke($emailService, $email,
                'Confirma tu cuenta de psyrisk', $html);
        } catch (\Throwable $e) {
            log_message('error', 'No fue posible enviar email de verificación: ' . $e->getMessage());
            return false;
        }
    }
}
