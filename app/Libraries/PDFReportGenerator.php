<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generador de Informes PDF Completos
 * Genera informes de 50+ páginas con análisis detallado de todas las dimensiones
 * según la Resolución 2404/2019 del Ministerio de Trabajo de Colombia
 */
class PDFReportGenerator
{
    private $dompdf;
    private $serviceData;
    private $results;
    private $html;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $this->dompdf = new Dompdf($options);
        $this->html = '';
    }

    /**
     * Generar informe completo
     */
    public function generateCompleteReport($serviceId)
    {
        // Cargar datos del servicio y resultados
        $this->loadServiceData($serviceId);

        // Iniciar HTML
        $this->html = $this->getHTMLHeader();

        // 1. Portada
        $this->html .= $this->generateCoverPage();

        // 2. Tabla de Contenidos
        $this->html .= $this->generateTableOfContents();

        // 3. Introducción y Metodología
        $this->html .= $this->generateIntroduction();

        // 4. Variables Sociodemográficas
        $this->html .= $this->generateDemographicSection();

        // 5. Riesgo Psicosocial General
        $this->html .= $this->generateGeneralRiskSection();

        // 6. Riesgo Intralaboral
        $this->html .= $this->generateIntralaboralSection();

        // 7. Riesgo Extralaboral
        $this->html .= $this->generateExtralaboralSection();

        // 8. Estrés
        $this->html .= $this->generateEstresSection();

        // 9. Recomendaciones y Plan de Intervención
        $this->html .= $this->generateRecommendationsSection();

        // Cerrar HTML
        $this->html .= $this->getHTMLFooter();

        // Generar PDF
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('Letter', 'portrait');
        $this->dompdf->render();

        return $this->dompdf;
    }

    /**
     * Obtener HTML generado (para debugging)
     */
    public function getGeneratedHTML()
    {
        return $this->html;
    }

    /**
     * Cargar datos del servicio
     */
    private function loadServiceData($serviceId)
    {
        $db = \Config\Database::connect();

        // Obtener servicio
        $this->serviceData = $db->table('battery_services')
            ->select('battery_services.*, companies.name as company_name, companies.nit')
            ->join('companies', 'companies.id = battery_services.company_id')
            ->where('battery_services.id', $serviceId)
            ->get()
            ->getRowArray();

        // Obtener resultados con información de trabajadores
        $this->results = $db->table('calculated_results')
            ->select('calculated_results.*, workers.name, workers.document')
            ->join('workers', 'workers.id = calculated_results.worker_id')
            ->where('calculated_results.battery_service_id', $serviceId)
            ->get()
            ->getResultArray();
    }

    /**
     * Obtener encabezado HTML con estilos
     */
    private function getHTMLHeader()
    {
        return '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .cover {
            text-align: center;
            padding-top: 100px;
        }
        .cover h1 {
            font-size: 24pt;
            color: #0066cc;
            margin-bottom: 20px;
        }
        .cover .company {
            font-size: 18pt;
            font-weight: bold;
            margin: 30px 0;
        }
        h1 {
            color: #0066cc;
            font-size: 16pt;
            border-bottom: 2px solid #0066cc;
            padding-bottom: 5px;
            margin-top: 30px;
        }
        h2 {
            color: #0066cc;
            font-size: 14pt;
            margin-top: 20px;
        }
        h3 {
            color: #333;
            font-size: 12pt;
            margin-top: 15px;
        }
        .definition {
            background-color: #f0f8ff;
            padding: 10px;
            border-left: 4px solid #0066cc;
            margin: 10px 0;
            font-style: italic;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .stats-table th,
        .stats-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .stats-table th {
            background-color: #0066cc;
            color: white;
            font-weight: bold;
        }
        .stats-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .risk-muy-alto { background-color: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; }
        .risk-alto { background-color: #fd7e14; color: white; padding: 3px 8px; border-radius: 3px; }
        .risk-medio { background-color: #ffc107; color: #333; padding: 3px 8px; border-radius: 3px; }
        .risk-bajo { background-color: #7dce82; color: white; padding: 3px 8px; border-radius: 3px; }
        .risk-sin { background-color: #28a745; color: white; padding: 3px 8px; border-radius: 3px; }
        .conclusion {
            background-color: #fff3cd;
            padding: 10px;
            border-left: 4px solid #ffc107;
            margin: 10px 0;
        }
        .page-break {
            page-break-after: always;
        }
        .chart-placeholder {
            width: 100%;
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            margin: 10px 0;
        }
    </style>
</head>
<body>';
    }

    /**
     * Cerrar HTML
     */
    private function getHTMLFooter()
    {
        return '
</body>
</html>';
    }

    /**
     * Generar portada
     */
    private function generateCoverPage()
    {
        $serviceName = esc($this->serviceData['service_name']);
        $companyName = esc($this->serviceData['company_name']);
        $date = date('d/m/Y', strtotime($this->serviceData['service_date']));

        return "
<div class='cover'>
    <h1>INFORME DE EVALUACIÓN<br>DE RIESGO PSICOSOCIAL</h1>
    <div class='company'>{$companyName}</div>
    <p style='font-size: 14pt; margin: 20px 0;'>{$serviceName}</p>
    <p style='font-size: 12pt; color: #666;'>Fecha: {$date}</p>
    <p style='margin-top: 50px; font-size: 10pt;'>
        Evaluación realizada según Resolución 2404 de 2019<br>
        Ministerio del Trabajo - República de Colombia
    </p>
</div>
<div class='page-break'></div>
";
    }

    /**
     * Generar tabla de contenidos
     */
    private function generateTableOfContents()
    {
        return "
<h1>TABLA DE CONTENIDOS</h1>
<ol style='line-height: 2;'>
    <li>Introducción</li>
    <li>Marco Legal y Conceptual</li>
    <li>Objetivos de la Evaluación</li>
    <li>Metodología</li>
    <li>Variables Sociodemográficas y Ocupacionales</li>
    <li>Resultados Generales de Riesgo Psicosocial</li>
    <li>Riesgo Psicosocial Intralaboral
        <ol type='a'>
            <li>Puntaje Global</li>
            <li>Análisis por Dominios</li>
            <li>Análisis por Dimensiones</li>
        </ol>
    </li>
    <li>Riesgo Psicosocial Extralaboral
        <ol type='a'>
            <li>Puntaje Global</li>
            <li>Análisis por Dimensiones</li>
        </ol>
    </li>
    <li>Nivel de Estrés</li>
    <li>Recomendaciones y Plan de Intervención</li>
</ol>
<div class='page-break'></div>
";
    }

    /**
     * Generar introducción y metodología
     */
    private function generateIntroduction()
    {
        $totalWorkers = count($this->results);

        return "
<h1>1. INTRODUCCIÓN</h1>
<p>
El presente informe contiene los resultados de la evaluación de factores de riesgo psicosocial
realizada en <strong>{$this->serviceData['company_name']}</strong>, en cumplimiento de lo establecido
en la Resolución 2404 de 2019 del Ministerio del Trabajo de Colombia.
</p>
<p>
La evaluación fue realizada con una muestra de <strong>{$totalWorkers} trabajadores</strong>,
utilizando los instrumentos validados por el Ministerio de la Protección Social:
</p>
<ul>
    <li>Cuestionario de factores de riesgo psicosocial intralaboral (Formas A y B)</li>
    <li>Cuestionario de factores de riesgo psicosocial extralaboral</li>
    <li>Cuestionario para la evaluación del estrés</li>
    <li>Ficha de datos generales</li>
</ul>

<h1>2. MARCO LEGAL</h1>
<p>
La evaluación de factores de riesgo psicosocial en Colombia está regulada principalmente por:
</p>
<ul>
    <li><strong>Ley 1562 de 2012:</strong> Modifica el Sistema de Riesgos Laborales y dicta otras disposiciones en materia de Salud Ocupacional.</li>
    <li><strong>Resolución 2404 de 2019:</strong> Adopta la Batería de Instrumentos para la Evaluación de Factores de Riesgo Psicosocial.</li>
    <li><strong>Resolución 0312 de 2019:</strong> Define los estándares mínimos del Sistema de Gestión de Seguridad y Salud en el Trabajo.</li>
</ul>

<h1>3. OBJETIVOS</h1>
<h3>Objetivo General</h3>
<p>
Identificar y evaluar los factores de riesgo psicosocial intralaboral, extralaboral y los niveles de estrés
en los trabajadores de {$this->serviceData['company_name']}, con el fin de establecer acciones de intervención
que promuevan el bienestar y la salud mental de los colaboradores.
</p>

<h3>Objetivos Específicos</h3>
<ul>
    <li>Aplicar los cuestionarios de evaluación de riesgo psicosocial a los trabajadores de la empresa.</li>
    <li>Analizar los resultados obtenidos e identificar los niveles de riesgo por dominio y dimensión.</li>
    <li>Identificar los trabajadores que requieren atención prioritaria.</li>
    <li>Proponer recomendaciones y plan de intervención basado en los hallazgos.</li>
</ul>

<h1>4. METODOLOGÍA</h1>
<p>
La evaluación se realizó siguiendo la metodología establecida en la Resolución 2404 de 2019,
que comprende las siguientes etapas:
</p>

<h3>4.1 Población Evaluada</h3>
<p>
Se evaluó un total de <strong>{$totalWorkers} trabajadores</strong> de diferentes áreas y niveles jerárquicos
de la organización, garantizando la representatividad de la muestra.
</p>

<h3>4.2 Instrumentos Utilizados</h3>
<p><strong>Cuestionario Intralaboral:</strong></p>
<ul>
    <li><strong>Forma A:</strong> Para jefes, profesionales y técnicos (123 ítems)</li>
    <li><strong>Forma B:</strong> Para auxiliares y operarios (97 ítems)</li>
</ul>
<p><strong>Cuestionario Extralaboral:</strong> 31 ítems que evalúan condiciones del entorno extralaboral.</p>
<p><strong>Cuestionario de Estrés:</strong> 31 ítems que evalúan síntomas de estrés.</p>

<h3>4.3 Aplicación</h3>
<p>
Los cuestionarios fueron aplicados de forma digital a través de la plataforma PsyRisk,
garantizando la confidencialidad y anonimato de las respuestas.
</p>

<h3>4.4 Procesamiento y Análisis</h3>
<p>
Los datos fueron procesados automáticamente por el sistema, aplicando los baremos y tablas de calificación
establecidos en la batería de instrumentos del Ministerio del Trabajo.
</p>
<div class='page-break'></div>
";
    }

    /**
     * Generar sección de variables sociodemográficas
     */
    private function generateDemographicSection()
    {
        $html = "<h1>5. VARIABLES SOCIODEMOGRÁFICAS Y OCUPACIONALES</h1>";

        // Género
        $genderCounts = $this->getDistribution('gender');
        $html .= $this->generateDemographicTable('Género', $genderCounts);

        // Edad
        $html .= "<h3>5.2 Distribución por Edad</h3>";
        $avgAge = $this->getAverage('age');
        $html .= "<p>La edad promedio de los trabajadores evaluados es de <strong>" . number_format($avgAge, 1) . " años</strong>.</p>";

        // Estado civil
        $maritalCounts = $this->getDistribution('marital_status');
        $html .= $this->generateDemographicTable('Estado Civil', $maritalCounts);

        // Nivel de estudios
        $educationCounts = $this->getDistribution('education_level');
        $html .= $this->generateDemographicTable('Nivel de Estudios', $educationCounts);

        // Departamento/Área
        $deptCounts = $this->getDistribution('department');
        $html .= $this->generateDemographicTable('Departamento/Área', $deptCounts);

        // Tipo de cargo
        $positionTypeCounts = $this->getDistribution('position_type');
        $html .= $this->generateDemographicTable('Tipo de Cargo', $positionTypeCounts);

        // Tipo de contrato
        $contractCounts = $this->getDistribution('contract_type');
        $html .= $this->generateDemographicTable('Tipo de Contrato', $contractCounts);

        $html .= "<div class='page-break'></div>";

        return $html;
    }

    /**
     * Generar tabla demográfica
     */
    private function generateDemographicTable($title, $data)
    {
        $html = "<h3>5. {$title}</h3>";
        $html .= "<table class='stats-table'>";
        $html .= "<thead><tr><th>{$title}</th><th>Cantidad</th><th>Porcentaje</th></tr></thead>";
        $html .= "<tbody>";

        $total = array_sum($data);
        foreach ($data as $key => $value) {
            $percentage = $total > 0 ? ($value / $total) * 100 : 0;
            $label = $key ?: 'No especificado';
            $html .= "<tr>";
            $html .= "<td>" . esc($label) . "</td>";
            $html .= "<td>{$value}</td>";
            $html .= "<td>" . number_format($percentage, 1) . "%</td>";
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";

        return $html;
    }

    /**
     * Obtener distribución de valores
     */
    private function getDistribution($field)
    {
        $counts = [];
        foreach ($this->results as $result) {
            $value = $result[$field] ?? 'No especificado';
            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }
        arsort($counts);
        return $counts;
    }

    /**
     * Obtener promedio
     */
    private function getAverage($field)
    {
        $sum = 0;
        $count = 0;
        foreach ($this->results as $result) {
            if (isset($result[$field]) && is_numeric($result[$field])) {
                $sum += $result[$field];
                $count++;
            }
        }
        return $count > 0 ? $sum / $count : 0;
    }

    // Continuará con más métodos...

    /**
     * Generar sección de riesgo general
     */
    private function generateGeneralRiskSection()
    {
        // TODO: Implementar análisis general
        return "<h1>6. RESULTADOS GENERALES DE RIESGO PSICOSOCIAL</h1><p>Sección en desarrollo...</p><div class='page-break'></div>";
    }

    /**
     * Generar sección intralaboral
     */
    private function generateIntralaboralSection()
    {
        // TODO: Implementar análisis intralaboral completo
        return "<h1>7. RIESGO PSICOSOCIAL INTRALABORAL</h1><p>Sección en desarrollo...</p><div class='page-break'></div>";
    }

    /**
     * Generar sección extralaboral
     */
    private function generateExtralaboralSection()
    {
        // TODO: Implementar análisis extralaboral completo
        return "<h1>8. RIESGO PSICOSOCIAL EXTRALABORAL</h1><p>Sección en desarrollo...</p><div class='page-break'></div>";
    }

    /**
     * Generar sección de estrés
     */
    private function generateEstresSection()
    {
        // TODO: Implementar análisis de estrés completo
        return "<h1>9. NIVEL DE ESTRÉS</h1><p>Sección en desarrollo...</p><div class='page-break'></div>";
    }

    /**
     * Generar recomendaciones
     */
    private function generateRecommendationsSection()
    {
        // TODO: Implementar recomendaciones
        return "<h1>10. RECOMENDACIONES Y PLAN DE INTERVENCIÓN</h1><p>Sección en desarrollo...</p>";
    }
}
