<?php

namespace App\Controllers\Pdf\Estaticas;

use App\Controllers\Pdf\PdfBaseController;

/**
 * Controller para la página de Contenido (Índice) del informe PDF
 */
class ContenidoController extends PdfBaseController
{
    /**
     * Estructura del contenido del informe
     * Nivel 1 = Sección principal, Nivel 2 = Subsección, Nivel 3 = Sub-subsección
     */
    protected function getTableOfContents()
    {
        return [
            ['title' => 'Introducción', 'page' => 4, 'level' => 1],
            ['title' => 'Marco Conceptual', 'page' => 4, 'level' => 1],
            ['title' => 'Condiciones Intralaborales:', 'page' => 5, 'level' => 2],
            ['title' => 'Condiciones Extralaborales:', 'page' => 6, 'level' => 2],
            ['title' => 'Condiciones Individuales:', 'page' => 6, 'level' => 2],
            ['title' => 'Marco Legal', 'page' => 7, 'level' => 1],
            ['title' => 'Objetivos', 'page' => 8, 'level' => 1],
            ['title' => 'Objetivo General', 'page' => 8, 'level' => 2],
            ['title' => 'Objetivos Específicos', 'page' => 8, 'level' => 2],
            ['title' => 'Metodología', 'page' => 9, 'level' => 1],
            ['title' => 'Instrumentos', 'page' => 9, 'level' => 2],
            ['title' => 'Procedimiento', 'page' => 9, 'level' => 2],
            ['title' => 'Resultados', 'page' => 10, 'level' => 1],
            ['title' => 'Conclusión Total De Aplicación Batería De Riesgo Psicosocial', 'page' => 11, 'level' => 2],
            ['title' => 'Conclusión Del Profesional', 'page' => 11, 'level' => 2],
            ['title' => 'Variables Sociodemográficas', 'page' => 12, 'level' => 2],
            ['title' => 'Resultados Ocupacionales', 'page' => 13, 'level' => 2],
            ['title' => 'Riesgo Psicosocial {EMPRESA}', 'page' => 14, 'level' => 1],
            ['title' => 'Factores De Riesgo Psicosocial General', 'page' => 17, 'level' => 1],
            ['title' => 'Riesgo Psicosocial Intralaboral', 'page' => 18, 'level' => 1],
            ['title' => 'Dominio Liderazgo Y Relaciones Sociales En El Trabajo', 'page' => 19, 'level' => 2],
            ['title' => 'Dimensión Características Del Liderazgo', 'page' => 20, 'level' => 3],
            ['title' => 'Dimensión Relaciones Sociales En El Trabajo', 'page' => 21, 'level' => 3],
            ['title' => 'Dimensión Retroalimentación Del Desempeño', 'page' => 22, 'level' => 3],
            ['title' => 'Dimensión Relación Con Los Colaboradores (Subordinados)', 'page' => 23, 'level' => 3],
            ['title' => 'Dominio Control Sobre El Trabajo', 'page' => 24, 'level' => 2],
            ['title' => 'Dimensión Claridad Del Rol', 'page' => 25, 'level' => 3],
            ['title' => 'Dimensión Capacitación', 'page' => 26, 'level' => 3],
            ['title' => 'Dimensión Participación Y Manejo Del Cambio', 'page' => 27, 'level' => 3],
            ['title' => 'Dimensión Oportunidades Para El Uso Y Desarrollo De Habilidades Y Conocimientos', 'page' => 28, 'level' => 3],
            ['title' => 'Dimensión Control Y Autonomía Sobre El Trabajo', 'page' => 29, 'level' => 3],
            ['title' => 'Dominio Demandas Del Trabajo', 'page' => 30, 'level' => 2],
            ['title' => 'Dimensión Demandas Ambientales Y De Esfuerzo Físico', 'page' => 31, 'level' => 3],
            ['title' => 'Dimensión Demandas Emocionales', 'page' => 32, 'level' => 3],
            ['title' => 'Dimensión Demandas Cuantitativas', 'page' => 33, 'level' => 3],
            ['title' => 'Dimensión Influencia Del Trabajo Sobre El Entorno Extralaboral', 'page' => 34, 'level' => 3],
            ['title' => 'Dimensión Exigencias De Responsabilidad Del Cargo', 'page' => 35, 'level' => 3],
            ['title' => 'Dimensión Demanda De Carga Mental', 'page' => 36, 'level' => 3],
            ['title' => 'Dimensión Consistencia Del Rol', 'page' => 37, 'level' => 3],
            ['title' => 'Dimensión Demandas De La Jornada De Trabajo', 'page' => 38, 'level' => 3],
            ['title' => 'Dominio Recompensas', 'page' => 39, 'level' => 2],
            ['title' => 'Dimensión Recompensas Derivadas De La Pertenencia A La Organización Y Del Trabajo Que Se Realiza', 'page' => 40, 'level' => 3],
            ['title' => 'Dimensión Reconocimiento Y Compensación', 'page' => 41, 'level' => 3],
            ['title' => 'Riesgo Psicosocial Extralaboral', 'page' => 42, 'level' => 1],
            ['title' => 'Dimensión Tiempo Fuera Del Trabajo', 'page' => 43, 'level' => 3],
            ['title' => 'Dimensión Relaciones Familiares', 'page' => 44, 'level' => 3],
            ['title' => 'Dimensión Comunicación Y Relaciones Interpersonales', 'page' => 45, 'level' => 3],
            ['title' => 'Dimensión Situación Económica Del Grupo Familiar', 'page' => 46, 'level' => 3],
            ['title' => 'Dimensión Características De La Vivienda Y De Su Entorno', 'page' => 47, 'level' => 3],
            ['title' => 'Dimensión Influencia Del Entorno Extralaboral En El Trabajo', 'page' => 48, 'level' => 3],
            ['title' => 'Dimensión Desplazamiento Vivienda – Trabajo – Vivienda', 'page' => 49, 'level' => 3],
            ['title' => 'Nivel De Estrés', 'page' => 50, 'level' => 1],
            ['title' => 'Recomendaciones Y Propuesta De Intervención', 'page' => 51, 'level' => 1],
        ];
    }

    /**
     * Renderiza las páginas de contenido (pueden ser 2 páginas)
     *
     * @param int $batteryServiceId
     * @return string HTML del contenido
     */
    public function render($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);

        $toc = $this->getTableOfContents();

        // Reemplazar {EMPRESA} con el nombre real
        foreach ($toc as &$item) {
            $item['title'] = str_replace('{EMPRESA}', $this->companyData['company_name'] ?? 'EMPRESA', $item['title']);
        }

        // Dividir en 2 páginas si es necesario (aprox 30 items por página)
        $itemsPerPage = 30;
        $pages = array_chunk($toc, $itemsPerPage);

        $html = '';
        foreach ($pages as $index => $pageItems) {
            $html .= $this->renderView('pdf/estaticas/contenido', [
                'items' => $pageItems,
                'isFirstPage' => ($index === 0),
            ]);

            if ($index < count($pages) - 1) {
                $html .= $this->pageBreak();
            }
        }

        return $html;
    }

    /**
     * Preview del contenido en navegador
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Contenido',
            'batteryServiceId' => $batteryServiceId
        ]);
    }
}
