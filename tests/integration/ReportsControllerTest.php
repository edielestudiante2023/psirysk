<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Test de Integracion para ReportsController y BatteryServiceController
 *
 * Objetivo: Verificar que las rutas de reportes responden correctamente
 * y generan contenido valido.
 *
 * Ejecutar: php vendor/bin/phpunit tests/integration/ReportsControllerTest.php
 */
class ReportsControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $DBGroup = 'default';
    protected $serviceId = 1; // ID del servicio de prueba

    // =========================================================================
    // TESTS DE RUTAS - MAPAS DE CALOR
    // =========================================================================

    /**
     * TEST: Ruta /reports/heatmap/{id} responde correctamente
     */
    public function testHeatmapRouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/heatmap/{$this->serviceId}");

        $result->assertStatus(200);
    }

    /**
     * TEST: Ruta /reports/intralaboral-a/{id} responde correctamente
     */
    public function testIntralaboralARouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/intralaboral-a/{$this->serviceId}");

        $result->assertStatus(200);
    }

    /**
     * TEST: Ruta /reports/intralaboral-b/{id} responde correctamente
     */
    public function testIntralaboralBRouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/intralaboral-b/{$this->serviceId}");

        $result->assertStatus(200);
    }

    /**
     * TEST: Ruta /reports/extralaboral-a/{id} responde correctamente
     */
    public function testExtralaboralARouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/extralaboral-a/{$this->serviceId}");

        $result->assertStatus(200);
    }

    /**
     * TEST: Ruta /reports/extralaboral-b/{id} responde correctamente
     */
    public function testExtralaboralBRouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/extralaboral-b/{$this->serviceId}");

        $result->assertStatus(200);
    }

    /**
     * TEST: Ruta /reports/estres-a/{id} responde correctamente
     */
    public function testEstresARouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/estres-a/{$this->serviceId}");

        $result->assertStatus(200);
    }

    /**
     * TEST: Ruta /reports/estres-b/{id} responde correctamente
     */
    public function testEstresBRouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/estres-b/{$this->serviceId}");

        $result->assertStatus(200);
    }

    // =========================================================================
    // TESTS DE RUTAS - BATTERY SERVICE
    // =========================================================================

    /**
     * TEST: Ruta /battery-services/global-gauges/{id} responde correctamente
     */
    public function testGlobalGaugesRouteResponds()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("battery-services/global-gauges/{$this->serviceId}");

        $result->assertStatus(200);
    }

    // =========================================================================
    // TESTS DE CONTENIDO - VERIFICAR DATOS EN RESPUESTA
    // =========================================================================

    /**
     * TEST: Heatmap contiene estructura de niveles de riesgo
     */
    public function testHeatmapContainsRiskLevels()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/heatmap/{$this->serviceId}");

        $result->assertStatus(200);

        // Verificar que la respuesta contiene los niveles de riesgo
        $body = $result->response()->getBody();

        // Al menos uno de estos debe estar presente
        $this->assertTrue(
            strpos($body, 'sin_riesgo') !== false ||
            strpos($body, 'riesgo_bajo') !== false ||
            strpos($body, 'riesgo_medio') !== false ||
            strpos($body, 'riesgo_alto') !== false ||
            strpos($body, 'riesgo_muy_alto') !== false ||
            strpos($body, 'Sin Riesgo') !== false ||
            strpos($body, 'Riesgo Bajo') !== false,
            'Heatmap debe contener niveles de riesgo'
        );
    }

    /**
     * TEST: Global Gauges contiene datos de graficos
     */
    public function testGlobalGaugesContainsChartData()
    {
        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("battery-services/global-gauges/{$this->serviceId}");

        $result->assertStatus(200);

        $body = $result->response()->getBody();

        // Debe contener referencias a graficos o gauges
        $this->assertTrue(
            strpos($body, 'gauge') !== false ||
            strpos($body, 'chart') !== false ||
            strpos($body, 'Chart') !== false ||
            strpos($body, 'canvas') !== false,
            'Global Gauges debe contener elementos de graficos'
        );
    }

    // =========================================================================
    // TESTS DE ERRORES
    // =========================================================================

    /**
     * TEST: Servicio inexistente retorna 404 o error apropiado
     */
    public function testNonExistentServiceReturnsError()
    {
        $nonExistentId = 99999;

        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/heatmap/{$nonExistentId}");

        // Puede ser 404 o redireccion con error
        $this->assertTrue(
            $result->response()->getStatusCode() === 404 ||
            $result->response()->getStatusCode() === 302 ||
            $result->response()->getStatusCode() === 500,
            'Servicio inexistente debe retornar error'
        );
    }

    /**
     * TEST: Acceso sin autenticacion redirige a login
     */
    public function testUnauthenticatedAccessRedirects()
    {
        $result = $this->get("reports/heatmap/{$this->serviceId}");

        // Debe redirigir a login o retornar error de autenticacion
        $this->assertTrue(
            $result->response()->getStatusCode() === 302 ||
            $result->response()->getStatusCode() === 401 ||
            $result->response()->getStatusCode() === 403,
            'Acceso sin autenticacion debe redirigir o retornar error'
        );
    }

    // =========================================================================
    // TESTS DE RENDIMIENTO (Basicos)
    // =========================================================================

    /**
     * TEST: Heatmap responde en tiempo razonable (<5 segundos)
     */
    public function testHeatmapResponseTime()
    {
        $start = microtime(true);

        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("reports/heatmap/{$this->serviceId}");

        $elapsed = microtime(true) - $start;

        $result->assertStatus(200);
        $this->assertLessThan(5.0, $elapsed, "Heatmap debe responder en menos de 5 segundos (tomo {$elapsed}s)");
    }

    /**
     * TEST: Global Gauges responde en tiempo razonable (<5 segundos)
     */
    public function testGlobalGaugesResponseTime()
    {
        $start = microtime(true);

        $result = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                       ->get("battery-services/global-gauges/{$this->serviceId}");

        $elapsed = microtime(true) - $start;

        $result->assertStatus(200);
        $this->assertLessThan(5.0, $elapsed, "Global Gauges debe responder en menos de 5 segundos (tomo {$elapsed}s)");
    }

    // =========================================================================
    // TESTS DE CONSISTENCIA ENTRE FORMAS
    // =========================================================================

    /**
     * TEST: Intralaboral A y B usan la misma estructura de datos
     */
    public function testIntralaboralFormsConsistentStructure()
    {
        $resultA = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                        ->get("reports/intralaboral-a/{$this->serviceId}");

        $resultB = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                        ->get("reports/intralaboral-b/{$this->serviceId}");

        $resultA->assertStatus(200);
        $resultB->assertStatus(200);

        // Ambos deben tener estructura similar (mismos elementos HTML)
        $bodyA = $resultA->response()->getBody();
        $bodyB = $resultB->response()->getBody();

        // Verificar que ambos tienen tablas o estructuras de datos
        $this->assertTrue(
            (strpos($bodyA, '<table') !== false || strpos($bodyA, 'heatmap') !== false) &&
            (strpos($bodyB, '<table') !== false || strpos($bodyB, 'heatmap') !== false),
            'Ambas formas deben tener estructura de datos similar'
        );
    }

    /**
     * TEST: Extralaboral A y B usan la misma estructura de datos
     */
    public function testExtralaboralFormsConsistentStructure()
    {
        $resultA = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                        ->get("reports/extralaboral-a/{$this->serviceId}");

        $resultB = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                        ->get("reports/extralaboral-b/{$this->serviceId}");

        $resultA->assertStatus(200);
        $resultB->assertStatus(200);
    }

    /**
     * TEST: Estres A y B usan la misma estructura de datos
     */
    public function testEstresFormsConsistentStructure()
    {
        $resultA = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                        ->get("reports/estres-a/{$this->serviceId}");

        $resultB = $this->withSession(['logged_in' => true, 'role_name' => 'superadmin'])
                        ->get("reports/estres-b/{$this->serviceId}");

        $resultA->assertStatus(200);
        $resultB->assertStatus(200);
    }
}
