<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar_menu') ?>
<a class="nav-link active" href="<?= base_url('dashboard') ?>">
    <i class="fas fa-home me-2"></i> Dashboard
</a>
<a class="nav-link" href="<?= base_url('users') ?>" target="_blank">
    <i class="fas fa-users me-2"></i> Usuarios
</a>
<a class="nav-link" href="<?= base_url('companies') ?>" target="_blank">
    <i class="fas fa-building me-2"></i> Empresas
</a>
<a class="nav-link" href="<?= base_url('battery-services') ?>" target="_blank">
    <i class="fas fa-clipboard-check me-2"></i> Servicios de Batería
</a>
<a class="nav-link" href="<?= base_url('consultants') ?>" target="_blank">
    <i class="fas fa-user-tie me-2"></i> Consultores
</a>
<a class="nav-link" href="<?= base_url('satisfaction/dashboard') ?>" target="_blank">
    <i class="fas fa-star me-2"></i> Encuestas Satisfaccion
</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-4">
        <div class="card stat-card bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Total Usuarios</h6>
                    <h2 class="fw-bold mb-0"><?= $totalUsers ?></h2>
                </div>
                <i class="fas fa-users icon"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Total Empresas</h6>
                    <h2 class="fw-bold mb-0"><?= $totalCompanies ?></h2>
                </div>
                <i class="fas fa-building icon"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card bg-info text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Total Servicios</h6>
                    <h2 class="fw-bold mb-0"><?= $totalServices ?></h2>
                </div>
                <i class="fas fa-clipboard-check icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-users text-primary me-2"></i>Usuarios Recientes</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($recentUsers as $user): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($user['name']) ?></strong><br>
                                <small class="text-muted"><?= esc($user['email']) ?></small>
                            </div>
                            <span class="badge bg-secondary"><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-building text-success me-2"></i>Empresas Recientes</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($recentCompanies as $company): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($company['name']) ?></strong><br>
                                <small class="text-muted">NIT: <?= esc($company['nit']) ?></small>
                            </div>
                            <span class="badge bg-<?= $company['type'] === 'gestor_multicompania' ? 'primary' : 'info' ?>">
                                <?= $company['type'] === 'gestor_multicompania' ? 'Gestor' : 'Individual' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
