<?php

namespace App\Controllers\Pdf\Estaticas;

use App\Controllers\Pdf\PdfBaseController;

/**
 * Controller para las páginas introductorias del informe PDF
 * Incluye: Introducción, Marco Conceptual, Marco Legal, Objetivos, Metodología
 */
class IntroduccionController extends PdfBaseController
{
    /**
     * Renderiza todas las páginas introductorias (para el PDF completo)
     */
    public function render($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);

        // Obtener estadísticas de participación
        $stats = $this->getParticipationStats($batteryServiceId);

        $html = '';

        // Página 1: Introducción + Marco Conceptual
        $html .= $this->renderView('pdf/estaticas/introduccion/introduccion', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'formaA' => $stats['forma_a'] ?? 0,
            'formaB' => $stats['forma_b'] ?? 0,
        ]);

        // Página 2: Condiciones Intralaborales
        $html .= $this->renderView('pdf/estaticas/introduccion/condiciones_intralaborales');

        // Página 3: Condiciones Extralaborales + Individuales
        $html .= $this->renderView('pdf/estaticas/introduccion/condiciones_extralaborales');

        // Página 4-5: Marco Legal
        $html .= $this->renderView('pdf/estaticas/introduccion/marco_legal');

        // Página 6: Objetivos
        $html .= $this->renderView('pdf/estaticas/introduccion/objetivos');

        // Página 7-8: Metodología
        $html .= $this->renderView('pdf/estaticas/introduccion/metodologia', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'formaA' => $stats['forma_a'] ?? 0,
            'formaB' => $stats['forma_b'] ?? 0,
        ]);

        return $html;
    }

    /**
     * Preview de todas las páginas introductorias
     */
    public function preview($batteryServiceId)
    {
        $html = $this->render($batteryServiceId);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Introducción y Marco Teórico'
        ]);
    }

    /**
     * Preview individual de Introducción
     */
    public function previewIntroduccion($batteryServiceId)
    {
        $this->initializeData($batteryServiceId);
        $stats = $this->getParticipationStats($batteryServiceId);

        $html = $this->renderView('pdf/estaticas/introduccion/introduccion', [
            'totalParticipantes' => $stats['total'] ?? 0,
            'formaA' => $stats['forma_a'] ?? 0,
            'formaB' => $stats['forma_b'] ?? 0,
        ]);

        return view('pdf/_partials/preview_wrapper', [
            'content' => $html,
            'pageTitle' => 'Preview: Introducción'
        ]);
    }
}
