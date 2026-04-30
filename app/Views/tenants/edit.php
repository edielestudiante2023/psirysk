<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h3>Editar Tenant: <?= esc($tenant['legal_name']) ?></h3>
<form method="post" action="<?= base_url('tenants/update/'.$tenant['id']) ?>" class="card card-body shadow-sm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-8"><label>Razón social</label><input name="legal_name" class="form-control" value="<?= esc($tenant['legal_name']) ?>"></div>
        <div class="col-md-4"><label>NIT</label><input class="form-control" value="<?= esc($tenant['nit']) ?>" disabled></div>
        <div class="col-md-8"><label>Nombre comercial</label><input name="trade_name" class="form-control" value="<?= esc($tenant['trade_name']) ?>"></div>
        <div class="col-md-4"><label>Slug</label><input class="form-control" value="<?= esc($tenant['slug']) ?>" disabled></div>
        <div class="col-md-6"><label>Contacto</label><input name="contact_name" class="form-control" value="<?= esc($tenant['contact_name']) ?>"></div>
        <div class="col-md-6"><label>Email</label><input type="email" name="contact_email" class="form-control" value="<?= esc($tenant['contact_email']) ?>"></div>
        <div class="col-md-4"><label>Teléfono</label><input name="contact_phone" class="form-control" value="<?= esc($tenant['contact_phone']) ?>"></div>
        <div class="col-md-8"><label>Dirección</label><input name="address" class="form-control" value="<?= esc($tenant['address']) ?>"></div>
        <div class="col-md-3"><label>Plan</label>
            <select name="plan" class="form-select">
                <?php foreach (['inicial','profesional','empresarial','custom'] as $p): ?>
                    <option value="<?= $p ?>" <?= $tenant['plan']===$p?'selected':'' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><label>Status</label>
            <select name="status" class="form-select">
                <?php foreach (['trial','active','suspended','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $tenant['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><label>Créditos disponibles</label><input type="number" name="credits_balance" class="form-control" value="<?= $tenant['credits_balance'] ?>"></div>
        <div class="col-md-3"><label>Créditos mensuales</label><input type="number" name="credits_included_monthly" class="form-control" value="<?= $tenant['credits_included_monthly'] ?>"></div>
        <div class="col-md-4"><label>Tarifa mensual COP</label><input type="number" name="monthly_fee_cop" class="form-control" value="<?= $tenant['monthly_fee_cop'] ?>"></div>
        <div class="col-md-4"><label>Crédito extra COP</label><input type="number" name="extra_credit_price_cop" class="form-control" value="<?= $tenant['extra_credit_price_cop'] ?>"></div>
        <div class="col-md-4"><label>Trial vence</label><input type="date" name="trial_ends_at" class="form-control" value="<?= $tenant['trial_ends_at'] ?>"></div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary">Guardar</button>
        <a href="<?= base_url('tenants') ?>" class="btn btn-link">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
