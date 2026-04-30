<?php

namespace App\Traits;

/**
 * TenantScopedTrait
 * ------------------
 * Aísla automáticamente las queries de un modelo al tenant del usuario logueado.
 *
 * Comportamiento:
 *  - Si la sesión NO tiene `tenant_id` (caso: SUPER ADMIN platform o ruta pública):
 *      → no se aplica scope. El modelo opera en modo global.
 *  - Si la sesión tiene `tenant_id`:
 *      → todas las queries find/findAll automáticamente tienen WHERE tenant_id = X
 *      → todas las inserts automáticamente fijan tenant_id = X (si no se pasó)
 *
 * Uso:
 *   class MiModelo extends Model {
 *       use \App\Traits\TenantScopedTrait;
 *       protected $beforeFind   = ['scopeToTenant'];
 *       protected $beforeInsert = ['injectTenantId'];
 *       // ... resto del modelo
 *   }
 *
 * Si el modelo ya tiene otros callbacks beforeInsert (ej. hashPassword en UserModel)
 * añade 'injectTenantId' a la lista existente, NO la sobrescribas.
 *
 * Para queries que necesiten saltarse el aislamiento (ej. operaciones platform-wide
 * desde cron, seeds, o el panel del SUPER ADMIN), usar `withoutTenantScope()`:
 *   $allCompanies = $companyModel->withoutTenantScope()->findAll();
 */
trait TenantScopedTrait
{
    protected bool $skipTenantScope = false;

    /**
     * beforeFind callback: agrega WHERE tenant_id al builder.
     */
    protected function scopeToTenant(array $eventData): array
    {
        if ($this->skipTenantScope) {
            $this->skipTenantScope = false;
            return $eventData;
        }

        $tenantId = $this->currentTenantId();
        if ($tenantId === null) {
            return $eventData;
        }

        $this->builder()->where($this->table . '.tenant_id', $tenantId);

        return $eventData;
    }

    /**
     * beforeInsert callback: inyecta tenant_id si no fue proporcionado.
     */
    protected function injectTenantId(array $data): array
    {
        $tenantId = $this->currentTenantId();
        if ($tenantId === null) {
            return $data;
        }

        if (!isset($data['data']['tenant_id']) || $data['data']['tenant_id'] === null) {
            $data['data']['tenant_id'] = $tenantId;
        }

        return $data;
    }

    /**
     * Devuelve el tenant_id de la sesión, o null si no hay sesión o si es admin platform.
     */
    protected function currentTenantId(): ?int
    {
        if (!function_exists('session')) {
            return null;
        }

        $session = session();
        if (!$session || !$session->get('isLoggedIn')) {
            return null;
        }

        $tenantId = $session->get('tenant_id');
        return $tenantId !== null ? (int) $tenantId : null;
    }

    /**
     * Permite saltarse el aislamiento por una sola query.
     * USAR CON EXTREMO CUIDADO. Solo para operaciones administrativas globales.
     */
    public function withoutTenantScope(): self
    {
        $this->skipTenantScope = true;
        return $this;
    }
}
