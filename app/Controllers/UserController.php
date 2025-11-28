<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $companyModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->companyModel = new \App\Models\CompanyModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');

        // Solo admin y superadmin pueden ver usuarios
        if (!in_array($roleName, ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Obtener usuarios con sus roles y empresas
        $users = $this->userModel
            ->select('users.*, roles.name as role_name, companies.name as company_name')
            ->join('roles', 'roles.id = users.role_id')
            ->join('companies', 'companies.id = users.company_id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Gestión de Usuarios',
            'users' => $users,
        ];

        return view('users/index', $data);
    }

    public function create()
    {
        // Verificar autenticación y permisos
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Obtener roles y empresas para los selects
        $roles = $this->roleModel->findAll();
        $companies = $this->companyModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Crear Usuario',
            'roles' => $roles,
            'companies' => $companies,
        ];

        return view('users/create', $data);
    }

    public function store()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role_id' => 'required|numeric',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'), // El modelo lo hasheará automáticamente
            'role_id' => $this->request->getPost('role_id'),
            'company_id' => $this->request->getPost('company_id') ?: null,
            'status' => 'active',
        ];

        if ($this->userModel->save($data)) {
            return redirect()->to('/users')->with('success', 'Usuario creado exitosamente');
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
        if (!in_array($roleName, ['admin', 'superadmin'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Usuario no encontrado');
        }

        $roles = $this->roleModel->findAll();
        $companies = $this->companyModel->orderBy('name', 'ASC')->findAll();

        $data = [
            'title' => 'Editar Usuario',
            'user' => $user,
            'roles' => $roles,
            'companies' => $companies,
        ];

        return view('users/edit', $data);
    }

    public function update($id)
    {
        // LOG DEBUG
        log_message('error', '🔍 UPDATE USUARIO INICIADO - ID: ' . $id);
        log_message('error', '🔍 POST DATA: ' . json_encode($this->request->getPost()));

        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            log_message('error', '❌ No está loggeado');
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            log_message('error', '❌ Usuario no encontrado');
            return redirect()->to('/users')->with('error', 'Usuario no encontrado');
        }

        log_message('error', '✅ Usuario encontrado: ' . json_encode($user));

        $rules = [
            'name' => 'required|min_length[3]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role_id' => 'required|numeric',
        ];

        // Solo validar password si se proporcionó uno nuevo
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
        }

        log_message('error', '🔍 Validando con reglas: ' . json_encode($rules));

        if (!$this->validate($rules)) {
            log_message('error', '❌ Validación falló: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        log_message('error', '✅ Validación exitosa');

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role_id' => $this->request->getPost('role_id'),
            'company_id' => $this->request->getPost('company_id') ?: null,
            'status' => $this->request->getPost('status'),
        ];

        // Solo actualizar password si se proporcionó uno nuevo
        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password'); // El modelo lo hasheará automáticamente
        }

        log_message('error', '🔍 Datos a actualizar: ' . json_encode($data));

        // Saltamos la validación del modelo porque ya validamos en el controlador
        // con is_unique[users.email,id,{$id}] que excluye correctamente al usuario actual
        $this->userModel->skipValidation(true);

        if ($this->userModel->update($id, $data)) {
            log_message('error', '✅ Usuario actualizado correctamente');
            return redirect()->to('/users')->with('success', 'Usuario actualizado exitosamente');
        } else {
            log_message('error', '❌ Error al actualizar: ' . json_encode($this->userModel->errors()));
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
        if ($roleName !== 'superadmin') {
            return redirect()->to('/users')->with('error', 'Solo el superadmin puede eliminar usuarios');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'Usuario no encontrado');
        }

        // No permitir eliminar al propio usuario
        if ($id == session()->get('id')) {
            return redirect()->to('/users')->with('error', 'No puedes eliminar tu propio usuario');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/users')->with('success', 'Usuario eliminado exitosamente');
        } else {
            return redirect()->back()->with('error', 'Error al eliminar el usuario');
        }
    }
}
