<?php

namespace App\Controllers;

use App\Models\BillingTransactionModel;
use App\Models\CreditMovementModel;
use App\Models\TenantModel;
use App\Services\WompiService;

/**
 * SubscriptionController
 * Página de suscripción y compra de créditos extra del tenant.
 *
 *   /subscription           → estado actual + acciones
 *   /subscription/checkout/{type}  → genera referencia y formulario hacia Wompi
 *   /subscription/return    → vuelta del Wompi Web Checkout
 *   /subscription/webhook   → endpoint público para Wompi
 *   /subscription/buy-credits/{qty} → compra puntual de créditos
 */
class SubscriptionController extends BaseController
{
    protected TenantModel $tenantModel;
    protected BillingTransactionModel $billingModel;
    protected CreditMovementModel $creditModel;
    protected WompiService $wompi;

    public function __construct()
    {
        $this->tenantModel = new TenantModel();
        $this->billingModel = new BillingTransactionModel();
        $this->creditModel = new CreditMovementModel();
        $this->wompi = new WompiService();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        $tenant = tenant_context();
        if (!$tenant) {
            return redirect()->to('/dashboard');
        }
        $recent = $this->billingModel
            ->where('tenant_id', $tenant['id'])
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();
        return view('subscription/index', [
            'title'  => 'Mi Suscripcion',
            'tenant' => $tenant,
            'transactions' => $recent,
        ]);
    }

    public function checkoutSubscription()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        $tenant = tenant_context();
        if (!$tenant) {
            return redirect()->to('/dashboard');
        }

        $reference = 'sub_' . $tenant['id'] . '_' . time() . '_' . bin2hex(random_bytes(4));
        $amountCop = (int) $tenant['monthly_fee_cop'];
        if ($amountCop <= 0) {
            return redirect()->to('/subscription')->with('error', 'Tarifa no configurada.');
        }
        $amountCents = $amountCop * 100;

        $this->billingModel->insert([
            'tenant_id'        => $tenant['id'],
            'transaction_type' => 'subscription',
            'amount_cop'       => $amountCop,
            'currency'         => 'COP',
            'wompi_reference'  => $reference,
            'wompi_status'     => 'PENDING',
            'description'      => 'Suscripción mensual plan ' . $tenant['plan'],
        ]);

