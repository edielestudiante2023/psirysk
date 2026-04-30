<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h3>Mi Suscripción</h3>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-warning"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Plan actual</h6>
                <h3><?= ucfirst(esc($tenant['plan'])) ?></h3>
                <p class="text-muted">$<?= number_format((int)$tenant['monthly_fee_cop'], 0, ',', '.') ?> COP / mes</p>
                <p>Estado: <span class="badge bg-<?= $tenant['status']==='active'?'success':($tenant['status']==='trial'?'warning':'danger') ?>"><?= esc($tenant['status']) ?></span></p>
                <?php if ($tenant['status']==='trial' && !empty($tenant['trial_ends_at'])): ?>
                    <small class="text-muted">Trial vence: <?= esc($tenant['trial_ends_at']) ?></small>
                <?php elseif (!empty($tenant['current_period_end'])): ?>
                    <small class="text-muted">Próximo cobro: <?= esc($tenant['current_period_end']) ?></small>
                <?php endif; ?>
                <hr>
                <form method="post" action="<?= base_url('subscription/checkout-subscription') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary w-100"><?= $tenant['status']==='trial' ? 'Activar suscripción' : 'Renovar ahora' ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Créditos disponibles</h6>
                <h3><?= (int)$tenant['credits_balance'] ?></h3>
                <p class="text-muted">de <?= (int)$tenant['credits_included_monthly'] ?> incluidos en el plan</p>
                <p>Crédito extra: <strong>$<?= number_format((int)$tenant['extra_credit_price_cop'], 0, ',', '.') ?></strong> c/u</p>
                <hr>
                <div class="d-flex gap-1">
                    <a href="<?= base_url('subscription/buy-credits/10') ?>" class="btn btn-outline-primary btn-sm flex-fill">+10</a>
                    <a href="<?= base_url('subscription/buy-credits/50') ?>" class="btn btn-outline-primary btn-sm flex-fill">+50</a>
                    <a href="<?= base_url('subscription/buy-credits/100') ?>" class="btn btn-outline-primary btn-sm flex-fill">+100</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Uso histórico</h6>
                <h3><?= (int)$tenant['credits_used_lifetime'] ?></h3>
                <p class="text-muted">evaluaciones consumidas</p>
            </div>
        </div>
    </div>
</div>

<h5 class="mt-4">Últimas transacciones</h5>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light">
                <tr><th>Fecha</th><th>Descripción</th><th>Monto</th><th>Estado</th><th>Método</th></tr>
            </thead>
            <tbody>
            <?php if (empty($transactions)): ?>
                <tr><td colspan="5" class="text-muted text-center">Sin transacciones todavía.</td></tr>
            <?php else: foreach ($transactions as $t): ?>
                <tr>
                    <td><?= esc($t['created_at']) ?></td>
                    <td><?= esc($t['description']) ?></td>
                    <td>$<?= number_format((int)$t['amount_cop'], 0, ',', '.') ?></td>
                    <td>
                        <?php $colors = ['APPROVED'=>'success','DECLINED'=>'danger','PENDING'=>'warning','VOIDED'=>'secondary','ERROR'=>'danger']; ?>
                        <span class="badge bg-<?= $colors[$t['wompi_status']] ?? 'secondary' ?>"><?= esc($t['wompi_status'] ?? '-') ?></span>
                    </td>
                    <td><small><?= esc($t['payment_method'] ?? '-') ?></small></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
