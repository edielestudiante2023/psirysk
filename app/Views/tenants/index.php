<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Tenants</h3>
    <a href="<?= base_url('tenants/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo tenant</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th><th>Slug</th><th>Razón social</th><th>NIT</th>
                    <th>Plan</th><th>Status</th><th>Créditos</th><th>Email</th><th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tenants as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><code><?= esc($t['slug']) ?></code></td>
                    <td><?= esc($t['legal_name']) ?></td>
                    <td><?= esc($t['nit']) ?></td>
                    <td><span class="badge bg-info"><?= esc($t['plan']) ?></span></td>
                    <td>
                        <?php $colors = ['trial'=>'warning','active'=>'success','suspended'=>'danger','cancelled'=>'secondary']; ?>
                        <span class="badge bg-<?= $colors[$t['status']] ?? 'secondary' ?>"><?= esc($t['status']) ?></span>
                    </td>
                    <td><?= $t['credits_balance'] ?> / <?= $t['credits_included_monthly'] ?></td>
                    <td><small><?= esc($t['contact_email']) ?></small></td>
                    <td>
                        <a href="<?= base_url('tenants/edit/'.$t['id']) ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <?php if ($t['status'] === 'active' || $t['status'] === 'trial'): ?>
                            <form method="post" action="<?= base_url('tenants/suspend/'.$t['id']) ?>" class="d-inline" onsubmit="return confirm('¿Suspender tenant?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger">Suspender</button>
                            </form>
                        <?php elseif ($t['status'] === 'suspended'): ?>
                            <form method="post" action="<?= base_url('tenants/activate/'.$t['id']) ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-success">Reactivar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