        return view('subscription/checkout', [
            'title'           => 'Pagar suscripción',
            'amountCents'     => $amountCents,
            'amountCop'       => $amountCop,
            'reference'       => $reference,
            'integrity'       => $this->wompi->generateIntegritySignature($reference, $amountCents),
            'publicKey'       => $this->wompi->getPublicKey(),
            'redirectUrl'     => base_url('subscription/return'),
            'description'     => 'Suscripción mensual psyrisk - plan ' . $tenant['plan'],
            'customerEmail'   => $tenant['contact_email'],
        ]);
    }

    public function buyCredits($qty)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        $qty = max(1, (int) $qty);
        $tenant = tenant_context();
        if (!$tenant) return redirect()->to('/dashboard');

        $unitPrice = (int) $tenant['extra_credit_price_cop'];
        $amountCop = $qty * $unitPrice;
        $amountCents = $amountCop * 100;
        $reference = 'cred_' . $tenant['id'] . '_' . $qty . '_' . time() . '_' . bin2hex(random_bytes(4));

        $this->billingModel->insert([
            'tenant_id'        => $tenant['id'],
            'transaction_type' => 'credit_pack',
            'amount_cop'       => $amountCop,
            'currency'         => 'COP',
            'wompi_reference'  => $reference,
            'wompi_status'     => 'PENDING',
            'description'      => "Compra {$qty} créditos extra",
            'metadata'         => json_encode(['credit_qty' => $qty]),
        ]);

        return view('subscription/checkout', [
            'title'           => "Comprar {$qty} créditos",
            'amountCents'     => $amountCents,
            'amountCop'       => $amountCop,
            'reference'       => $reference,
            'integrity'       => $this->wompi->generateIntegritySignature($reference, $amountCents),
            'publicKey'       => $this->wompi->getPublicKey(),
            'redirectUrl'     => base_url('subscription/return'),
            'description'     => "{$qty} créditos extra de psyrisk",
            'customerEmail'   => $tenant['contact_email'],
        ]);
    }

    /**
     * Vuelta del usuario desde Wompi. Wompi añade ?id=xxx
     * Verificamos la transacción contra la API de Wompi.
     */
    public function returnFromWompi()
    {
        $transactionId = $this->request->getGet('id');
        if (!$transactionId) {
            return redirect()->to('/subscription')->with('error', 'Sin transacción.');
        }
        $tx = $this->wompi->getTransaction($transactionId);
        if (!$tx || empty($tx['data'])) {
            return redirect()->to('/subscription')->with('error', 'Transacción no encontrada.');
        }
        $data = $tx['data'];
        $reference = $data['reference'] ?? null;
        if (!$reference) {
            return redirect()->to('/subscription')->with('error', 'Referencia inválida.');
        }
        $this->processTransactionUpdate($reference, $data);

        if (($data['status'] ?? '') === 'APPROVED') {
            return redirect()->to('/subscription')->with('success', 'Pago aprobado y créditos acreditados.');
        }
        return redirect()->to('/subscription')->with('error', 'Pago en estado: ' . ($data['status'] ?? 'desconocido'));
    }

    /**
     * Webhook de Wompi: notificación asíncrona del cambio de estado.
     * Wompi envía el evento con la firma SHA-256 en el body.
     */
    public function webhook()
    {
        $body = $this->request->getBody();
        $payload = json_decode($body ?: '', true);
        if (!is_array($payload)) {
            return $this->response->setStatusCode(400)->setBody('invalid payload');
        }
        $checksum = $payload['signature']['checksum'] ?? '';
        if (!$this->wompi->validateWebhookSignature($payload, $checksum)) {
            log_message('warning', 'Wompi webhook con firma inválida.');
            return $this->response->setStatusCode(401)->setBody('invalid signature');
        }
        $tx = $payload['data']['transaction'] ?? null;
        if (!$tx) {
            return $this->response->setStatusCode(400)->setBody('no transaction');
        }
        $reference = $tx['reference'] ?? '';
        $this->processTransactionUpdate($reference, $tx);
        return $this->response->setStatusCode(200)->setBody('ok');
    }

    private function processTransactionUpdate(string $reference, array $tx): void
    {
        $billing = $this->billingModel->findByReference($reference);
        if (!$billing) {
            log_message('warning', "Wompi: referencia {$reference} desconocida.");
            return;
        }

        // Idempotencia: si ya está APPROVED y procesada, no doble-acreditar.
        if ($billing['wompi_status'] === 'APPROVED' && !empty($billing['paid_at'])) {
            return;
        }

        $newStatus = $tx['status'] ?? 'PENDING';
        $update = [
            'wompi_transaction_id' => $tx['id'] ?? null,
            'wompi_status'         => $newStatus,
            'payment_method'       => $tx['payment_method_type'] ?? null,
        ];
        if ($newStatus === 'APPROVED') {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }
        $this->billingModel->withoutTenantScope()->update($billing['id'], $update);

        if ($newStatus === 'APPROVED') {
            $this->grantCreditsForTransaction($billing);
            $this->notifyAccountingForInvoice($billing, $tx);
        }
    }

    /**
     * Notifica al equipo contable de Cycloid para emitir factura electrónica
     * en Siigo de manera manual. Operación manual mientras volumen sea bajo.
     * Cuando crezca: integración Siigo API (Fase 1.5).
     */
    private function notifyAccountingForInvoice(array $billing, array $tx): void
    {
        $tenantId = (int) $billing['tenant_id'];
        $tenant = $this->tenantModel->withoutTenantScope()->find($tenantId);
        if (!$tenant) return;

        $to = 'diana.cuestas@cycloidtalent.com';
        $subject = "[psyrisk] Pago aprobado — emitir factura: {$tenant['legal_name']} — \$"
            . number_format((int) $billing['amount_cop'], 0, ',', '.');

        $rows = [
            ['Cliente (razón social)', $tenant['legal_name']],
            ['NIT', $tenant['nit']],
            ['Email cliente', $tenant['contact_email']],
            ['Concepto', $billing['description']],
            ['Tipo', $billing['transaction_type']],
            ['Monto cobrado', '$' . number_format((int) $billing['amount_cop'], 0, ',', '.') . ' COP'],
            ['Método de pago', $billing['payment_method'] ?? '-'],
            ['Referencia Wompi', $billing['wompi_reference']],
            ['ID transacción Wompi', $billing['wompi_transaction_id'] ?? '-'],
            ['Fecha de pago', $billing['paid_at'] ?? date('Y-m-d H:i:s')],
        ];

        $rowsHtml = '';
        foreach ($rows as $r) {
            $rowsHtml .= '<tr><td style="padding:6px 12px;border-bottom:1px solid #eee;color:#666;">'
                . esc($r[0]) . '</td><td style="padding:6px 12px;border-bottom:1px solid #eee;font-weight:600;">'
                . esc($r[1]) . '</td></tr>';
        }

        $html = '<!DOCTYPE html><html><body style="font-family:Segoe UI,sans-serif;background:#f4f4f7;padding:20px;">'
            . '<div style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">'
            . '<div style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:20px;">'
            . '<h2 style="margin:0;">Pago aprobado — emitir factura electrónica</h2>'
            . '</div><div style="padding:20px;">'
            . '<p>Diana, se acaba de aprobar un pago en psyrisk. Por favor emitir factura electrónica en Siigo con estos datos:</p>'
            . '<table style="width:100%;border-collapse:collapse;font-size:14px;">' . $rowsHtml . '</table>'
            . '<p style="margin-top:20px;padding:12px;background:#fff7e6;border-left:4px solid #ffa500;font-size:13px;">'
            . '<strong>Plazo legal:</strong> 5 días hábiles desde la fecha de pago para emitir la factura electrónica (DIAN).'
            . '</p><p style="font-size:12px;color:#999;">Operación manual durante Fase 1. Integración Siigo automática planeada para Fase 1.5.</p>'
            . '</div></div></body></html>';

        try {
            $emailService = new \App\Libraries\EmailService();
            $reflection = new \ReflectionClass($emailService);
            $sendMethod = $reflection->getMethod('sendViaSendGrid');
            $sendMethod->setAccessible(true);
            $sendMethod->invoke($emailService, $to, $subject, $html);
        } catch (\Throwable $e) {
            log_message('error', 'Falla notificando facturación a contabilidad: ' . $e->getMessage());
        }
    }

    private function grantCreditsForTransaction(array $billing): void
    {
        $tenantId = (int) $billing['tenant_id'];
        $tenant = $this->tenantModel->withoutTenantScope()->find($tenantId);
        if (!$tenant) return;

        $type = $billing['transaction_type'];
        $creditsToGrant = 0;
        $source = '';

        if ($type === 'subscription') {
            $creditsToGrant = (int) $tenant['credits_included_monthly'];
            $source = 'monthly_refill';
            $this->tenantModel->withoutTenantScope()->update($tenantId, [
                'status'                 => 'active',
                'current_period_start'   => date('Y-m-d'),
                'current_period_end'     => date('Y-m-d', strtotime('+1 month')),
            ]);
        } elseif ($type === 'credit_pack') {
            $meta = json_decode($billing['metadata'] ?? '{}', true);
            $creditsToGrant = (int) ($meta['credit_qty'] ?? 0);
            $source = 'credit_pack';
        }

        if ($creditsToGrant <= 0) return;

        $newBalance = ((int) $tenant['credits_balance']) + $creditsToGrant;
        $this->tenantModel->withoutTenantScope()->update($tenantId, [
            'credits_balance' => $newBalance,
        ]);
        $this->creditModel->insert([
            'tenant_id'      => $tenantId,
            'movement_type'  => 'grant',
            'amount'         => $creditsToGrant,
            'balance_after'  => $newBalance,
            'source'         => $source,
            'reference_type' => 'billing_transaction',
            'reference_id'   => $billing['id'],
        ]);
    }
}
