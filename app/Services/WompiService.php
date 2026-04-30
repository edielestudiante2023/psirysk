<?php

namespace App\Services;

/**
 * WompiService
 * Cliente HTTP para integración con la pasarela Wompi (Bancolombia).
 *
 * Modos:
 *   - sandbox: https://sandbox.wompi.co/v1
 *   - production: https://production.wompi.co/v1
 *
 * Configurar las llaves en .env:
 *   wompi.publicKey   = pub_test_xxxx
 *   wompi.privateKey  = prv_test_xxxx
 *   wompi.eventsKey   = test_events_xxxx (para validar webhook signatures)
 *   wompi.environment = sandbox | production
 *
 * Documentación oficial: https://docs.wompi.co
 */
class WompiService
{
    protected string $baseUrl;
    protected string $publicKey;
    protected string $privateKey;
    protected string $eventsKey;

    public function __construct()
    {
        $env = env('wompi.environment', 'sandbox');
        $this->baseUrl = $env === 'production'
            ? 'https://production.wompi.co/v1'
            : 'https://sandbox.wompi.co/v1';
        $this->publicKey  = (string) env('wompi.publicKey', '');
        $this->privateKey = (string) env('wompi.privateKey', '');
        $this->eventsKey  = (string) env('wompi.eventsKey', '');
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Genera la firma de integridad que Wompi exige en el Web Checkout.
     *   integrity = SHA256(reference + amountInCents + currency + integritySecret)
     */
    public function generateIntegritySignature(string $reference, int $amountInCents, string $currency = 'COP'): string
    {
        $integritySecret = (string) env('wompi.integritySecret', '');
        return hash('sha256', $reference . $amountInCents . $currency . $integritySecret);
    }

    /**
     * Consulta el estado de una transacción.
     * Devuelve null si la transacción no existe.
     */
    public function getTransaction(string $transactionId): ?array
    {
        return $this->httpGet("/transactions/{$transactionId}");
    }

    /**
     * Consulta una transacción por referencia única (la que enviamos al checkout).
     */
    public function getTransactionByReference(string $reference): ?array
    {
        $resp = $this->httpGet('/transactions?reference=' . urlencode($reference));
        if (!$resp || empty($resp['data'])) {
            return null;
        }
        return $resp['data'][0] ?? null;
    }

    /**
     * Valida la firma HMAC del webhook de Wompi para garantizar autenticidad.
     */
    public function validateWebhookSignature(array $payload, string $checksum): bool
    {
        $properties = $payload['signature']['properties'] ?? [];
        $timestamp = $payload['timestamp'] ?? '';
        $concat = '';
        foreach ($properties as $prop) {
            $concat .= $this->getNestedValue($payload['data'] ?? [], $prop);
        }
        $concat .= $timestamp . $this->eventsKey;
        $expected = hash('sha256', $concat);
        return hash_equals($expected, $checksum);
    }

    private function getNestedValue(array $arr, string $path)
    {
        $keys = explode('.', $path);
        $value = $arr;
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return '';
            }
            $value = $value[$k];
        }
        return is_scalar($value) ? (string) $value : '';
    }

    private function httpGet(string $path): ?array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->privateKey,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            log_message('error', "Wompi GET {$path} HTTP {$code}: " . substr((string) $body, 0, 500));
            return null;
        }
        $decoded = json_decode((string) $body, true);
        return is_array($decoded) ? $decoded : null;
    }
}
