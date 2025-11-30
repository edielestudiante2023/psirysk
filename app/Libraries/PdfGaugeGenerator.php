<?php

namespace App\Libraries;

/**
 * Generador de gauges SVG dinámicos para PDF
 * Diseñado para ser compatible con DomPDF
 *
 * Los segmentos del arco son PROPORCIONALES a los rangos del baremo real
 * según la Resolución 2404/2019 del Ministerio del Trabajo de Colombia.
 */
class PdfGaugeGenerator
{
    /**
     * Colores por nivel de riesgo
     */
    protected $riskColors = [
        'sin_riesgo'      => '#4CAF50',
        'riesgo_bajo'     => '#8BC34A',
        'riesgo_medio'    => '#FFEB3B',
        'riesgo_alto'     => '#FF9800',
        'riesgo_muy_alto' => '#F44336',
    ];

    /**
     * Etiquetas cortas para el gauge
     */
    protected $riskLabelsShort = [
        'sin_riesgo'      => 'SR',
        'riesgo_bajo'     => 'RB',
        'riesgo_medio'    => 'RM',
        'riesgo_alto'     => 'RA',
        'riesgo_muy_alto' => 'RMA',
    ];

    /**
     * Genera un gauge SVG dinámico con segmentos proporcionales al baremo
     *
     * @param float $value Valor del puntaje (0-100)
     * @param array $baremo Baremos con rangos por nivel de riesgo
     * @return string Data URI de la imagen SVG (base64)
     */
    public function generate($value, $baremo)
    {
        // Determinar el nivel basado en el valor
        $nivel = $this->getNivelFromValue($value, $baremo);
        $color = $this->riskColors[$nivel] ?? '#999999';

        // Generar SVG dinámico
        $svg = $this->generateDynamicSvg($value, $baremo, $color);

        // Convertir a data URI
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Genera un SVG con segmentos dinámicos según el baremo real
     *
     * Geometría del gauge:
     * - Semicírculo superior de 180° (izquierda=0, derecha=100)
     * - Verde (sin_riesgo) a la izquierda
     * - Rojo (muy_alto) a la derecha
     */
    protected function generateDynamicSvg($value, $baremo, $currentColor)
    {
        // Parámetros del gauge
        $cx = 100;           // Centro X
        $cy = 90;            // Centro Y
        $radius = 70;        // Radio del arco exterior
        $labelRadius = 50;   // Radio para las etiquetas (interior)
        $needleLength = 60;  // Longitud de la aguja ($radius - 10)

        // Calcular ángulo de la aguja
        // El semicírculo va de 180° (izquierda, valor=0) a 0° (derecha, valor=100)
        $percentage = min(100, max(0, $value));
        $needleAngleDeg = 180 - ($percentage * 1.8);  // 180° total / 100 = 1.8° por unidad
        $needleAngleRad = deg2rad($needleAngleDeg);

        // Posición de la punta de la aguja
        $needleX = $cx + ($needleLength * cos($needleAngleRad));
        $needleY = $cy - ($needleLength * sin($needleAngleRad));  // Negativo porque Y crece hacia abajo en SVG

        // Función para calcular punto en el arco
        $getPoint = function($pct, $r = null) use ($cx, $cy, $radius) {
            $r = $r ?? $radius;
            $angleDeg = 180 - ($pct * 1.8);
            $angleRad = deg2rad($angleDeg);
            return [
                round($cx + $r * cos($angleRad), 2),
                round($cy - $r * sin($angleRad), 2)
            ];
        };

        // Generar los segmentos del arco y etiquetas
        $levels = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];
        $pathsSvg = '';
        $labelsSvg = '';

        foreach ($levels as $lvl) {
            if (!isset($baremo[$lvl])) continue;

            $startPct = $baremo[$lvl][0];  // Valor inicial del rango
            $endPct = min(100, $baremo[$lvl][1]);  // Valor final del rango

            $p1 = $getPoint($startPct);  // Punto inicial del arco
            $p2 = $getPoint($endPct);    // Punto final del arco

            // Path SVG del arco
            // M = Move to (punto inicial)
            // A = Arc (rx, ry, x-axis-rotation, large-arc-flag, sweep-flag, x, y)
            $pathsSvg .= '<path d="M ' . $p1[0] . ' ' . $p1[1] .
                         ' A ' . $radius . ' ' . $radius .
                         ' 0 0 1 ' . $p2[0] . ' ' . $p2[1] .
                         '" fill="none" stroke="' . $this->riskColors[$lvl] .
                         '" stroke-width="12"/>' . "\n    ";

            // Etiqueta + Rango en UNA SOLA LINEA: "RM: 25.9-31.5"
            $midPct = ($startPct + $endPct) / 2;
            $labelPos = $getPoint($midPct, $labelRadius);
            $labelsSvg .= '<text x="' . $labelPos[0] . '" y="' . $labelPos[1] .
                         '" font-family="Arial" font-size="4" text-anchor="middle" fill="#333">' .
                         $this->riskLabelsShort[$lvl] . ': ' . $startPct . '-' . $endPct . '</text>' . "\n    ";
        }

        // Etiquetas de extremos (0 y 100)
        $labelsSvg .= '<text x="28" y="95" font-family="Arial" font-size="5" fill="#333">0</text>' . "\n    ";
        $labelsSvg .= '<text x="168" y="95" font-family="Arial" font-size="5" fill="#333">100</text>' . "\n    ";

        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="120" viewBox="0 0 200 120">
    <!-- Fondo gris del arco -->
    <path d="M 30 90 A 70 70 0 0 1 170 90" fill="none" stroke="#E8E8E8" stroke-width="12"/>

    <!-- Segmentos dinámicos según baremo -->
    ' . $pathsSvg . '

    <!-- Etiquetas de cada segmento -->
    ' . $labelsSvg . '

    <!-- Aguja apuntando a: ' . number_format($value, 1) . ' -->
    <line x1="' . $cx . '" y1="' . $cy . '" x2="' . round($needleX, 2) . '" y2="' . round($needleY, 2) . '"
          stroke="#333" stroke-width="3" stroke-linecap="round"/>

    <!-- Centro de la aguja -->
    <circle cx="' . $cx . '" cy="' . $cy . '" r="8" fill="#333"/>

    <!-- Valor actual -->
    <text x="' . $cx . '" y="115" font-family="Arial, sans-serif" font-size="14" font-weight="bold"
          text-anchor="middle" fill="' . $currentColor . '">' . number_format($value, 1) . '</text>
</svg>';

        return $svg;
    }

    /**
     * Obtiene el nivel de riesgo basado en el valor y baremo
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
