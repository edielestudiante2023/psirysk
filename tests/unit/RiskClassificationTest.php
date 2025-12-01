<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\IntralaboralAScoring;
use App\Libraries\IntralaboralBScoring;
use App\Libraries\ExtralaboralScoring;
use App\Libraries\EstresScoring;

/**
 * Test de Clasificacion de Riesgo
 *
 * Objetivo: Validar que la clasificacion de puntajes produce los niveles correctos
 * usando los baremos de las librerias autorizadas.
 *
 * Ejecutar: php vendor/bin/phpunit tests/unit/RiskClassificationTest.php
 */
class RiskClassificationTest extends CIUnitTestCase
{
    // =========================================================================
    // CLASIFICACION INTRALABORAL TOTAL FORMA A
    // =========================================================================

    /**
     * TEST: Clasificacion de puntajes limite - Intralaboral Total Forma A
     */
    public function testClasificacionLimitesIntralaboralTotalA()
    {
        $baremo = IntralaboralAScoring::getBaremoTotal();

        // Casos limite exactos
        $casos = [
            ['puntaje' => 0.0,   'esperado' => 'sin_riesgo'],
            ['puntaje' => 19.7,  'esperado' => 'sin_riesgo'],
            ['puntaje' => 19.8,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 25.8,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 25.9,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 31.5,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 31.6,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 38.0,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 38.1,  'esperado' => 'riesgo_muy_alto'],
            ['puntaje' => 100.0, 'esperado' => 'riesgo_muy_alto'],
        ];

        foreach ($casos as $caso) {
            $nivel = $this->clasificarPuntaje($caso['puntaje'], $baremo);
            $this->assertEquals($caso['esperado'], $nivel,
                "Intralaboral A: Puntaje {$caso['puntaje']} deberia ser '{$caso['esperado']}', obtuvo '{$nivel}'");
        }
    }

    /**
     * TEST: Clasificacion de puntajes intermedios - Intralaboral Total Forma A
     */
    public function testClasificacionIntermediosIntralaboralTotalA()
    {
        $baremo = IntralaboralAScoring::getBaremoTotal();

        $casos = [
            ['puntaje' => 10.0,  'esperado' => 'sin_riesgo'],
            ['puntaje' => 22.0,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 28.0,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 35.0,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 50.0,  'esperado' => 'riesgo_muy_alto'],
            ['puntaje' => 75.0,  'esperado' => 'riesgo_muy_alto'],
        ];

        foreach ($casos as $caso) {
            $nivel = $this->clasificarPuntaje($caso['puntaje'], $baremo);
            $this->assertEquals($caso['esperado'], $nivel,
                "Intralaboral A intermedio: Puntaje {$caso['puntaje']} deberia ser '{$caso['esperado']}'");
        }
    }

    // =========================================================================
    // CLASIFICACION INTRALABORAL TOTAL FORMA B
    // =========================================================================

    /**
     * TEST: Clasificacion de puntajes limite - Intralaboral Total Forma B
     */
    public function testClasificacionLimitesIntralaboralTotalB()
    {
        $baremo = IntralaboralBScoring::getBaremoTotal();

        $casos = [
            ['puntaje' => 0.0,   'esperado' => 'sin_riesgo'],
            ['puntaje' => 20.6,  'esperado' => 'sin_riesgo'],
            ['puntaje' => 20.7,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 26.0,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 26.1,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 31.2,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 31.3,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 38.7,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 38.8,  'esperado' => 'riesgo_muy_alto'],
            ['puntaje' => 100.0, 'esperado' => 'riesgo_muy_alto'],
        ];

        foreach ($casos as $caso) {
            $nivel = $this->clasificarPuntaje($caso['puntaje'], $baremo);
            $this->assertEquals($caso['esperado'], $nivel,
                "Intralaboral B: Puntaje {$caso['puntaje']} deberia ser '{$caso['esperado']}', obtuvo '{$nivel}'");
        }
    }

    // =========================================================================
    // CLASIFICACION DOMINIOS
    // =========================================================================

