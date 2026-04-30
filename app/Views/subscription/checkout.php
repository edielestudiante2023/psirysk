<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4 text-center">
                <h4>Confirmar pago</h4>
                <p class="text-muted"><?= esc($description) ?></p>
                <h2 class="my-3">$<?= number_format($amountCop, 0, ',', '.') ?> COP</h2>
                <p class="small text-muted">Pagas con tarjeta, PSE, Nequi o Bancolombia.<br>Comisión transaccional Wompi se suma al pagar.</p>
                <form action="https://checkout.wompi.co/p/" method="GET">
                    <input type="hidden" name="public-key" value="<?= esc($publicKey) ?>">
                    <input type="hidden" name="currency" value="COP">
                    <input type="hidden" name="amount-in-cents" value="<?= (int)$amountCents ?>">
                    <input type="hidden" name="reference" value="<?= esc($reference) ?>">
                    <input type="hidden" name="signature:integrity" value="<?= esc($integrity) ?>">
                    <input type="hidden" name="redirect-url" value="<?= esc($redirectUrl) ?>">
                    <input type="hidden" name="customer-data:email" value="<?= esc($customerEmail) ?>">
                    <button class="btn btn-primary btn-lg w-100">Pagar con Wompi →</button>
                </form>
                <a href="<?= base_url('subscription') ?>" class="btn btn-link mt-2">Cancelar</a>
                <hr>
                <small class="text-muted">Pagos procesados por Wompi (Bancolombia). PCI-DSS Nivel 1.</small>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
