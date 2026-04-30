<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h3>Crear Tenant</h3>
<?php if ($errors = session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form method="post" action="<?= base_url('tenants/store') ?>" class="card card-body shadow-sm">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-md-4"><label>Slug *</label><input name="slug" class="form-control" required value="<?= old('slug') ?>"></div>
        <div class="col-md-8"><label>Razón social *</label><input name="legal_name" class="form-control" required value="<?= old('legal_name') ?>"></div>
        <div class="col-md-8"><label>Nombre comercial *</label><input name="trade_name" class="form-control" required value="<?= old('trade_name') ?>"></div>
        <div class="col-md-4"><label>NIT *</label><input name="nit" class="form-control" required value="<?= old('nit') ?>"></div>
        <div class="col-md-6"><label>Contacto *</label><input name="contact_name" class="form-control" required value="<?= old('contact_name') ?>"></div>
        <div class="col-md-6"><label>Email *</label><input type="email" name="contact_email" class="form-control" required value="<?= old('contact_email') ?>"></div>
        <div class="col-md-4"><label>Teléfono</label><input name="contact_phone" class="form-control" value="<?= old('contact_phone') ?>"></div>
        <div class="col-md-8"><label>Dirección</label><input name="address" class="form-control" value="<?= old('address') ?>"></div>
        <div class="col-md-6"><label>Plan *</label>
            <select name="plan" class="form-select">
                <option value="inicial">Inicial — $79.000/mes</option>
                <option value="profesional">Profesional — $199.000/mes</option>
                <option value="empresarial">Empresarial — $499.000/mes</option>
                <option value="custom">Custom (a medida)</option>
            </select>
        </div>
        <div class="col-md-6"><label>Status</label>
            <select name="status" class="form-select">
                <option value="trial">Trial</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary">Crear</button>
        <a href="<?= base_url('tenants') ?>" class="btn btn-link">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