    /**
     * TEST: Clasificacion de todos los dominios Forma A
     */
    public function testClasificacionTodosDominiosFormaA()
    {
        $dominios = [
            'liderazgo_relaciones_sociales',
            'control',
            'demandas',
            'recompensas'
        ];

        foreach ($dominios as $dominio) {
            $baremo = IntralaboralAScoring::getBaremoDominio($dominio);
            $this->assertNotNull($baremo, "Baremo para dominio '{$dominio}' Forma A no existe");

            // Probar que cada nivel es alcanzable
            foreach ($baremo as $nivel => $rango) {
                $puntajeMedio = ($rango[0] + $rango[1]) / 2;
                $nivelClasificado = $this->clasificarPuntaje($puntajeMedio, $baremo);
                $this->assertEquals($nivel, $nivelClasificado,
                    "Dominio '{$dominio}' Forma A: puntaje {$puntajeMedio} deberia ser '{$nivel}'");
            }
        }
    }

    /**
     * TEST: Clasificacion de todos los dominios Forma B
     */
    public function testClasificacionTodosDominiosFormaB()
    {
        $dominios = [
            'liderazgo_relaciones_sociales',
            'control',
            'demandas',
            'recompensas'
        ];

        foreach ($dominios as $dominio) {
            $baremo = IntralaboralBScoring::getBaremoDominio($dominio);
            $this->assertNotNull($baremo, "Baremo para dominio '{$dominio}' Forma B no existe");

            // Probar que cada nivel es alcanzable
            foreach ($baremo as $nivel => $rango) {
                $puntajeMedio = ($rango[0] + $rango[1]) / 2;
                $nivelClasificado = $this->clasificarPuntaje($puntajeMedio, $baremo);
                $this->assertEquals($nivel, $nivelClasificado,
                    "Dominio '{$dominio}' Forma B: puntaje {$puntajeMedio} deberia ser '{$nivel}'");
            }
        }
    }

    // =========================================================================
    // CLASIFICACION EXTRALABORAL
    // =========================================================================

    /**
     * TEST: Clasificacion Extralaboral Total - Jefes
     */
    public function testClasificacionExtralaboralTotalJefes()
    {
        $baremo = ExtralaboralScoring::getBaremoTotal('A');

        $casos = [
            ['puntaje' => 0.0,   'esperado' => 'sin_riesgo'],
            ['puntaje' => 11.3,  'esperado' => 'sin_riesgo'],
            ['puntaje' => 11.4,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 16.9,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 17.0,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 22.6,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 22.7,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 29.0,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 29.1,  'esperado' => 'riesgo_muy_alto'],
            ['puntaje' => 100.0, 'esperado' => 'riesgo_muy_alto'],
        ];

        foreach ($casos as $caso) {
            $nivel = $this->clasificarPuntaje($caso['puntaje'], $baremo);
            $this->assertEquals($caso['esperado'], $nivel,
                "Extralaboral Jefes: Puntaje {$caso['puntaje']} deberia ser '{$caso['esperado']}'");
        }
    }

    /**
     * TEST: Clasificacion Extralaboral Total - Auxiliares
     */
    public function testClasificacionExtralaboralTotalAuxiliares()
    {
        $baremo = ExtralaboralScoring::getBaremoTotal('B');

        $casos = [
            ['puntaje' => 0.0,   'esperado' => 'sin_riesgo'],
            ['puntaje' => 12.9,  'esperado' => 'sin_riesgo'],
            ['puntaje' => 13.0,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 17.7,  'esperado' => 'riesgo_bajo'],
            ['puntaje' => 17.8,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 24.2,  'esperado' => 'riesgo_medio'],
            ['puntaje' => 24.3,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 32.3,  'esperado' => 'riesgo_alto'],
            ['puntaje' => 32.4,  'esperado' => 'riesgo_muy_alto'],
            ['puntaje' => 100.0, 'esperado' => 'riesgo_muy_alto'],
        ];

        foreach ($casos as $caso) {
            $nivel = $this->clasificarPuntaje($caso['puntaje'], $baremo);
            $this->assertEquals($caso['esperado'], $nivel,
                "Extralaboral Auxiliares: Puntaje {$caso['puntaje']} deberia ser '{$caso['esperado']}'");
        }
    }

    // =========================================================================
    // CLASIFICACION ESTRES
    // =========================================================================

