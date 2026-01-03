<?php

namespace App\Controllers\PdfEjecutivo;

use App\Controllers\BaseController;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * CasosIntervencionController
 *
 * PROPÓSITO: Vista privada exclusiva del consultor para casos blanco de intervención.
 *
 * CONFIDENCIALIDAD: Este reporte contiene información individualizable de trabajadores
 * en riesgo alto y muy alto. Según la Resolución 2764/2022, esta información tiene
 * reserva similar a historia clínica y NO debe incluirse en informes entregados a la empresa.
 *
 * USO PERMITIDO:
 * - Entregar a la ARL cuando soliciten profundización de casos
 * - Compartir con médico especialista en SST bajo requerimiento administrativo
 * - Planificación interna del consultor para intervenciones
 */
class CasosIntervencionController extends BaseController
{
    protected $batteryServiceModel;
    protected $calculatedResultsModel;
    protected $companyModel;
    protected $workerModel;

    protected $batteryService;
    protected $company;
    protected $calculatedResults;

    public function __construct()
    {
        $this->batteryServiceModel = new \App\Models\BatteryServiceModel();
        $this->calculatedResultsModel = new \App\Models\CalculatedResultModel();
        $this->companyModel = new \App\Models\CompanyModel();
        $this->workerModel = new \App\Models\WorkerModel();
    }

    /**
     * Vista previa HTML
     */
    public function preview($batteryServiceId)
    {
        if (!$this->loadData($batteryServiceId)) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Solo consultores y superadmin pueden ver esto
        $role = session()->get('role_name');
        if (!in_array($role, ['consultor', 'superadmin'])) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }

