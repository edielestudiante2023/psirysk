<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * TenantCreditFilter
 * Bloquea acciones que consumen créditos cuando el tenant no tiene saldo
 * o está suspendido/cancelado.
 *
 * Aplicar a rutas de creación/finalización de servicios de batería:
 *   - POST battery-services/store
 *   - POST workers/close-service/*
 *
 * El platform admin (sin tenant_id) no se ve afectado.
 */
class TenantCreditFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return;
        }
        $tenantId = $session->get('tenant_id');
        if (!$tenantId) {
            return;
        }

        $tenantModel = new \App\Models\TenantModel();
        $tenant = $tenantModel->withoutTenantScope()->find($tenantId);
        if (!$tenant) {
            return redirect()->to('/login');
        }

        if (in_array($tenant['status'], ['suspended', 'cancelled'], true)) {
            return redirect()->to('/subscription')
                ->with('error', 'Tu cuenta está ' . $tenant['status'] . '. Renueva para continuar.');
        }

        $minRequired = (int) ($arguments[0] ?? 1);
        if ((int) $tenant['credits_balance'] < $minRequired) {
            return redirect()->to('/subscription')
                ->with('error', 'No tienes créditos suficientes. Compra créditos extra o espera a la renovación.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
