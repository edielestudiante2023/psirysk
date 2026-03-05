<?php

namespace App\Controllers;

use App\Models\BatteryServiceModel;
use App\Models\WorkerModel;
use App\Models\CompanyModel;

/**
 * BateriaPublicaController
 *
 * Flujo de acceso grupal a la batería de riesgo psicosocial.
 * No requiere autenticación de sesión.
 *
 * Rutas:
 *   GET  /bateria/{enlace}    → Pantalla de ingreso de documento
 *   POST /bateria/validar     → Valida documento y redirige al token personal
 */
class BateriaPublicaController extends BaseController
{
    protected $batteryServiceModel;
    protected $workerModel;
    protected $companyModel;

    public function __construct()
    {
        $this->batteryServiceModel = new BatteryServiceModel();
        $this->workerModel         = new WorkerModel();
        $this->companyModel        = new CompanyModel();
    }

    /**
     * GET /bateria/{enlace}
     *
     * Muestra la pantalla de acceso: el trabajador ingresa su número de documento.
     */
    public function acceso(string $enlace)
    {
        // Buscar servicio por enlace_acceso
        $service = $this->batteryServiceModel
            ->select('battery_services.*, companies.name as company_name')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->where('battery_services.enlace_acceso', $enlace)
            ->first();

        if (!$service) {
            return view('bateria_publica/enlace_invalido', [
                'mensaje' => 'El enlace no es válido o ha sido desactivado.'
            ]);
        }

        // El servicio debe estar en curso
        if ($service['status'] !== 'en_curso') {
            $mensajes = [
                'planificado' => 'La batería aún no ha comenzado. Consulta con tu empleador la fecha de inicio.',
                'finalizado'  => 'Esta batería ya ha finalizado. Si tienes dudas, contacta al responsable.',
            ];
            return view('bateria_publica/enlace_invalido', [
                'mensaje' => $mensajes[$service['status']] ?? 'El enlace no está activo en este momento.'
            ]);
        }

        // Verificar que el enlace no haya expirado
        if (!empty($service['link_expiration_date']) && strtotime($service['link_expiration_date']) < strtotime(date('Y-m-d'))) {
            return view('bateria_publica/enlace_invalido', [
                'mensaje' => 'El enlace de acceso ha expirado. Contacta al consultor responsable.'
            ]);
        }

        return view('bateria_publica/acceso', [
            'service' => $service,
            'enlace'  => $enlace,
        ]);
    }

    /**
     * POST /bateria/validar
     *
     * Recibe el documento del trabajador, lo busca en el servicio y
     * redirige a su token personal de evaluación.
     */
    public function validarDocumento()
    {
        $enlace    = $this->request->getPost('enlace');
        $documento = trim($this->request->getPost('documento'));

        // Buscar servicio
        $service = $this->batteryServiceModel
            ->where('enlace_acceso', $enlace)
            ->where('status', 'en_curso')
            ->first();

        if (!$service) {
            return redirect()->to(base_url('bateria/' . $enlace))
                ->with('error', 'El enlace ya no está activo.');
        }

        // Buscar trabajador por documento en este servicio
        $worker = $this->workerModel
            ->where('battery_service_id', $service['id'])
            ->where('document', $documento)
            ->first();

        if (!$worker) {
            return redirect()->to(base_url('bateria/' . $enlace))
                ->with('error', 'Tu documento no se encuentra registrado en esta evaluación. Verifica el número e inténtalo de nuevo.');
        }

        // Verificar si ya completó
        if ($worker['status'] === 'completado') {
            return view('bateria_publica/ya_completado', [
                'worker'  => $worker,
                'service' => $service,
            ]);
        }

        // Verificar que el token exista
        if (empty($worker['token'])) {
            return redirect()->to(base_url('bateria/' . $enlace))
                ->with('error', 'Tu evaluación no tiene un enlace generado. Contacta al consultor.');
        }

        // Todo OK → redirigir al formulario personal
        return redirect()->to(base_url('assessment/' . $worker['token']));
    }
}
