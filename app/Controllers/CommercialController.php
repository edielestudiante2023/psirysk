<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BatteryServiceModel;
use App\Models\CompanyModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controlador para el módulo comercial (Equipo Gladiator)
 * Gestiona la creación de órdenes de servicio y asignación a consultores
 */
class CommercialController extends BaseController
{
    protected $batteryServiceModel;
    protected $companyModel;
    protected $userModel;

    public function __construct()
    {
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->companyModel = new CompanyModel();
        $this->userModel = new UserModel();
        helper(['form', 'url']);
    }

    /**
     * Dashboard comercial - Vista principal
     */
    public function index()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');

        // Solo directores comerciales pueden acceder
        if ($roleName !== 'director_comercial') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
        }

        // Estadísticas
        $totalClients = $this->companyModel->countAll();
        $totalOrders = $this->batteryServiceModel->countAll();
        $openOrders = $this->batteryServiceModel->where('status !=', 'finalizado')->countAllResults();
        $closedOrders = $this->batteryServiceModel->where('status', 'finalizado')->countAllResults();

        // Últimas 5 órdenes
        $recentOrders = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->orderBy('battery_services.created_at', 'DESC')
            ->limit(5)
            ->findAll();

        return view('commercial/dashboard', [
            'title' => 'Dashboard Comercial - Equipo Gladiator',
            'totalClients' => $totalClients,
            'totalOrders' => $totalOrders,
            'openOrders' => $openOrders,
            'closedOrders' => $closedOrders,
            'recentOrders' => $recentOrders
        ]);
    }

    /**
     * Historial de órdenes de servicio
     */
    public function orders()
    {
        // Verificar autenticación
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');

        if ($roleName !== 'director_comercial') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Obtener todas las órdenes de servicio
        $services = $this->batteryServiceModel
            ->select('battery_services.*,
                      companies.name as company_name,
                      companies.nit as company_nit,
                      parent.name as parent_company_name,
                      users.name as consultant_name,
                      users.email as consultant_email')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->join('companies as parent', 'parent.id = companies.parent_company_id', 'left')
            ->join('users', 'users.id = battery_services.consultant_id')
            ->orderBy('battery_services.created_at', 'DESC')
            ->findAll();

        return view('commercial/orders', [
            'title' => 'Historial de Órdenes - Equipo Gladiator',
            'services' => $services
        ]);
    }

    /**
     * Formulario para crear nueva orden de servicio
     */
    public function create()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'director_comercial') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Obtener empresas con información de empresa gestora
        $companies = $this->companyModel
            ->select('companies.*, parent.name as parent_company_name, parent.contact_email as parent_contact_email')
            ->join('companies as parent', 'parent.id = companies.parent_company_id', 'left')
            ->orderBy('companies.name', 'ASC')
            ->findAll();

        $consultants = $this->userModel
            ->where('role_id', 2) // rol consultor (corregido de 3 a 2)
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('commercial/create', [
            'title' => 'Nueva Orden de Servicio',
            'companies' => $companies,
            'consultants' => $consultants
        ]);
    }

    /**
     * Guardar nueva orden de servicio
     */
    public function store()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'director_comercial') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        // Validación
        $validation = \Config\Services::validation();
        $validation->setRules([
            'company_id' => 'required|integer',
            'consultant_id' => 'required|integer',
            'service_name' => 'required|min_length[3]|max_length[255]',
            'service_date' => 'required|valid_date',
            'link_expiration_days' => 'required|integer|greater_than[0]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Calcular fecha de expiración
        $serviceDate = $this->request->getPost('service_date');
        $expirationDays = $this->request->getPost('link_expiration_days');
        $linkExpirationDate = date('Y-m-d', strtotime($serviceDate . ' + ' . $expirationDays . ' days'));

        // Crear orden de servicio
        $data = [
            'company_id' => $this->request->getPost('company_id'),
            'consultant_id' => $this->request->getPost('consultant_id'),
            'notify_parent_company' => $this->request->getPost('notify_parent_company') ? 1 : 0,
            'service_name' => $this->request->getPost('service_name'),
            'service_date' => $serviceDate,
            'link_expiration_date' => $linkExpirationDate,
            'includes_intralaboral' => $this->request->getPost('includes_intralaboral') ? 1 : 0,
            'includes_extralaboral' => $this->request->getPost('includes_extralaboral') ? 1 : 0,
            'includes_estres' => $this->request->getPost('includes_estres') ? 1 : 0,
            'cantidad_forma_a' => $this->request->getPost('cantidad_forma_a') ?? 0,
            'cantidad_forma_b' => $this->request->getPost('cantidad_forma_b') ?? 0,
            'status' => 'planificado'
        ];

        $serviceId = $this->batteryServiceModel->insert($data);

        if ($serviceId) {
            // Enviar email al consultor
            $this->sendServiceOrderEmail($serviceId);

            return redirect()->to('/commercial')->with('success', 'Orden de servicio creada exitosamente. Email enviado al consultor.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al crear la orden de servicio');
        }
    }

    /**
     * Enviar email de orden de servicio al consultor
     */
    protected function sendServiceOrderEmail($serviceId)
    {
        $service = $this->batteryServiceModel
            ->select('battery_services.*,
                      companies.name as company_name,
                      companies.nit as company_nit,
                      companies.parent_company_id,
                      consultant.name as consultant_name,
                      consultant.email as consultant_email')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->join('users as consultant', 'consultant.id = battery_services.consultant_id')
            ->find($serviceId);

        if (!$service) {
            return false;
        }

        // Obtener email del vendedor (usuario actual de sesión)
        $vendorEmail = session()->get('email');
        $vendorName = session()->get('name');

        $email = \Config\Services::email();

        // Email HTML con diseño profesional
        $message = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    font-family: 'Arial', 'Helvetica', sans-serif;
                    background-color: #f4f4f4;
                }
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background-color: #ffffff;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
                    color: white;
                    padding: 30px 20px;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 28px;
                    font-weight: bold;
                }
                .header .subtitle {
                    margin: 10px 0 0 0;
                    font-size: 16px;
                    opacity: 0.95;
                }
                .shield-icon {
                    font-size: 48px;
                    margin-bottom: 10px;
                }
                .content {
                    padding: 30px 25px;
                    color: #333;
                    line-height: 1.6;
                }
                .greeting {
                    font-size: 18px;
                    color: #2c3e50;
                    margin-bottom: 20px;
                }
                .info-box {
                    background-color: #f8f9fa;
                    border-left: 4px solid #e74c3c;
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 4px;
                }
                .info-row {
                    margin: 12px 0;
                    display: flex;
                    align-items: flex-start;
                }
                .info-label {
                    font-weight: bold;
                    color: #e74c3c;
                    min-width: 140px;
                    font-size: 14px;
                }
                .info-value {
                    color: #2c3e50;
                    font-size: 14px;
                }
                .section-title {
                    background-color: #e74c3c;
                    color: white;
                    padding: 10px 15px;
                    margin: 25px 0 15px 0;
                    border-radius: 4px;
                    font-weight: bold;
                    font-size: 14px;
                    text-transform: uppercase;
                }
                .questionnaire-list {
                    list-style: none;
                    padding: 0;
                    margin: 15px 0;
                }
                .questionnaire-list li {
                    padding: 10px 15px;
                    margin: 8px 0;
                    background-color: #e8f5e9;
                    border-left: 3px solid #27ae60;
                    border-radius: 3px;
                    color: #2c3e50;
                }
                .questionnaire-list li:before {
                    content: '✓ ';
                    color: #27ae60;
                    font-weight: bold;
                    margin-right: 8px;
                }
                .cta-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 15px 35px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: bold;
                    margin: 20px 0;
                    text-align: center;
                }
                .footer {
                    background-color: #2c3e50;
                    color: white;
                    text-align: center;
                    padding: 20px;
                    font-size: 12px;
                }
                .footer a {
                    color: #e74c3c;
                    text-decoration: none;
                }
                .order-number {
                    background-color: #fff3cd;
                    border: 2px solid #ffc107;
                    color: #856404;
                    padding: 15px;
                    text-align: center;
                    border-radius: 4px;
                    font-size: 18px;
                    font-weight: bold;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <!-- Header -->
                <div class='header'>
                    <div class='shield-icon'>🛡️</div>
                    <h1>EQUIPO GLADIATOR</h1>
                    <div class='subtitle'>Nueva Orden de Servicio Asignada</div>
                </div>

                <!-- Content -->
                <div class='content'>
                    <div class='greeting'>
                        Hola <strong>{$service['consultant_name']}</strong>,
                    </div>

                    <p>Se te ha asignado una nueva orden de servicio de <strong>Batería de Riesgo Psicosocial</strong>.</p>

                    <div class='order-number'>
                        📋 Orden N° " . str_pad($service['id'], 6, '0', STR_PAD_LEFT) . "
                    </div>

                    <!-- Información del Cliente -->
                    <div class='section-title'>📊 Información del Cliente</div>
                    <div class='info-box'>
                        <div class='info-row'>
                            <span class='info-label'>Empresa:</span>
                            <span class='info-value'>{$service['company_name']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>NIT:</span>
                            <span class='info-value'>{$service['company_nit']}</span>
                        </div>
                    </div>

                    <!-- Información del Servicio -->
                    <div class='section-title'>🎯 Detalles del Servicio</div>
                    <div class='info-box'>
                        <div class='info-row'>
                            <span class='info-label'>Nombre:</span>
                            <span class='info-value'>{$service['service_name']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Fecha de Servicio:</span>
                            <span class='info-value'>" . date('d/m/Y', strtotime($service['service_date'])) . "</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Vigencia Enlace:</span>
                            <span class='info-value'>Hasta " . date('d/m/Y', strtotime($service['link_expiration_date'])) . "</span>
                        </div>
                    </div>

                    <!-- Cuestionarios -->
                    <div class='section-title'>📝 Cuestionarios Incluidos</div>
                    <ul class='questionnaire-list'>
                        " . ($service['includes_intralaboral'] ? "<li>Cuestionario de Factores de Riesgo Psicosocial Intralaboral (Forma A y B)</li>" : "") . "
                        " . ($service['includes_extralaboral'] ? "<li>Cuestionario de Factores de Riesgo Psicosocial Extralaboral</li>" : "") . "
                        " . ($service['includes_estres'] ? "<li>Cuestionario para la Evaluación del Estrés</li>" : "") . "
                    </ul>

                    <!-- Distribución de Unidades -->
                    <div class='section-title'>👥 Distribución de Unidades</div>
                    <div class='info-box'>
                        <div class='info-row'>
                            <span class='info-label'>Forma A (Jefes/Profesionales):</span>
                            <span class='info-value'><strong>" . ($service['cantidad_forma_a'] ?? 0) . " unidades</strong></span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Forma B (Auxiliares/Operarios):</span>
                            <span class='info-value'><strong>" . ($service['cantidad_forma_b'] ?? 0) . " unidades</strong></span>
                        </div>
                        <div class='info-row' style='margin-top: 15px; padding-top: 15px; border-top: 2px solid #e74c3c;'>
                            <span class='info-label' style='font-size: 16px;'>TOTAL DE UNIDADES:</span>
                            <span class='info-value' style='color: #e74c3c; font-size: 18px;'><strong>" . (($service['cantidad_forma_a'] ?? 0) + ($service['cantidad_forma_b'] ?? 0)) . " unidades</strong></span>
                        </div>
                    </div>

                    <p style='margin-top: 30px; text-align: center;'>
                        <a href='" . base_url('dashboard') . "' class='cta-button'>
                            🚀 Acceder al Panel de Control
                        </a>
                    </p>

                    <p style='color: #7f8c8d; font-size: 13px; margin-top: 25px; padding-top: 20px; border-top: 1px solid #ecf0f1;'>
                        <strong>Nota:</strong> Puedes gestionar esta orden desde tu panel de control. Recuerda revisar los trabajadores asignados y enviar los enlaces de evaluación.
                    </p>
                </div>

                <!-- Footer -->
                <div class='footer'>
                    <p style='margin: 0 0 10px 0;'><strong>Cycloid Talent SAS</strong></p>
                    <p style='margin: 0 0 10px 0;'>Equipo Gladiator - Área Comercial</p>
                    <p style='margin: 0;'>
                        <a href='https://www.cycloidtalent.com'>www.cycloidtalent.com</a>
                    </p>
                    <p style='margin: 15px 0 0 0; font-size: 11px; opacity: 0.8;'>
                        © " . date('Y') . " Cycloid Talent. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Obtener emails de empresa hija y gestora si existe
        $companyModel = new \App\Models\CompanyModel();
        $company = $companyModel->find($service['company_id']);

        $recipients = [];
        $ccRecipients = [];

        // Email principal al consultor
        $recipients[] = $service['consultant_email'];

        // Agregar empresa hija (siempre)
        if (!empty($company['contact_email'])) {
            $ccRecipients[] = $company['contact_email'];
        }

        // Si hay empresa gestora Y el vendedor seleccionó notificarla, agregar su email
        if (!empty($service['parent_company_id']) && !empty($service['notify_parent_company'])) {
            $parentCompany = $companyModel->find($service['parent_company_id']);
            if ($parentCompany && !empty($parentCompany['contact_email'])) {
                $ccRecipients[] = $parentCompany['contact_email'];
            }
        }

        // Agregar vendedor en copia
        if (!empty($vendorEmail)) {
            $ccRecipients[] = $vendorEmail;
        }

        // Copia a Edison para seguimiento
        $ccRecipients[] = 'edison.cuervo@cycloidtalent.com';

        $email->setFrom('noreply@cycloidtalent.com', 'Cycloid Talent - Equipo Gladiator');
        $email->setTo($recipients);
        if (!empty($ccRecipients)) {
            $email->setCC(array_unique($ccRecipients)); // array_unique para evitar duplicados
        }
        $email->setSubject('🛡️ Nueva Orden de Servicio N° ' . str_pad($service['id'], 6, '0', STR_PAD_LEFT) . ' - ' . $service['service_name']);
        $email->setMailType('html'); // Importante: especificar que es HTML
        $email->setMessage($message);

        return $email->send();
    }

    /**
     * Descargar orden de servicio en PDF
     */
    public function downloadPdf($serviceId)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $roleName = session()->get('role_name');
        if ($roleName !== 'director_comercial') {
            return redirect()->to('/dashboard')->with('error', 'No tienes permisos');
        }

        $service = $this->batteryServiceModel
            ->select('battery_services.*,
                      companies.name as company_name,
                      companies.nit as company_nit,
                      companies.address as company_address,
                      users.name as consultant_name,
                      users.email as consultant_email')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->join('users', 'users.id = battery_services.consultant_id')
            ->find($serviceId);

        if (!$service) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Generar PDF
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);

        $html = view('commercial/pdf_order', ['service' => $service]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Orden_Servicio_' . $serviceId . '_' . date('Y-m-d') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }
}
