<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use CodeIgniter\HTTP\ResponseInterface;

class CompanyController extends BaseController
{
    protected $companyModel;

    public function __construct()
    {
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
        $userId = session()->get('id');

        // Todos los roles autorizados pueden ver todas las empresas
        // Esto permite colaboración entre consultores en proyectos grandes
        if ($roleName === 'admin' || $roleName === 'superadmin' ||
            $roleName === 'director_comercial' || $roleName === 'consultor') {
            $companies = $this->companyModel->orderBy('name', 'ASC')->findAll();
        } else {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        $data = [
            'title' => 'Gestión de Empresas',
            'companies' => $companies,
        ];

        return view('companies/index', $data);
    }

    public function create()
    {
        // Verificar autenticación y permisos
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if (!in_array($roleName, ['superadmin', 'admin', 'consultor', 'director_comercial'])) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Obtener empresas gestoras para el select de parent_company
        $gestores = $this->companyModel->where('type', 'gestor_multicompania')->findAll();

        $data = [
            'title' => 'Crear Empresa',
            'gestores' => $gestores,
        ];

        return view('companies/create', $data);
    }

    public function store()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'type' => 'required|in_list[gestor_multicompania,empresa_individual]',
            'nit' => 'required|is_unique[companies.nit]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'nit' => $this->request->getPost('nit'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'contact_name' => $this->request->getPost('contact_name'),
            'contact_email' => $this->request->getPost('contact_email'),
            'parent_company_id' => $this->request->getPost('parent_company_id') ?: null,
            'created_by' => session()->get('id'),
            'status' => 'active',
        ];

        // Procesar logo si se subió
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/logos/companies';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $newName = 'company_' . time() . '_' . $logo->getRandomName();
            $logo->move($uploadPath, $newName);
            $data['logo_path'] = 'uploads/logos/companies/' . $newName;
        }

        if ($this->companyModel->save($data)) {
            return redirect()->to('/companies')->with('success', 'Empresa creada exitosamente');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al crear la empresa');
        }
    }

    public function edit($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $company = $this->companyModel->find($id);
        if (!$company) {
            return redirect()->to('/companies')->with('error', 'Empresa no encontrada');
        }

        // Verificar permisos
        $roleName = session()->get('role_name');
        $userId = session()->get('id');
        if ($roleName === 'consultor' && $company['created_by'] != $userId) {
            return redirect()->to('/companies')->with('error', 'No tienes permisos para editar esta empresa');
        }

        $gestores = $this->companyModel->where('type', 'gestor_multicompania')->findAll();

        $data = [
            'title' => 'Editar Empresa',
            'company' => $company,
            'gestores' => $gestores,
        ];

        return view('companies/edit', $data);
    }

    public function update($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $company = $this->companyModel->find($id);
        if (!$company) {
            return redirect()->to('/companies')->with('error', 'Empresa no encontrada');
        }

        $rules = [
            'name' => 'required|min_length[3]',
            'type' => 'required|in_list[gestor_multicompania,empresa_individual]',
            'nit' => "required|is_unique[companies.nit,id,{$id}]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'type' => $this->request->getPost('type'),
            'nit' => $this->request->getPost('nit'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'contact_name' => $this->request->getPost('contact_name'),
            'contact_email' => $this->request->getPost('contact_email'),
            'parent_company_id' => $this->request->getPost('parent_company_id') ?: null,
            'status' => $this->request->getPost('status'),
        ];

        // Procesar logo si se subió uno nuevo
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/logos/companies';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Eliminar logo anterior si existe
            if (!empty($company['logo_path']) && file_exists(FCPATH . $company['logo_path'])) {
                unlink(FCPATH . $company['logo_path']);
            }

            $newName = 'company_' . time() . '_' . $logo->getRandomName();
            $logo->move($uploadPath, $newName);
            $data['logo_path'] = 'uploads/logos/companies/' . $newName;
        }

        if ($this->companyModel->update($id, $data)) {
            return redirect()->to('/companies')->with('success', 'Empresa actualizada exitosamente');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar la empresa');
        }
    }

    public function delete($id)
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $company = $this->companyModel->find($id);
        if (!$company) {
            return redirect()->to('/companies')->with('error', 'Empresa no encontrada');
        }

        // Verificar permisos
        $roleName = session()->get('role_name');
        if ($roleName !== 'superadmin') {
            return redirect()->to('/companies')->with('error', 'Solo el superadmin puede eliminar empresas');
        }

        if ($this->companyModel->delete($id)) {
            return redirect()->to('/companies')->with('success', 'Empresa eliminada exitosamente');
        } else {
            return redirect()->back()->with('error', 'Error al eliminar la empresa');
        }
    }
}
