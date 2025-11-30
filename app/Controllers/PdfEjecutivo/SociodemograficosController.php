<?php

namespace App\Controllers\PdfEjecutivo;

/**
 * Controlador de Variables Sociodemográficas para el Informe Ejecutivo PDF
 * Sección 2: Características Sociodemográficas de la población evaluada
 * Usa datos de IA generados previamente en demographics_sections
 */
class SociodemograficosController extends PdfEjecutivoBaseController
{
    protected $demographicsData = null;

    /**
     * Preview de las variables sociodemográficas en navegador
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadDemographicsData($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePreview($html, 'Preview: Variables Sociodemográficas');
    }

    /**
     * Descarga PDF de las variables sociodemográficas
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $this->loadDemographicsData($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePdf($html, "Sociodemograficos.pdf");
    }

    /**
     * Carga datos demográficos generados por IA
     */
    protected function loadDemographicsData($batteryServiceId)
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT * FROM demographics_sections
            WHERE battery_service_id = ?
        ", [$batteryServiceId]);

        $this->demographicsData = $query->getRowArray();
    }

    /**
     * Renderiza el HTML de las variables sociodemográficas
     */
    public function render($batteryServiceId)
    {
        if (empty($this->companyData)) {
            $this->initializeData($batteryServiceId);
            $this->loadDemographicsData($batteryServiceId);
        }

        $html = $this->renderEncabezado();
        $html .= $this->renderSexo();
        $html .= $this->renderEdad();
        $html .= $this->renderEstadoCivil();
        $html .= $this->renderEducacion();  // Page break antes
        $html .= $this->renderEstrato();
        $html .= $this->renderVivienda();
        $html .= $this->renderDependientes();
        $html .= $this->renderResidencia();   // Page break antes
        $html .= $this->renderAntiguedadEmpresa();
        $html .= $this->renderAntiguedadCargo();
        $html .= $this->renderContrato();
        $html .= $this->renderCargo();        // Page break antes
        $html .= $this->renderArea();
        $html .= $this->renderHoras();
        $html .= $this->renderSalario();
        $html .= $this->renderSintesis();     // Page break antes

        return $html;
    }

    /**
     * Encabezado de la sección
     */
    protected function renderEncabezado()
    {
        $total = $this->demographicsData['total_workers'] ?? 0;
        $company = $this->companyData;

        return '
<h1 style="font-size: 14pt; margin: 0 0 12pt 0; padding-bottom: 5pt;">Características Sociodemográficas</h1>

<p style="font-size: 9pt; text-align: justify; margin: 0 0 10pt 0;">
A continuación, se presentan las características sociodemográficas de los <strong>' . $total . ' colaboradores</strong> de la empresa <strong>' . esc($company['company_name'] ?? 'EMPRESA') . '</strong> que participaron en la evaluación de factores de riesgo psicosocial.
</p>
';
    }

    /**
     * Renderiza una variable demográfica con tabla y comentario IA
     */
    protected function renderVariableDemografica($titulo, $dataJson, $comentarioIA, $pageBreak = false)
    {
        $html = '';

        if ($pageBreak) {
            $html .= '<div class="page-break"></div>';
        }

        $html .= '
<h3 style="font-size: 11pt; color: #006699; margin: 12pt 0 6pt 0; text-decoration: underline;">' . $titulo . '</h3>
';

        // Parsear datos JSON
        $data = json_decode($dataJson, true);

        if (!empty($data)) {
            // Verificar si es estructura de edad (con distribution y statistics)
            if (isset($data['distribution'])) {
                $html .= $this->renderTablaDistribucion($data['distribution']);
                if (isset($data['statistics'])) {
                    $html .= $this->renderEstadisticas($data['statistics']);
                }
            } else {
                // Estructura normal de array con label, count, percentage
                $html .= $this->renderTablaDistribucion($data);
            }
        }

        // Comentario generado por IA
        if (!empty($comentarioIA)) {
            $html .= '
<p style="font-size: 9pt; text-align: justify; margin: 8pt 0 5pt 0; padding: 8pt; background-color: #f9f9f9; border-left: 3pt solid #006699;">
' . esc($comentarioIA) . '
</p>
';
        }

        return $html;
    }

    /**
     * Renderiza tabla de distribución de datos
     */
    protected function renderTablaDistribucion($data)
    {
        if (empty($data)) {
            return '<p style="font-size: 9pt; color: #666;">No hay datos disponibles.</p>';
        }

        $html = '
<table style="width: 100%; border-collapse: collapse; margin: 5pt 0; font-size: 8pt;">
    <thead>
        <tr>
            <th style="width: 50%; background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: left;">Categoría</th>
            <th style="width: 25%; background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center;">Cantidad</th>
            <th style="width: 25%; background-color: #006699; color: white; padding: 4pt; border: 1pt solid #333; text-align: center;">Porcentaje</th>
        </tr>
    </thead>
    <tbody>
';
        foreach ($data as $row) {
            $label = $row['label'] ?? 'Sin definir';
            $count = $row['count'] ?? 0;
            $percentage = $row['percentage'] ?? 0;

            $html .= '
        <tr>
            <td style="padding: 3pt; border: 1pt solid #333;">' . esc($label) . '</td>
            <td style="padding: 3pt; border: 1pt solid #333; text-align: center;">' . $count . '</td>
            <td style="padding: 3pt; border: 1pt solid #333; text-align: center;">' . number_format($percentage, 1) . '%</td>
        </tr>
';
        }

        $html .= '
    </tbody>
</table>
';
        return $html;
    }

    /**
     * Renderiza estadísticas (para edad)
     */
    protected function renderEstadisticas($stats)
    {
        return '
<p style="font-size: 8pt; color: #555; margin: 3pt 0;">
    <strong>Estadísticas:</strong> Edad mínima: ' . ($stats['min'] ?? '-') . ' años |
    Edad máxima: ' . ($stats['max'] ?? '-') . ' años |
    Promedio: ' . number_format($stats['mean'] ?? 0, 1) . ' años |
    Mediana: ' . number_format($stats['median'] ?? 0, 1) . ' años
</p>
';
    }

    // ========== Variables Demográficas Individuales ==========

    protected function renderSexo()
    {
        return $this->renderVariableDemografica(
            'Sexo',
            $this->demographicsData['sexo_data'] ?? '[]',
            $this->demographicsData['sexo_ia'] ?? ''
        );
    }

    protected function renderEdad()
    {
        return $this->renderVariableDemografica(
            'Edad',
            $this->demographicsData['edad_data'] ?? '[]',
            $this->demographicsData['edad_ia'] ?? ''
        );
    }

    protected function renderEstadoCivil()
    {
        return $this->renderVariableDemografica(
            'Estado Civil',
            $this->demographicsData['estado_civil_data'] ?? '[]',
            $this->demographicsData['estado_civil_ia'] ?? ''
        );
    }

    protected function renderEducacion()
    {
        return $this->renderVariableDemografica(
            'Nivel de Escolaridad',
            $this->demographicsData['educacion_data'] ?? '[]',
            $this->demographicsData['educacion_ia'] ?? ''
        );
    }

    protected function renderEstrato()
    {
        return $this->renderVariableDemografica(
            'Estrato Socioeconómico',
            $this->demographicsData['estrato_data'] ?? '[]',
            $this->demographicsData['estrato_ia'] ?? ''
        );
    }

    protected function renderVivienda()
    {
        return $this->renderVariableDemografica(
            'Tipo de Vivienda',
            $this->demographicsData['vivienda_data'] ?? '[]',
            $this->demographicsData['vivienda_ia'] ?? ''
        );
    }

    protected function renderDependientes()
    {
        return $this->renderVariableDemografica(
            'Personas a Cargo',
            $this->demographicsData['dependientes_data'] ?? '[]',
            $this->demographicsData['dependientes_ia'] ?? ''
        );
    }

    protected function renderResidencia()
    {
        return $this->renderVariableDemografica(
            'Lugar de Residencia',
            $this->demographicsData['residencia_data'] ?? '[]',
            $this->demographicsData['residencia_ia'] ?? ''
        );
    }

    protected function renderAntiguedadEmpresa()
    {
        return $this->renderVariableDemografica(
            'Antigüedad en la Empresa',
            $this->demographicsData['antiguedad_empresa_data'] ?? '[]',
            $this->demographicsData['antiguedad_empresa_ia'] ?? ''
        );
    }

    protected function renderAntiguedadCargo()
    {
        return $this->renderVariableDemografica(
            'Antigüedad en el Cargo',
            $this->demographicsData['antiguedad_cargo_data'] ?? '[]',
            $this->demographicsData['antiguedad_cargo_ia'] ?? ''
        );
    }

    protected function renderContrato()
    {
        return $this->renderVariableDemografica(
            'Tipo de Contrato',
            $this->demographicsData['contrato_data'] ?? '[]',
            $this->demographicsData['contrato_ia'] ?? ''
        );
    }

    protected function renderCargo()
    {
        return $this->renderVariableDemografica(
            'Tipo de Cargo',
            $this->demographicsData['cargo_data'] ?? '[]',
            $this->demographicsData['cargo_ia'] ?? ''
        );
    }

    protected function renderArea()
    {
        return $this->renderVariableDemografica(
            'Área o Departamento',
            $this->demographicsData['area_data'] ?? '[]',
            $this->demographicsData['area_ia'] ?? ''
        );
    }

    protected function renderHoras()
    {
        return $this->renderVariableDemografica(
            'Horas de Trabajo Diarias',
            $this->demographicsData['horas_data'] ?? '[]',
            $this->demographicsData['horas_ia'] ?? ''
        );
    }

    protected function renderSalario()
    {
        return $this->renderVariableDemografica(
            'Tipo de Salario',
            $this->demographicsData['salario_data'] ?? '[]',
            $this->demographicsData['salario_ia'] ?? ''
        );
    }

    /**
     * Síntesis sociodemográfica
     */
    protected function renderSintesis()
    {
        $sintesis = $this->demographicsData['sintesis_ia'] ?? '';
        $comentarioConsultor = $this->demographicsData['sintesis_comment'] ?? '';

        if (empty($sintesis) && empty($comentarioConsultor)) {
            return '';
        }

        // Combinar síntesis IA con comentarios del consultor
        $contenido = $sintesis;
        if (!empty($comentarioConsultor)) {
            $contenido .= "\n\n" . $comentarioConsultor;
        }

        $html = '
<div class="page-break"></div>
<h2 style="font-size: 13pt; color: #006699; margin: 0 0 10pt 0; padding-bottom: 3pt; border-bottom: 1pt solid #006699;">Síntesis Sociodemográfica</h2>

<div style="font-size: 9pt; text-align: justify; margin: 0 0 10pt 0; padding: 10pt; background-color: #e8f4fc; border: 1pt solid #006699;">
' . nl2br(esc($contenido)) . '
</div>
';

        return $html;
    }
}
