<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

/**
 * Test de Validacion de Baremos
 *
 * Objetivo: Validar que los baremos en las librerias coinciden con las tablas
 * oficiales de la Resolucion 2764/2022 (antes 2404/2019)
 *
 * Ejecutar: php vendor/bin/phpunit tests/unit/BaremosValidationTest.php
 */
class BaremosValidationTest extends CIUnitTestCase
{
    // =========================================================================
    // INTRALABORAL TOTAL (Tabla 33)
    // =========================================================================

    /**
     * TEST: Baremo Intralaboral Total Forma A (Tabla 33)
     */
    public function testBaremosIntralaboralTotalFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoTotal();

        // Valores esperados segun Tabla 33 - Forma A (Jefes, profesionales, tecnicos)
        $esperado = [
            'sin_riesgo'      => [0.0, 19.7],
            'riesgo_bajo'     => [19.8, 25.8],
            'riesgo_medio'    => [25.9, 31.5],
            'riesgo_alto'     => [31.6, 38.0],
            'riesgo_muy_alto' => [38.1, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Intralaboral Total Forma A no coincide con Tabla 33');
    }

    /**
     * TEST: Baremo Intralaboral Total Forma B (Tabla 33)
     */
    public function testBaremosIntralaboralTotalFormaB()
    {
        $baremoLibreria = IntralaboralBScoring::getBaremoTotal();

        // Valores esperados segun Tabla 33 - Forma B (Auxiliares, operarios)
        $esperado = [
            'sin_riesgo'      => [0.0, 20.6],
            'riesgo_bajo'     => [20.7, 26.0],
            'riesgo_medio'    => [26.1, 31.2],
            'riesgo_alto'     => [31.3, 38.7],
            'riesgo_muy_alto' => [38.8, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Intralaboral Total Forma B no coincide con Tabla 33');
    }

    // =========================================================================
    // DOMINIOS FORMA A (Tabla 31)
    // =========================================================================

    /**
     * TEST: Baremo Dominio Liderazgo y Relaciones Sociales - Forma A (Tabla 31)
     */
    public function testBaremoDominioLiderazgoFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoDominio('liderazgo_relaciones_sociales');

        $esperado = [
            'sin_riesgo'      => [0.0, 9.1],
            'riesgo_bajo'     => [9.2, 17.7],
            'riesgo_medio'    => [17.8, 25.6],
            'riesgo_alto'     => [25.7, 34.8],
            'riesgo_muy_alto' => [34.9, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Liderazgo Forma A no coincide con Tabla 31');
    }

    /**
     * TEST: Baremo Dominio Control - Forma A (Tabla 31)
     */
    public function testBaremoDominioControlFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoDominio('control');

        $esperado = [
            'sin_riesgo'      => [0.0, 10.7],
            'riesgo_bajo'     => [10.8, 19.0],
            'riesgo_medio'    => [19.1, 29.8],
            'riesgo_alto'     => [29.9, 40.5],
            'riesgo_muy_alto' => [40.6, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Control Forma A no coincide con Tabla 31');
    }

    /**
     * TEST: Baremo Dominio Demandas - Forma A (Tabla 31)
     */
    public function testBaremoDominioDemandasFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoDominio('demandas');

        $esperado = [
            'sin_riesgo'      => [0.0, 28.5],
            'riesgo_bajo'     => [28.6, 35.0],
            'riesgo_medio'    => [35.1, 41.5],
            'riesgo_alto'     => [41.6, 47.5],
            'riesgo_muy_alto' => [47.6, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Demandas Forma A no coincide con Tabla 31');
    }

    /**
     * TEST: Baremo Dominio Recompensas - Forma A (Tabla 31)
     */
    public function testBaremoDominioRecompensasFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoDominio('recompensas');

        $esperado = [
            'sin_riesgo'      => [0.0, 4.5],
            'riesgo_bajo'     => [4.6, 11.4],
            'riesgo_medio'    => [11.5, 20.5],
            'riesgo_alto'     => [20.6, 29.5],
            'riesgo_muy_alto' => [29.6, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Recompensas Forma A no coincide con Tabla 31');
    }

    // =========================================================================
    // DOMINIOS FORMA B (Tabla 32)
    // =========================================================================

    /**
     * TEST: Baremo Dominio Liderazgo y Relaciones Sociales - Forma B (Tabla 32)
     */
    public function testBaremoDominioLiderazgoFormaB()
    {
        $baremoLibreria = IntralaboralBScoring::getBaremoDominio('liderazgo_relaciones_sociales');

        $esperado = [
            'sin_riesgo'      => [0.0, 10.0],
            'riesgo_bajo'     => [10.1, 17.5],
            'riesgo_medio'    => [17.6, 25.0],
            'riesgo_alto'     => [25.1, 35.0],
            'riesgo_muy_alto' => [35.1, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Liderazgo Forma B no coincide con Tabla 32');
    }

    /**
     * TEST: Baremo Dominio Control - Forma B (Tabla 32)
     */
    public function testBaremoDominioControlFormaB()
    {
        $baremoLibreria = IntralaboralBScoring::getBaremoDominio('control');

        $esperado = [
            'sin_riesgo'      => [0.0, 8.8],
            'riesgo_bajo'     => [8.9, 16.3],
            'riesgo_medio'    => [16.4, 23.8],
            'riesgo_alto'     => [23.9, 31.3],
            'riesgo_muy_alto' => [31.4, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Control Forma B no coincide con Tabla 32');
    }

    /**
     * TEST: Baremo Dominio Demandas - Forma B (Tabla 32)
     */
    public function testBaremoDominioDemandasFormaB()
    {
        $baremoLibreria = IntralaboralBScoring::getBaremoDominio('demandas');

        $esperado = [
            'sin_riesgo'      => [0.0, 26.9],
            'riesgo_bajo'     => [27.0, 33.3],
            'riesgo_medio'    => [33.4, 37.8],
            'riesgo_alto'     => [37.9, 44.2],
            'riesgo_muy_alto' => [44.3, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Demandas Forma B no coincide con Tabla 32');
    }

    /**
     * TEST: Baremo Dominio Recompensas - Forma B (Tabla 32)
     */
    public function testBaremoDominioRecompensasFormaB()
    {
        $baremoLibreria = IntralaboralBScoring::getBaremoDominio('recompensas');

        $esperado = [
            'sin_riesgo'      => [0.0, 2.5],
            'riesgo_bajo'     => [2.6, 10.0],
            'riesgo_medio'    => [10.1, 17.5],
            'riesgo_alto'     => [17.6, 27.5],
            'riesgo_muy_alto' => [27.6, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dominio Recompensas Forma B no coincide con Tabla 32');
    }

    // =========================================================================
    // DIMENSIONES FORMA A (Tabla 29) - Muestra representativa
    // =========================================================================

    /**
     * TEST: Baremo Dimension Caracteristicas del Liderazgo - Forma A (Tabla 29)
     */
    public function testBaremoDimensionCaracteristicasLiderazgoFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoDimension('caracteristicas_liderazgo');

        $esperado = [
            'sin_riesgo'      => [0.0, 3.8],
            'riesgo_bajo'     => [3.9, 15.4],
            'riesgo_medio'    => [15.5, 30.8],
            'riesgo_alto'     => [30.9, 46.2],
            'riesgo_muy_alto' => [46.3, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dimension Caracteristicas Liderazgo Forma A no coincide con Tabla 29');
    }

    /**
     * TEST: Baremo Dimension Claridad de Rol - Forma A (Tabla 29)
     */
    public function testBaremoDimensionClaridadRolFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoDimension('claridad_rol');

        $esperado = [
            'sin_riesgo'      => [0.0, 0.9],
            'riesgo_bajo'     => [1.0, 10.7],
            'riesgo_medio'    => [10.8, 21.4],
            'riesgo_alto'     => [21.5, 39.3],
            'riesgo_muy_alto' => [39.4, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dimension Claridad Rol Forma A no coincide con Tabla 29');
    }

    /**
     * TEST: Baremo Dimension Demandas Cuantitativas - Forma A (Tabla 29)
     */
    public function testBaremoDimensionDemandasCuantitativasFormaA()
    {
        $baremoLibreria = IntralaboralAScoring::getBaremoDimension('demandas_cuantitativas');

        $esperado = [
            'sin_riesgo'      => [0.0, 25.0],
            'riesgo_bajo'     => [25.1, 33.3],
            'riesgo_medio'    => [33.4, 45.8],
            'riesgo_alto'     => [45.9, 54.2],
            'riesgo_muy_alto' => [54.3, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dimension Demandas Cuantitativas Forma A no coincide con Tabla 29');
    }

    // =========================================================================
    // DIMENSIONES FORMA B (Tabla 30) - Muestra representativa
    // =========================================================================

    /**
     * TEST: Baremo Dimension Caracteristicas del Liderazgo - Forma B (Tabla 30)
     */
    public function testBaremoDimensionCaracteristicasLiderazgoFormaB()
    {
        $baremoLibreria = IntralaboralBScoring::getBaremoDimension('caracteristicas_liderazgo');

        $esperado = [
            'sin_riesgo'      => [0.0, 3.8],
            'riesgo_bajo'     => [3.9, 13.5],
            'riesgo_medio'    => [13.6, 25.0],
            'riesgo_alto'     => [25.1, 38.5],
            'riesgo_muy_alto' => [38.6, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dimension Caracteristicas Liderazgo Forma B no coincide con Tabla 30');
    }

    /**
     * TEST: Baremo Dimension Claridad de Rol - Forma B (Tabla 30)
     */
    public function testBaremoDimensionClaridadRolFormaB()
    {
        $baremoLibreria = IntralaboralBScoring::getBaremoDimension('claridad_rol');

        $esperado = [
            'sin_riesgo'      => [0.0, 0.9],
            'riesgo_bajo'     => [1.0, 5.0],
            'riesgo_medio'    => [5.1, 15.0],
            'riesgo_alto'     => [15.1, 30.0],
            'riesgo_muy_alto' => [30.1, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Dimension Claridad Rol Forma B no coincide con Tabla 30');
    }

    // =========================================================================
    // EXTRALABORAL (Tablas 17 y 18)
    // =========================================================================

    /**
     * TEST: Baremo Extralaboral Total - Jefes/Profesionales (Tabla 17)
     */
    public function testBaremosExtralaboralTotalJefes()
    {
        $baremoLibreria = ExtralaboralScoring::getBaremoTotal('A');

        $esperado = [
            'sin_riesgo'      => [0.0, 11.3],
            'riesgo_bajo'     => [11.4, 16.9],
            'riesgo_medio'    => [17.0, 22.6],
            'riesgo_alto'     => [22.7, 29.0],
            'riesgo_muy_alto' => [29.1, 100]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Extralaboral Total Jefes no coincide con Tabla 17');
    }

    /**
     * TEST: Baremo Extralaboral Total - Auxiliares/Operarios (Tabla 18)
     */
    public function testBaremosExtralaboralTotalAuxiliares()
    {
        $baremoLibreria = ExtralaboralScoring::getBaremoTotal('B');

        $esperado = [
            'sin_riesgo'      => [0.0, 12.9],
            'riesgo_bajo'     => [13.0, 17.7],
            'riesgo_medio'    => [17.8, 24.2],
            'riesgo_alto'     => [24.3, 32.3],
            'riesgo_muy_alto' => [32.4, 100]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Extralaboral Total Auxiliares no coincide con Tabla 18');
    }

    /**
     * TEST: Baremo Extralaboral Dimension Tiempo Fuera del Trabajo - Jefes (Tabla 17)
     */
    public function testBaremosExtralaboralTiempoFueraJefes()
    {
        $baremoLibreria = ExtralaboralScoring::getBaremoDimension('tiempo_fuera_trabajo', 'A');

        $esperado = [
            'sin_riesgo'      => [0.0, 6.3],
            'riesgo_bajo'     => [6.4, 25.0],
            'riesgo_medio'    => [25.1, 37.5],
            'riesgo_alto'     => [37.6, 50.0],
            'riesgo_muy_alto' => [50.1, 100]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Extralaboral Tiempo Fuera Trabajo Jefes no coincide con Tabla 17');
    }

    // =========================================================================
    // ESTRES (Tabla 6)
    // =========================================================================

    /**
     * TEST: Baremo Estres - Jefes/Profesionales/Tecnicos (Tabla 6)
     */
    public function testBaremosEstresJefes()
    {
        $baremoLibreria = EstresScoring::getBaremoA();

        $esperado = [
            'muy_bajo'  => [0.0, 7.8],
            'bajo'      => [7.9, 12.6],
            'medio'     => [12.7, 17.7],
            'alto'      => [17.8, 25.0],
            'muy_alto'  => [25.1, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Estres Jefes no coincide con Tabla 6');
    }

    /**
     * TEST: Baremo Estres - Auxiliares/Operarios (Tabla 6)
     */
    public function testBaremosEstresAuxiliares()
    {
        $baremoLibreria = EstresScoring::getBaremoB();

        $esperado = [
            'muy_bajo'  => [0.0, 6.5],
            'bajo'      => [6.6, 11.8],
            'medio'     => [11.9, 17.0],
            'alto'      => [17.1, 23.4],
            'muy_alto'  => [23.5, 100.0]
        ];

        $this->assertEquals($esperado, $baremoLibreria,
            'Baremo Estres Auxiliares no coincide con Tabla 6');
    }

    // =========================================================================
    // VALIDACIONES DE ESTRUCTURA
    // =========================================================================

    /**
     * TEST: Todos los dominios Forma A tienen baremos
     */
    public function testTodosDominiosFormaATienenBaremos()
    {
        $dominiosEsperados = [
            'liderazgo_relaciones_sociales',
            'control',
            'demandas',
            'recompensas'
        ];

        foreach ($dominiosEsperados as $dominio) {
            $baremo = IntralaboralAScoring::getBaremoDominio($dominio);
            $this->assertNotNull($baremo, "Baremo para dominio '{$dominio}' Forma A no existe");
            $this->assertCount(5, $baremo, "Baremo para '{$dominio}' Forma A debe tener 5 niveles");
        }
    }

    /**
     * TEST: Todos los dominios Forma B tienen baremos
     */
    public function testTodosDominiosFormaBTienenBaremos()
    {
        $dominiosEsperados = [
            'liderazgo_relaciones_sociales',
            'control',
            'demandas',
            'recompensas'
        ];

        foreach ($dominiosEsperados as $dominio) {
            $baremo = IntralaboralBScoring::getBaremoDominio($dominio);
            $this->assertNotNull($baremo, "Baremo para dominio '{$dominio}' Forma B no existe");
            $this->assertCount(5, $baremo, "Baremo para '{$dominio}' Forma B debe tener 5 niveles");
        }
    }

    /**
     * TEST: Estructura de baremos es valida (5 niveles con rangos [min, max])
     */
    public function testEstructuraBaremosValida()
    {
        $baremo = IntralaboralAScoring::getBaremoTotal();

        $nivelesEsperados = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];

        foreach ($nivelesEsperados as $nivel) {
            $this->assertArrayHasKey($nivel, $baremo, "Falta nivel '{$nivel}' en baremo");
            $this->assertCount(2, $baremo[$nivel], "Rango de '{$nivel}' debe tener [min, max]");
            $this->assertLessThanOrEqual($baremo[$nivel][1], $baremo[$nivel][0],
                "Min debe ser <= Max en nivel '{$nivel}'");
        }
    }

    /**
     * TEST: Rangos de baremos son continuos (no hay gaps)
     */
    public function testRangosBaremosContinuos()
    {
        $baremo = IntralaboralAScoring::getBaremoTotal();

        $niveles = ['sin_riesgo', 'riesgo_bajo', 'riesgo_medio', 'riesgo_alto', 'riesgo_muy_alto'];

        for ($i = 0; $i < count($niveles) - 1; $i++) {
            $nivelActual = $niveles[$i];
            $nivelSiguiente = $niveles[$i + 1];

            $maxActual = $baremo[$nivelActual][1];
            $minSiguiente = $baremo[$nivelSiguiente][0];

            // El minimo del siguiente debe ser el maximo del actual + 0.1
            $diferencia = round($minSiguiente - $maxActual, 1);
            $this->assertEquals(0.1, $diferencia,
                "Gap entre '{$nivelActual}' y '{$nivelSiguiente}': esperado 0.1, obtenido {$diferencia}");
        }
    }
}