        $html = $this->renderInteractive($batteryServiceId);
        return $html;
    }

    /**
     * Descargar PDF
     */
    public function download($batteryServiceId)
    {
        if (!$this->loadData($batteryServiceId)) {
            return redirect()->back()->with('error', 'Servicio no encontrado');
        }

        // Solo consultores y superadmin pueden descargar esto
        $role = session()->get('role_name');
        if (!in_array($role, ['consultor', 'superadmin'])) {
            return redirect()->back()->with('error', 'Acceso no autorizado');
        }

        $html = $this->render($batteryServiceId);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'Casos_Intervencion_' . $this->company['name'] . '_' . date('Y-m-d') . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Carga datos necesarios
     */
    protected function loadData($batteryServiceId)
    {
        $this->batteryService = $this->batteryServiceModel->find($batteryServiceId);
        if (!$this->batteryService) {
            return false;
        }

        $this->company = $this->companyModel->find($this->batteryService['company_id']);
        if (!$this->company) {
            return false;
        }

        $this->calculatedResults = $this->calculatedResultsModel
            ->where('battery_service_id', $batteryServiceId)
            ->findAll();

        return true;
    }

    /**
     * Renderiza el HTML interactivo con DataTables (para preview)
     */
    public function renderInteractive($batteryServiceId)
    {
        if (!$this->batteryService) {
            $this->loadData($batteryServiceId);
        }

        $casosIntralaboral = $this->getCasosRiesgoAlto('intralaboral');
        $casosExtralaboral = $this->getCasosRiesgoAlto('extralaboral');
        $casosEstres = $this->getCasosRiesgoAlto('estres');

        $totalCasos = count(array_unique(array_merge(
            array_column($casosIntralaboral, 'worker_id'),
            array_column($casosExtralaboral, 'worker_id'),
            array_column($casosEstres, 'worker_id')
        )));

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casos Blanco de Intervención - ' . esc($this->company['name']) . '</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; background: #f8f9fa; }
        .container-fluid { padding: 20px; max-width: 1400px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 30px; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header h1 { color: #dc3545; margin: 0; font-size: 32px; font-weight: bold; }
        .header h2 { color: #666; margin: 10px 0 0 0; font-size: 24px; font-weight: normal; }
        .confidencial {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border-radius: 8px;
        }
        .confidencial-title { color: #856404; font-weight: bold; font-size: 18px; }
        .confidencial-text { color: #856404; font-size: 14px; margin-top: 10px; }
        .resumen-box {
            background: white;
            border: 1px solid #dee2e6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .resumen-grid { display: flex; justify-content: space-around; }
        .resumen-item { text-align: center; padding: 15px; }
        .resumen-numero { font-size: 48px; font-weight: bold; }
        .resumen-label { font-size: 14px; color: #666; margin-top: 10px; }
        .section-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-title {
            background: #dc3545;
            color: white;
            padding: 12px 20px;
            margin: -20px -20px 20px -20px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }
        table.dataTable { width: 100% !important; }
        .nivel-alto { background: #FF9800 !important; color: white; text-align: center; font-weight: bold; padding: 5px 10px; border-radius: 4px; }
        .nivel-muy-alto { background: #F44336 !important; color: white; text-align: center; font-weight: bold; padding: 5px 10px; border-radius: 4px; }
        .no-casos {
            text-align: center;
            padding: 40px;
            color: #28a745;
            font-style: italic;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
        }
        .footer-legal {
            margin-top: 40px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            font-size: 12px;
            color: #666;
            text-align: justify;
        }
        .dt-buttons { margin-bottom: 15px; }
        .btn-group { gap: 10px; }
    </style>
</head>
<body>

<div class="container-fluid">

<div class="header">
    <h1>CASOS BLANCO DE INTERVENCIÓN</h1>
    <h2>' . esc($this->company['name']) . '</h2>
    <p style="font-size: 14px; color: #666; margin: 10px 0 0 0;">
        Servicio #' . $this->batteryService['id'] . ' | Generado: ' . date('d/m/Y H:i') . '
    </p>
</div>

<div class="confidencial">
    <div class="confidencial-title"><i class="fas fa-exclamation-triangle"></i> DOCUMENTO CONFIDENCIAL - USO EXCLUSIVO DEL PROFESIONAL SST</div>
    <div class="confidencial-text">
        Este documento contiene información individualizable con reserva similar a historia clínica según Resolución 2764/2022.<br>
        <strong>NO debe incluirse en informes entregados a la empresa.</strong><br>
        Uso permitido: Entrega a ARL para profundización, médico especialista SST bajo requerimiento administrativo.
    </div>
</div>

<div class="resumen-box">
    <div class="resumen-grid">
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #dc3545;">' . $totalCasos . '</div>
            <div class="resumen-label">Trabajadores en<br>Riesgo Alto/Muy Alto</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #FF9800;">' . count($casosIntralaboral) . '</div>
            <div class="resumen-label">Casos<br>Intralaboral</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #9B59B6;">' . count($casosExtralaboral) . '</div>
            <div class="resumen-label">Casos<br>Extralaboral</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #17a2b8;">' . count($casosEstres) . '</div>
            <div class="resumen-label">Casos<br>Estrés</div>
        </div>
    </div>
</div>';

        // Sección Intralaboral
        $html .= '<div class="section-card">
    <div class="section-title">RIESGO INTRALABORAL - Casos Alto y Muy Alto</div>';
        if (!empty($casosIntralaboral)) {
            $html .= $this->renderTablaInteractiva($casosIntralaboral, 'intralaboral');
        } else {
            $html .= '<div class="no-casos"><i class="fas fa-check-circle"></i> No hay trabajadores con riesgo alto o muy alto en el cuestionario intralaboral.</div>';
        }
        $html .= '</div>';

        // Sección Extralaboral
        $html .= '<div class="section-card">
    <div class="section-title">RIESGO EXTRALABORAL - Casos Alto y Muy Alto</div>';
        if (!empty($casosExtralaboral)) {
            $html .= $this->renderTablaInteractiva($casosExtralaboral, 'extralaboral');
        } else {
            $html .= '<div class="no-casos"><i class="fas fa-check-circle"></i> No hay trabajadores con riesgo alto o muy alto en el cuestionario extralaboral.</div>';
        }
        $html .= '</div>';

        // Sección Estrés
        $html .= '<div class="section-card">
    <div class="section-title">ESTRÉS - Casos Alto y Muy Alto</div>';
        if (!empty($casosEstres)) {
            $html .= $this->renderTablaInteractiva($casosEstres, 'estres');
        } else {
            $html .= '<div class="no-casos"><i class="fas fa-check-circle"></i> No hay trabajadores con riesgo alto o muy alto en el cuestionario de estrés.</div>';
        }
        $html .= '</div>';

        // Footer legal
        $html .= '
<div class="footer-legal">
    <strong>Marco Legal:</strong> Resolución 2646 de 2008, Resolución 2404 de 2019, Resolución 2764 de 2022 del Ministerio del Trabajo de Colombia.
    La información contenida en este documento tiene carácter reservado y confidencial. Su uso indebido puede generar responsabilidades legales.
    <br><br>
    <strong>Responsable:</strong> ' . esc(session()->get('name') ?? 'Consultor SST') . ' |
    <strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '
</div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $(".tabla-casos").DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        dom: "Bfrtip",
        buttons: [
            {
                extend: "excelHtml5",
                text: "<i class=\"fas fa-file-excel\"></i> Exportar a Excel",
                className: "btn btn-success btn-sm",
                title: "Casos de Intervención - ' . esc($this->company['name']) . '",
                exportOptions: {
                    columns: ":visible"
                }
            },
            {
                extend: "print",
                text: "<i class=\"fas fa-print\"></i> Imprimir",
                className: "btn btn-secondary btn-sm"
            }
        ],
        pageLength: 25,
        order: [[6, "desc"], [5, "desc"]], // Ordenar por nivel (muy alto primero) y puntaje
        responsive: true
    });
});
</script>

</body>
</html>';

        return $html;
    }

    /**
     * Renderiza tabla interactiva con DataTables
     */
    protected function renderTablaInteractiva($casos, $tipo)
    {
        $tableId = 'tabla-' . $tipo;

        $html = '
<table id="' . $tableId . '" class="table table-striped table-bordered tabla-casos">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Área</th>
            <th>Cargo</th>
            <th>Forma</th>
            <th>Puntaje</th>
            <th>Nivel</th>
        </tr>
    </thead>
    <tbody>';

        foreach ($casos as $caso) {
            // Manejar ambas nomenclaturas: riesgo_muy_alto/muy_alto
            $esMuyAlto = in_array($caso['nivel'], ['riesgo_muy_alto', 'muy_alto']);
            $claseNivel = $esMuyAlto ? 'nivel-muy-alto' : 'nivel-alto';
            $nombreNivel = $esMuyAlto ? 'MUY ALTO' : 'ALTO';

            $html .= '
        <tr>
            <td>' . esc($caso['nombre']) . '</td>
            <td>' . esc($caso['documento']) . '</td>
            <td>' . esc($caso['area']) . '</td>
            <td>' . esc($caso['cargo']) . '</td>
            <td style="text-align: center;">' . esc($caso['forma']) . '</td>
            <td style="text-align: center;">' . $caso['puntaje'] . '</td>
            <td><span class="' . $claseNivel . '">' . $nombreNivel . '</span></td>
        </tr>';
        }

        $html .= '
    </tbody>
</table>';

        return $html;
    }

    /**
     * Renderiza el HTML del reporte
     */
    public function render($batteryServiceId)
    {
        if (!$this->batteryService) {
            $this->loadData($batteryServiceId);
        }

        $casosIntralaboral = $this->getCasosRiesgoAlto('intralaboral');
        $casosExtralaboral = $this->getCasosRiesgoAlto('extralaboral');
        $casosEstres = $this->getCasosRiesgoAlto('estres');

        $totalCasos = count(array_unique(array_merge(
            array_column($casosIntralaboral, 'worker_id'),
            array_column($casosExtralaboral, 'worker_id'),
            array_column($casosEstres, 'worker_id')
        )));

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Casos Blanco de Intervención - ' . esc($this->company['name']) . '</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20pt; padding-bottom: 10pt; border-bottom: 3pt solid #dc3545; }
        .header h1 { color: #dc3545; margin: 0; font-size: 16pt; }
        .header h2 { color: #666; margin: 5pt 0 0 0; font-size: 12pt; font-weight: normal; }
        .confidencial {
            background: #fff3cd;
            border: 2pt solid #ffc107;
            padding: 10pt;
            margin: 15pt 0;
            text-align: center;
        }
        .confidencial-title { color: #856404; font-weight: bold; font-size: 11pt; }
        .confidencial-text { color: #856404; font-size: 8pt; margin-top: 5pt; }
        .resumen-box {
            background: #f8f9fa;
            border: 1pt solid #dee2e6;
            padding: 15pt;
            margin: 15pt 0;
        }
        .resumen-grid { display: table; width: 100%; }
        .resumen-item {
            display: table-cell;
            text-align: center;
            padding: 10pt;
            width: 25%;
        }
        .resumen-numero { font-size: 28pt; font-weight: bold; }
        .resumen-label { font-size: 8pt; color: #666; }
        .section-title {
            background: #dc3545;
            color: white;
            padding: 8pt 12pt;
            margin: 20pt 0 10pt 0;
            font-size: 11pt;
            font-weight: bold;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15pt; }
        th {
            background: #343a40;
            color: white;
            padding: 6pt 4pt;
            text-align: left;
            font-size: 8pt;
            border: 1pt solid #333;
        }
        td {
            padding: 5pt 4pt;
            border: 1pt solid #ddd;
            font-size: 8pt;
            vertical-align: top;
        }
        tr:nth-child(even) { background: #f8f9fa; }
        .nivel-alto { background: #FF9800 !important; color: white; text-align: center; font-weight: bold; }
        .nivel-muy-alto { background: #F44336 !important; color: white; text-align: center; font-weight: bold; }
        .footer-legal {
            margin-top: 30pt;
            padding-top: 15pt;
            border-top: 1pt solid #ddd;
            font-size: 7pt;
            color: #666;
            text-align: justify;
        }
        .page-break { page-break-before: always; }
        .no-casos {
            text-align: center;
            padding: 20pt;
            color: #28a745;
            font-style: italic;
            background: #d4edda;
            border: 1pt solid #c3e6cb;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>CASOS BLANCO DE INTERVENCIÓN</h1>
    <h2>' . esc($this->company['name']) . '</h2>
    <p style="font-size: 9pt; color: #666; margin: 5pt 0 0 0;">
        Servicio #' . $this->batteryService['id'] . ' | Generado: ' . date('d/m/Y H:i') . '
    </p>
</div>

<div class="confidencial">
    <div class="confidencial-title">⚠ DOCUMENTO CONFIDENCIAL - USO EXCLUSIVO DEL PROFESIONAL SST</div>
    <div class="confidencial-text">
        Este documento contiene información individualizable con reserva similar a historia clínica según Resolución 2764/2022.<br>
        <strong>NO debe incluirse en informes entregados a la empresa.</strong><br>
        Uso permitido: Entrega a ARL para profundización, médico especialista SST bajo requerimiento administrativo.
    </div>
</div>

<div class="resumen-box">
    <div class="resumen-grid">
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #dc3545;">' . $totalCasos . '</div>
            <div class="resumen-label">Trabajadores en<br>Riesgo Alto/Muy Alto</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #FF9800;">' . count($casosIntralaboral) . '</div>
            <div class="resumen-label">Casos<br>Intralaboral</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #9B59B6;">' . count($casosExtralaboral) . '</div>
            <div class="resumen-label">Casos<br>Extralaboral</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-numero" style="color: #17a2b8;">' . count($casosEstres) . '</div>
            <div class="resumen-label">Casos<br>Estrés</div>
        </div>
    </div>
</div>';

        // Sección Intralaboral
        $html .= '<div class="section-title">RIESGO INTRALABORAL - Casos Alto y Muy Alto</div>';
        if (!empty($casosIntralaboral)) {
            $html .= $this->renderTablaCasos($casosIntralaboral, 'intralaboral');
        } else {
            $html .= '<div class="no-casos">No hay trabajadores con riesgo alto o muy alto en el cuestionario intralaboral.</div>';
        }

        // Sección Extralaboral
        $html .= '<div class="section-title">RIESGO EXTRALABORAL - Casos Alto y Muy Alto</div>';
        if (!empty($casosExtralaboral)) {
            $html .= $this->renderTablaCasos($casosExtralaboral, 'extralaboral');
        } else {
            $html .= '<div class="no-casos">No hay trabajadores con riesgo alto o muy alto en el cuestionario extralaboral.</div>';
        }

        // Sección Estrés
        $html .= '<div class="section-title">ESTRÉS - Casos Alto y Muy Alto</div>';
        if (!empty($casosEstres)) {
            $html .= $this->renderTablaCasos($casosEstres, 'estres');
        } else {
            $html .= '<div class="no-casos">No hay trabajadores con riesgo alto o muy alto en el cuestionario de estrés.</div>';
        }

        // Footer legal
        $html .= '
<div class="footer-legal">
    <strong>Marco Legal:</strong> Resolución 2646 de 2008, Resolución 2404 de 2019, Resolución 2764 de 2022 del Ministerio del Trabajo de Colombia.
    La información contenida en este documento tiene carácter reservado y confidencial. Su uso indebido puede generar responsabilidades legales.
    <br><br>
    <strong>Responsable:</strong> ' . esc(session()->get('name') ?? 'Consultor SST') . ' |
    <strong>Fecha de generación:</strong> ' . date('d/m/Y H:i:s') . '
</div>

</body>
</html>';

        return $html;
    }

    /**
     * Obtiene casos en riesgo alto y muy alto
     */
    protected function getCasosRiesgoAlto($tipo)
    {
        $casos = [];
        $campoNivel = '';
        $campoPuntaje = '';

        switch ($tipo) {
            case 'intralaboral':
                $campoNivel = 'intralaboral_total_nivel';
                $campoPuntaje = 'intralaboral_total_puntaje';
                $nivelesAlto = ['riesgo_alto', 'riesgo_muy_alto'];
                break;
            case 'extralaboral':
                $campoNivel = 'extralaboral_total_nivel';
                $campoPuntaje = 'extralaboral_total_puntaje';
                $nivelesAlto = ['riesgo_alto', 'riesgo_muy_alto'];
                break;
            case 'estres':
                // Estrés usa nomenclatura diferente: alto, muy_alto (no riesgo_alto)
                $campoNivel = 'estres_total_nivel';
                $campoPuntaje = 'estres_total_puntaje';
                $nivelesAlto = ['alto', 'muy_alto'];
                break;
        }

        foreach ($this->calculatedResults as $result) {
            $nivel = $result[$campoNivel] ?? '';
            if (in_array($nivel, $nivelesAlto)) {
                // Obtener datos del trabajador
                $worker = $this->workerModel->find($result['worker_id']);

                $casos[] = [
                    'worker_id' => $result['worker_id'],
                    'nombre' => $worker['name'] ?? 'N/D',
                    'documento' => $worker['document'] ?? 'N/D',
                    'area' => $worker['area'] ?? 'Sin área',
                    'cargo' => $worker['position'] ?? 'Sin cargo',
                    'forma' => $result['intralaboral_form_type'] ?? 'N/A',
                    'puntaje' => number_format($result[$campoPuntaje] ?? 0, 1),
                    'nivel' => $nivel,
                ];
            }
        }

        // Ordenar por nivel (muy alto primero) y luego por puntaje
        usort($casos, function($a, $b) {
            if ($a['nivel'] === $b['nivel']) {
                return floatval($b['puntaje']) - floatval($a['puntaje']);
            }
            // Manejar ambas nomenclaturas: riesgo_muy_alto/muy_alto
            $aMuyAlto = in_array($a['nivel'], ['riesgo_muy_alto', 'muy_alto']);
            $bMuyAlto = in_array($b['nivel'], ['riesgo_muy_alto', 'muy_alto']);
            return $aMuyAlto ? -1 : ($bMuyAlto ? 1 : 0);
        });

        return $casos;
    }

    /**
     * Renderiza tabla de casos
     */
    protected function renderTablaCasos($casos, $tipo)
    {
        $html = '
<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th style="width: 80pt;">Documento</th>
            <th>Área</th>
            <th>Cargo</th>
            <th style="width: 40pt;">Forma</th>
            <th style="width: 50pt;">Puntaje</th>
            <th style="width: 70pt;">Nivel</th>
        </tr>
    </thead>
    <tbody>';

        foreach ($casos as $caso) {
            // Manejar ambas nomenclaturas: riesgo_muy_alto/muy_alto
            $esMuyAlto = in_array($caso['nivel'], ['riesgo_muy_alto', 'muy_alto']);
            $claseNivel = $esMuyAlto ? 'nivel-muy-alto' : 'nivel-alto';
            $nombreNivel = $esMuyAlto ? 'MUY ALTO' : 'ALTO';

            $html .= '
        <tr>
            <td>' . esc($caso['nombre']) . '</td>
            <td>' . esc($caso['documento']) . '</td>
            <td>' . esc($caso['area']) . '</td>
            <td>' . esc($caso['cargo']) . '</td>
            <td style="text-align: center;">' . esc($caso['forma']) . '</td>
            <td style="text-align: center;">' . $caso['puntaje'] . '</td>
            <td class="' . $claseNivel . '">' . $nombreNivel . '</td>
        </tr>';
        }

        $html .= '
    </tbody>
</table>';

        return $html;
    }
}
