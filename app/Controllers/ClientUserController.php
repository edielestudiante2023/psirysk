<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\CompanyModel;
use CodeIgniter\HTTP\ResponseInterface;

class ClientUserController extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $companyModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->companyModel = new CompanyModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');

        // Solo consultores pueden acceder
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Obtener solo usuarios de cliente (cliente_gestor y cliente_empresa)
        $users = $this->userModel
            ->select('users.*, roles.name as role_name, companies.name as company_name')
            ->join('roles', 'roles.id = users.role_id')
            ->join('companies', 'companies.id = users.company_id', 'left')
            ->whereIn('roles.name', ['cliente_gestor', 'cliente_empresa'])
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Usuarios de Cliente',
            'users' => $users,
        ];

        return view('client_users/index', $data);
    }

    public function create()
    {
        // Verificar autenticación y permisos
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Obtener solo roles de cliente
        $roles = $this->roleModel
            ->whereIn('name', ['cliente_gestor', 'cliente_empresa'])
            ->findAll();

        $companies = $this->companyModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Crear Usuario de Cliente',
            'roles' => $roles,
            'companies' => $companies,
        ];

        return view('client_users/create', $data);
    }

    public function store()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role_id' => 'required|numeric',
            'company_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Verificar que el rol sea de cliente
        $role = $this->roleModel->find($this->request->getPost('role_id'));
        if (!in_array($role['name'], ['cliente_gestor', 'cliente_empresa'])) {
            return redirect()->back()->withInput()->with('error', 'Solo puedes crear usuarios de tipo cliente');
        }

        // Obtener empresa para el email
        $company = $this->companyModel->find($this->request->getPost('company_id'));

        // Guardar contraseña en texto plano temporalmente para enviar por email
        $plainPassword = $this->request->getPost('password');

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $plainPassword,
            'role_id' => $this->request->getPost('role_id'),
            'company_id' => $this->request->getPost('company_id'),
            'status' => 'active',
        ];

        if ($this->userModel->save($data)) {
            // Enviar email con credenciales
            $this->sendCredentialsEmail($data['email'], $data['name'], $plainPassword, $role['name'], $company['name']);

            return redirect()->to('/client-users')->with('success', 'Usuario creado exitosamente. Se han enviado las credenciales por email.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al crear el usuario');
        }
    }

    public function edit($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $user = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $id)
            ->first();

        if (!$user) {
            return redirect()->to('/client-users')->with('error', 'Usuario no encontrado');
        }

        // Verificar que sea usuario de cliente
        if (!in_array($user['role_name'], ['cliente_gestor', 'cliente_empresa'])) {
            return redirect()->to('/client-users')->with('error', 'Solo puedes editar usuarios de cliente');
        }

        $roles = $this->roleModel
            ->whereIn('name', ['cliente_gestor', 'cliente_empresa'])
            ->findAll();

        $companies = $this->companyModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Editar Usuario de Cliente',
            'user' => $user,
            'roles' => $roles,
            'companies' => $companies,
        ];

        return view('client_users/edit', $data);
    }

    public function update($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/client-users')->with('error', 'Usuario no encontrado');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role_id' => 'required|numeric',
            'company_id' => 'required|numeric',
        ];

        // Solo validar password si se proporcionó uno nuevo
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Verificar que el rol sea de cliente
        $role = $this->roleModel->find($this->request->getPost('role_id'));
        if (!in_array($role['name'], ['cliente_gestor', 'cliente_empresa'])) {
            return redirect()->back()->withInput()->with('error', 'Solo puedes asignar roles de tipo cliente');
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role_id' => $this->request->getPost('role_id'),
            'company_id' => $this->request->getPost('company_id'),
            'status' => $this->request->getPost('status'),
        ];

        // Solo actualizar password si se proporcionó uno nuevo
        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        if ($this->userModel->update($id, $data)) {
            return redirect()->to('/client-users')->with('success', 'Usuario actualizado exitosamente');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el usuario');
        }
    }

    public function delete($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'consultor') {
            return redirect()->to('/client-users')->with('error', 'No tienes permisos');
        }

        $user = $this->userModel
            ->select('users.*, roles.name as role_name')
            ->join('roles', 'roles.id = users.role_id')
            ->where('users.id', $id)
            ->first();

        if (!$user) {
            return redirect()->to('/client-users')->with('error', 'Usuario no encontrado');
        }

        // Verificar que sea usuario de cliente
        if (!in_array($user['role_name'], ['cliente_gestor', 'cliente_empresa'])) {
            return redirect()->to('/client-users')->with('error', 'Solo puedes eliminar usuarios de cliente');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/client-users')->with('success', 'Usuario eliminado exitosamente');
        } else {
            return redirect()->back()->with('error', 'Error al eliminar el usuario');
        }
    }

    private function sendCredentialsEmail($email, $name, $password, $roleName, $companyName)
    {
        $emailService = \Config\Services::email();

        $tipoUsuario = $roleName === 'cliente_gestor' ? 'Gestor Multiempresa' : 'Cliente Individual';

        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .credentials-box { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 20px 0; }
                .credential-item { padding: 10px 0; border-bottom: 1px solid #e0e0e0; }
                .credential-item:last-child { border-bottom: none; }
                .credential-label { font-weight: bold; color: #667eea; }
                .credential-value { color: #333; font-size: 16px; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔐 Credenciales de Acceso</h1>
                    <p>Sistema PsyRisk - Evaluación de Riesgo Psicosocial</p>
                </div>

                <div class="content">
                    <h2>Hola ' . esc($name) . ',</h2>

                    <p>Se ha creado tu cuenta de acceso al sistema <strong>PsyRisk</strong>.</p>

                    <div class="credentials-box">
                        <h3 style="margin-top: 0; color: #667eea;">Tus credenciales de acceso:</h3>

                        <div class="credential-item">
                            <div class="credential-label">Tipo de Usuario:</div>
                            <div class="credential-value">' . esc($tipoUsuario) . '</div>
                        </div>

                        <div class="credential-item">
                            <div class="credential-label">Empresa:</div>
                            <div class="credential-value">' . esc($companyName) . '</div>
                        </div>

                        <div class="credential-item">
                            <div class="credential-label">Usuario (Email):</div>
                            <div class="credential-value">' . esc($email) . '</div>
                        </div>

                        <div class="credential-item">
                            <div class="credential-label">Contraseña:</div>
                            <div class="credential-value"><strong>' . esc($password) . '</strong></div>
                        </div>
                    </div>

                    <div class="warning">
                        <strong>⚠️ Importante:</strong> Por seguridad, te recomendamos cambiar tu contraseña después del primer inicio de sesión.
                    </div>

                    <div style="text-align: center;">
                        <a href="' . base_url('login') . '" class="button">Iniciar Sesión</a>
                    </div>

                    <p>Si tienes alguna duda o problema para acceder, por favor contacta a tu consultor asignado.</p>
                </div>

                <div class="footer">
                    <p>Este es un correo automático, por favor no responder.</p>
                    <p>&copy; ' . date('Y') . ' PsyRisk - Cycloid Talent</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $emailService->setFrom('noreply@cycloidtalent.com', 'PsyRisk - Cycloid Talent');
        $emailService->setTo($email);
        $emailService->setSubject('🔐 Tus credenciales de acceso a PsyRisk');
        $emailService->setMailType('html');
        $emailService->setMessage($message);

        try {
            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'Error enviando credenciales: ' . $e->getMessage());
        }
    }
}
