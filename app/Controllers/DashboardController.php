<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\CompanyModel;
use App\Models\BatteryServiceModel;
use App\Models\WorkerModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function __construct()
    {
        helper(['url']);
    }

    public function index()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');

        // Redirigir según el rol
        switch ($roleName) {
            case 'admin':
            case 'superadmin':
                return $this->superadminDashboard();
            case 'consultor':
                return $this->consultorDashboard();
            case 'director_comercial':
                return redirect()->to('/commercial'); // Redirige al módulo comercial
            case 'cliente_gestor':
                return $this->clienteGestorDashboard();
            case 'cliente_empresa':
                return $this->clienteEmpresaDashboard();
            default:
                return redirect()->to('/login');
        }
    }

    private function superadminDashboard()
    {
        $userModel = new UserModel();
        $companyModel = new CompanyModel();
        $batteryServiceModel = new BatteryServiceModel();

        $data = [
            'title' => 'Dashboard Superadmin',
            'totalUsers' => $userModel->countAll(),
            'totalCompanies' => $companyModel->countAll(),
            'totalServices' => $batteryServiceModel->countAll(),
            'recentUsers' => $userModel->orderBy('created_at', 'DESC')->limit(5)->find(),
            'recentCompanies' => $companyModel->orderBy('created_at', 'DESC')->limit(5)->find(),
        ];

        return view('dashboard/superadmin', $data);
    }

    private function consultorDashboard()
    {
        $companyModel = new CompanyModel();
        $batteryServiceModel = new BatteryServiceModel();
        $workerModel = new WorkerModel();

        $userId = session()->get('id');

        $data = [
            'title' => 'Dashboard Consultor',
            'totalCompanies' => $companyModel->countAll(),
            'totalServices' => $batteryServiceModel->countAllResults(),
            'recentServices' => $batteryServiceModel
                ->select('battery_services.*, companies.name as company_name, COUNT(workers.id) as worker_count')
                ->join('companies', 'companies.id = battery_services.company_id')
                ->join('workers', 'workers.battery_service_id = battery_services.id', 'left')
                ->groupBy('battery_services.id')
                ->orderBy('battery_services.created_at', 'DESC')
                ->limit(10)
                ->find(),
        ];

        return view('dashboard/consultor', $data);
    }

    private function clienteGestorDashboard()
    {
        $companyModel = new CompanyModel();
        $batteryServiceModel = new BatteryServiceModel();
        $userId = session()->get('id');
        $userCompanyId = session()->get('company_id');

        // Si no tiene empresa asignada, mostrar error
        if (!$userCompanyId) {
            return redirect()->to('/logout')->with('error', 'Tu usuario no tiene una empresa asignada. Contacta al administrador.');
        }

        // Obtener la empresa gestor
        $gestorCompany = $companyModel->find($userCompanyId);

        // Obtener todas las empresas hijas
        $childCompanies = $companyModel
            ->where('parent_company_id', $userCompanyId)
            ->orWhere('id', $userCompanyId) // Incluir también la gestora
            ->findAll();

        $companyIds = array_column($childCompanies, 'id');

        // Obtener servicios de batería de todas las empresas del grupo
        $services = $batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->whereIn('battery_services.company_id', $companyIds)
            ->orderBy('battery_services.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Dashboard Cliente Gestor',
            'gestorCompany' => $gestorCompany,
            'childCompanies' => $childCompanies,
            'totalCompanies' => count($childCompanies),
            'totalServices' => count($services),
            'recentServices' => array_slice($services, 0, 10),
        ];

        return view('dashboard/cliente_gestor', $data);
    }

    private function clienteEmpresaDashboard()
    {
        $companyModel = new CompanyModel();
        $batteryServiceModel = new BatteryServiceModel();
        $userId = session()->get('id');
        $userCompanyId = session()->get('company_id');

        // Si no tiene empresa asignada, mostrar error
        if (!$userCompanyId) {
            return redirect()->to('/logout')->with('error', 'Tu usuario no tiene una empresa asignada. Contacta al administrador.');
        }

        // Obtener la empresa
        $company = $companyModel->find($userCompanyId);

        // Obtener servicios de batería de la empresa
        $services = $batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->where('battery_services.company_id', $userCompanyId)
            ->orderBy('battery_services.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Dashboard Cliente',
            'company' => $company,
            'totalServices' => count($services),
            'recentServices' => array_slice($services, 0, 10),
        ];

        return view('dashboard/cliente_empresa', $data);
    }
}
