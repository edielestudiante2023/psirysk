<?php

namespace App\Services;

use App\Models\CreditMovementModel;
use App\Models\TenantModel;

/**
 * CreditConsumptionService
 * Encapsula el descuento de créditos del tenant cuando se consume una evaluación.
 *
 * Política actual:
 *   - Se consume 1 crédito por trabajador con status='completado' al cerrar el servicio.
 *   - "No participó" no consume crédito (el tenant no es responsable).
 *
 * Llamado desde: WorkerController::closeService() o manualmente.
 */
class CreditConsumptionService
{
    /**
     * Consume créditos y registra el movimiento.
     * Devuelve true si se aplicó el descuento, false si no había saldo (o si el tenant es global).
     *
     * @param int|null $tenantId  Tenant que paga la evaluación (null = platform / no descuento)
     * @param int      $amount    Cantidad de créditos a consumir
     * @param string   $source    Etiqueta de origen
     * @param array    $reference [type, id] para trazabilidad
     */
    public function consume(?int $tenantId, int $amount, string $source, array $reference = []): bool
    {
        if ($tenantId === null || $amount <= 0) {
            return true;
        }

        $tenantModel = new TenantModel();
        $tenant = $tenantModel->withoutTenantScope()->find($tenantId);
        if (!$tenant) {
            return false;
        }

        if ((int) $tenant['credits_balance'] < $amount) {
            log_message('warning', "Tenant {$tenantId} sin saldo para consumir {$amount} créditos.");
            return false;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $newBalance = (int) $tenant['credits_balance'] - $amount;
        $tenantModel->withoutTenantScope()->update($tenantId, [
            'credits_balance'       => $newBalance,
            'credits_used_lifetime' => (int) $tenant['credits_used_lifetime'] + $amount,
        ]);

        (new CreditMovementModel())->insert([
            'tenant_id'      => $tenantId,
            'movement_type'  => 'consume',
            'amount'         => -$amount,
            'balance_after'  => $newBalance,
            'source'         => $source,
            'reference_type' => $reference['type'] ?? null,
            'reference_id'   => $reference['id'] ?? null,
            'created_by'     => session()->get('id') ?: null,
        ]);

        $db->transComplete();
        return $db->transStatus();
    }
}
