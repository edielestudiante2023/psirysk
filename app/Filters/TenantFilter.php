<?php

namespace App\Filters;

use App\Models\TenantModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * TenantFilter
 * Verifica que el tenant del usuario logueado esté activo y dentro de período.
 *
 * Aplicar a TODAS las rutas que operan sobre datos de tenant (post-login).
 * NO aplicar a:
 *   - rutas públicas (/login, /forgot-password, /bateria/*)
 *   - rutas del panel platform admin (cuando se cree)
 *
 * Comportamiento:
 *   - Si el usuario es platform admin (tenant_id null en sesión) → permite paso.
 *   - Si el usuario tiene tenant_id pero el tenant está suspendido/cancelado:
 *       → cierra sesión y redirige a /login con mensaje.
 *   - Si el usuario tiene tenant_id pero NO existe el tenant (anomalía):
 *       → cierra sesión.
 */
class TenantFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            // Si no hay login, AuthFilter lo manejó. Acá no hacemos nada.
            return;
        }

        $tenantId = $session->get('tenant_id');

        // Platform admin: tenant_id NULL es válido.
        if ($tenantId === null) {
            return;
        }

        $tenantModel = new TenantModel();
        $tenant = $tenantModel->withoutTenantScope()->find($tenantId);

        if (!$tenant) {
            $session->destroy();
            return redirect()->to('/login')
                ->with('error', 'Tu cuenta de tenant no existe. Contacta al administrador.');
        }

        if (in_array($tenant['status'], ['suspended', 'cancelled'], true)) {
            $session->destroy();
            return redirect()->to('/login')
                ->with('error', 'Tu cuenta está ' .
                    ($tenant['status'] === 'suspended' ? 'suspendida' : 'cancelada') .
                    '. Contacta al administrador.');
        }

        // Hace disponible el tenant en el request para uso en controladores/views.
        $request->tenant = $tenant;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hace nada en el after.
    }
}