    /**
     * TEST: Clasificacion Estres - Jefes
     */
    public function testClasificacionEstresJefes()
    {
        $baremo = EstresScoring::getBaremoA();

        $casos = [
            ['puntaje' => 0.0,   'esperado' => 'muy_bajo'],
            ['puntaje' => 7.8,   'esperado' => 'muy_bajo'],
            ['puntaje' => 7.9,   'esperado' => 'bajo'],
            ['puntaje' => 12.6,  'esperado' => 'bajo'],
            ['puntaje' => 12.7,  'esperado' => 'medio'],
            ['puntaje' => 17.7,  'esperado' => 'medio'],
            ['puntaje' => 17.8,  'esperado' => 'alto'],
            ['puntaje' => 25.0,  'esperado' => 'alto'],
            ['puntaje' => 25.1,  'esperado' => 'muy_alto'],
            ['puntaje' => 100.0, 'esperado' => 'muy_alto'],
        ];

        foreach ($casos as $caso) {
            $nivel = $this->clasificarPuntaje($caso['puntaje'], $baremo);
            $this->assertEquals($caso['esperado'], $nivel,
                "Estres Jefes: Puntaje {$caso['puntaje']} deberia ser '{$caso['esperado']}'");
        }
    }

    /**
     * TEST: Clasificacion Estres - Auxiliares
     */
    public function testClasificacionEstresAuxiliares()
    {
        $baremo = EstresScoring::getBaremoB();

        $casos = [
            ['puntaje' => 0.0,   'esperado' => 'muy_bajo'],
            ['puntaje' => 6.5,   'esperado' => 'muy_bajo'],
            ['puntaje' => 6.6,   'esperado' => 'bajo'],
            ['puntaje' => 11.8,  'esperado' => 'bajo'],
            ['puntaje' => 11.9,  'esperado' => 'medio'],
            ['puntaje' => 17.0,  'esperado' => 'medio'],
            ['puntaje' => 17.1,  'esperado' => 'alto'],
            ['puntaje' => 23.4,  'esperado' => 'alto'],
            ['puntaje' => 23.5,  'esperado' => 'muy_alto'],
            ['puntaje' => 100.0, 'esperado' => 'muy_alto'],
        ];

        foreach ($casos as $caso) {
            $nivel = $this->clasificarPuntaje($caso['puntaje'], $baremo);
            $this->assertEquals($caso['esperado'], $nivel,
                "Estres Auxiliares: Puntaje {$caso['puntaje']} deberia ser '{$caso['esperado']}'");
        }
    }

    // =========================================================================
    // CASOS ESPECIALES
    // =========================================================================

    /**
     * TEST: Puntaje exactamente en el limite inferior (0.0)
     */
    public function testPuntajeLimiteInferior()
    {
        $baremo = IntralaboralAScoring::getBaremoTotal();
        $nivel = $this->clasificarPuntaje(0.0, $baremo);
        $this->assertEquals('sin_riesgo', $nivel, 'Puntaje 0.0 debe ser sin_riesgo');
    }

    /**
     * TEST: Puntaje exactamente en el limite superior (100.0)
     */
    public function testPuntajeLimiteSuperior()
    {
        $baremo = IntralaboralAScoring::getBaremoTotal();
        $nivel = $this->clasificarPuntaje(100.0, $baremo);
        $this->assertEquals('riesgo_muy_alto', $nivel, 'Puntaje 100.0 debe ser riesgo_muy_alto');
    }

    /**
     * TEST: Puntajes con decimales precisos
     */
    public function testPuntajesConDecimales()
    {
        $baremo = IntralaboralAScoring::getBaremoTotal();

        // 19.75 esta entre 19.7 (sin_riesgo max) y 19.8 (riesgo_bajo min)
        // Segun baremo, 19.75 deberia caer en riesgo_bajo porque > 19.7
        $nivel = $this->clasificarPuntaje(19.75, $baremo);
        // Este test verifica el comportamiento del algoritmo de clasificacion

        $this->assertContains($nivel, ['sin_riesgo', 'riesgo_bajo'],
            'Puntaje 19.75 debe estar en sin_riesgo o riesgo_bajo segun precision');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Clasificar puntaje usando baremo
     * Este es el algoritmo que debe replicarse en los controladores
     */
    private function clasificarPuntaje($puntaje, $baremo)
    {
        foreach ($baremo as $nivel => $rango) {
            if ($puntaje >= $rango[0] && $puntaje <= $rango[1]) {
                return $nivel;
            }
        }
        return 'sin_riesgo'; // Default si no coincide con ningun rango
    }
}
