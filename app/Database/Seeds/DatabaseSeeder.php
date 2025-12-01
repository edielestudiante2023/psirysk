<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder Maestro para Producción
 *
 * Ejecuta todos los seeders necesarios en el orden correcto.
 *
 * ORDEN DE EJECUCIÓN:
 * 1. RoleSeeder - Roles del sistema (REQUERIDO)
 * 2. UserSeeder - Usuarios iniciales incluyendo superadmin (REQUERIDO)
 * 3. ConsultantSeeder - Consultores para PDFs (REQUERIDO)
 * 4. ActionPlansMasterSeeder - Planes de acción para recomendaciones (REQUERIDO)
 *
 * NOTA: CompanySeeder contiene datos de demostración, NO ejecutar en producción limpia
 *
 * USO:
 *   php spark db:seed DatabaseSeeder
 *
 * IMPORTANTE:
 * - Ejecutar DESPUÉS de las migraciones: php spark migrate
 * - Asegurarse de que la BD esté vacía antes de ejecutar
 * - En producción, modificar UserSeeder para cambiar contraseñas
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║           PSYRISK - DATABASE SEEDER MAESTRO                  ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";

        // 1. Roles del sistema
        echo "▶ [1/4] Ejecutando RoleSeeder...\n";
        $this->call('RoleSeeder');
        echo "   ✓ Roles creados correctamente\n\n";

        // 2. Usuarios iniciales
        echo "▶ [2/4] Ejecutando UserSeeder...\n";
        $this->call('UserSeeder');
        echo "   ✓ Usuarios iniciales creados\n\n";

        // 3. Consultores
        echo "▶ [3/4] Ejecutando ConsultantSeeder...\n";
        $this->call('ConsultantSeeder');
        echo "   ✓ Consultores registrados\n\n";

        // 4. Planes de Acción
        echo "▶ [4/4] Ejecutando ActionPlansMasterSeeder...\n";
        $this->call('ActionPlansMasterSeeder');
        echo "   ✓ Planes de acción cargados\n\n";

        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║           ✓ SEEDING COMPLETADO EXITOSAMENTE                  ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";

        echo "RESUMEN DE DATOS CARGADOS:\n";
        echo "  • Roles: 5 (superadmin, consultor, cliente_gestor, cliente_empresa, director_comercial)\n";
        echo "  • Usuarios: 6 iniciales (admin, consultor, comercial, demo clientes)\n";
        echo "  • Consultores: 1 (Edison Cuervo)\n";
        echo "  • Planes de Acción: 27 (19 intralaboral + 7 extralaboral + 1 estrés)\n\n";

        echo "⚠️  IMPORTANTE PARA PRODUCCIÓN:\n";
        echo "  1. Cambiar contraseñas de usuarios en UserSeeder ANTES de ejecutar\n";
        echo "  2. Subir firma del consultor manualmente\n";
        echo "  3. Eliminar usuarios de demo si no son necesarios\n\n";
    }
}
