<?php

namespace App\Controllers\PdfEjecutivo;

/**
 * Controlador de Tabla de Contenido para el Informe Ejecutivo PDF
 * Sección 2: Índice visual SIN números de página
 * NOTA: Diseño compacto para caber en UNA página
 */
class ContenidoController extends PdfEjecutivoBaseController
{
    /**
     * Preview de la tabla de contenido en navegador
     */
    public function preview($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePreview($html, 'Preview: Tabla de Contenido');
    }

    /**
     * Descarga PDF de la tabla de contenido
     */
    public function download($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $html = $this->render($batteryServiceId);

        return $this->generatePdf($html, "Contenido.pdf");
    }

    /**
     * Renderiza el HTML de la tabla de contenido (compacto)
     */
    public function render($batteryServiceId)
    {
        if (empty($this->companyData)) {
            $this->initializeData($batteryServiceId);
        }

        $html = '
<!-- TABLA DE CONTENIDO -->
<h1 style="font-size: 14pt; margin: 0 0 15pt 0; padding-bottom: 5pt;">Contenido</h1>

<table style="width: 100%; border: none; font-size: 10pt; line-height: 1.4;">
    <tr>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699; width: 25pt;">1.</td>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">Introducción</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Marco Conceptual</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Marco Legal</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Objetivos</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Metodología</td>
    </tr>

    <tr><td colspan="2" style="border: none; padding: 3pt 0;"></td></tr>

    <tr>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">2.</td>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">Características Sociodemográficas</td>
    </tr>

    <tr><td colspan="2" style="border: none; padding: 3pt 0;"></td></tr>

    <tr>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">3.</td>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">Resultados Intralaboral</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Resultados Totales (Forma A y B)</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Dominios Intralaborales</td>
    </tr>
    <tr>
        <td style="border: none; padding: 1pt 0 1pt 30pt;"></td>
        <td style="border: none; padding: 1pt 0; color: #777; font-size: 8pt;">- Liderazgo y Relaciones Sociales en el Trabajo</td>
    </tr>
    <tr>
        <td style="border: none; padding: 1pt 0 1pt 30pt;"></td>
        <td style="border: none; padding: 1pt 0; color: #777; font-size: 8pt;">- Control sobre el Trabajo</td>
    </tr>
    <tr>
        <td style="border: none; padding: 1pt 0 1pt 30pt;"></td>
        <td style="border: none; padding: 1pt 0; color: #777; font-size: 8pt;">- Demandas del Trabajo</td>
    </tr>
    <tr>
        <td style="border: none; padding: 1pt 0 1pt 30pt;"></td>
        <td style="border: none; padding: 1pt 0; color: #777; font-size: 8pt;">- Recompensas</td>
    </tr>

    <tr><td colspan="2" style="border: none; padding: 3pt 0;"></td></tr>

    <tr>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">4.</td>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">Resultados Extralaboral</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Resultados Totales</td>
    </tr>
    <tr>
        <td style="border: none; padding: 2pt 0 2pt 15pt;"></td>
        <td style="border: none; padding: 2pt 0; color: #555; font-size: 9pt;">Dimensiones Extralaborales</td>
    </tr>

    <tr><td colspan="2" style="border: none; padding: 3pt 0;"></td></tr>

    <tr>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">5.</td>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">Evaluación del Estrés</td>
    </tr>

    <tr><td colspan="2" style="border: none; padding: 3pt 0;"></td></tr>

    <tr>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">6.</td>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">Conclusiones y Recomendaciones</td>
    </tr>

    <tr><td colspan="2" style="border: none; padding: 3pt 0;"></td></tr>

    <tr>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">7.</td>
        <td style="border: none; padding: 4pt 0; font-weight: bold; color: #006699;">Firma del Profesional</td>
    </tr>
</table>
';

        return $html;
    }
}
