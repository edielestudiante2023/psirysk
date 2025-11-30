<?php

namespace App\Libraries;

/**
 * Generador de gauges para PDF usando imágenes base64
 * Diseñado para ser compatible con DomPDF
 * Genera una representación visual simple del nivel de riesgo
 */
class PdfGaugeGenerator
{
    /**
     * Colores por nivel de riesgo
     */
    protected $riskColors = [
        'sin_riesgo' => '#4CAF50',
        'riesgo_bajo' => '#8BC34A',
        'riesgo_medio' => '#FFEB3B',
        'riesgo_alto' => '#FF9800',
        'riesgo_muy_alto' => '#F44336',
    ];

    /**
     * Genera una imagen de gauge como data URI base64
     * Como DomPDF no soporta bien SVG complejos, generamos una imagen PNG simple
     *
     * @param float $value Valor del puntaje
     * @param array $baremo Baremos con rangos por nivel
     * @return string Data URI de la imagen
     */
    public function generate($value, $baremo)
    {
        // Determinar el nivel basado en el valor
        $nivel = $this->getNivelFromValue($value, $baremo);
        $color = $this->riskColors[$nivel] ?? '#999999';

        // Calcular el porcentaje para la posición de la aguja (0-100)
        $percentage = $this->calculatePercentage($value, $baremo);

        // Generar SVG simple que DomPDF puede manejar
        $svg = $this->generateSimpleSvg($percentage, $color, $value);

        // Convertir a data URI
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Genera un SVG simple compatible con DomPDF
     */
    protected function generateSimpleSvg($percentage, $color, $value)
    {
        // Calcular el ángulo de la aguja (-90 a 90 grados)
        $angle = -90 + ($percentage * 1.8); // 180 grados total

        // Centro del gauge
        $cx = 100;
        $cy = 90;
        $radius = 70;

        // Calcular posición de la punta de la aguja
        $radians = deg2rad($angle);
        $needleX = $cx + (($radius - 10) * cos($radians));
        $needleY = $cy + (($radius - 10) * sin($radians));

        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="120" viewBox="0 0 200 120">
    <!-- Fondo del arco segmentado -->
    <path d="M 30 90 A 70 70 0 0 1 170 90" fill="none" stroke="#E8E8E8" stroke-width="12"/>

    <!-- Segmento Sin Riesgo (verde oscuro) -->
    <path d="M 30 90 A 70 70 0 0 1 50 45" fill="none" stroke="#4CAF50" stroke-width="12"/>

    <!-- Segmento Riesgo Bajo (verde claro) -->
    <path d="M 50 45 A 70 70 0 0 1 85 25" fill="none" stroke="#8BC34A" stroke-width="12"/>

    <!-- Segmento Riesgo Medio (amarillo) -->
    <path d="M 85 25 A 70 70 0 0 1 115 25" fill="none" stroke="#FFEB3B" stroke-width="12"/>

    <!-- Segmento Riesgo Alto (naranja) -->
    <path d="M 115 25 A 70 70 0 0 1 150 45" fill="none" stroke="#FF9800" stroke-width="12"/>

    <!-- Segmento Riesgo Muy Alto (rojo) -->
    <path d="M 150 45 A 70 70 0 0 1 170 90" fill="none" stroke="#F44336" stroke-width="12"/>

    <!-- Aguja -->
    <line x1="' . $cx . '" y1="' . $cy . '" x2="' . $needleX . '" y2="' . $needleY . '"
          stroke="#333" stroke-width="3" stroke-linecap="round"/>

    <!-- Centro de la aguja -->
    <circle cx="' . $cx . '" cy="' . $cy . '" r="8" fill="#333"/>

    <!-- Valor -->
    <text x="' . $cx . '" y="115" font-family="Arial, sans-serif" font-size="14" font-weight="bold"
          text-anchor="middle" fill="' . $color . '">' . number_format($value, 1) . '</text>
</svg>';

        return $svg;
    }

    /**
     * Calcula el porcentaje basado en el valor y los baremos
     */
    protected function calculatePercentage($value, $baremo)
    {
        // Encontrar el rango total
        $min = 0;
        $max = 100;

        // Calcular porcentaje simple (0-100)
        $percentage = min(100, max(0, $value));

        return $percentage;
    }

    /**
     * Obtiene el nivel de riesgo basado en el valor
     */
    protected function getNivelFromValue($value, $baremo)
    {
        foreach ($baremo as $nivel => $rango) {
            if ($value >= $rango[0] && $value <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo';
    }
}
