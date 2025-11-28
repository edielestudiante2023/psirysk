<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar_menu') ?>
<a class="nav-link active" href="<?= base_url('dashboard') ?>">
    <i class="fas fa-home me-2"></i> Dashboard
</a>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
.service-card {
    border-left: 4px solid #667eea;
    transition: all 0.3s;
}
.service-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Company Info -->
<?php if (isset($gestorCompany)): ?>
<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex align-items-center">
        <i class="fas fa-building fa-2x me-3"></i>
        <div>
            <h5 class="mb-0"><?= esc($gestorCompany['name']) ?></h5>
            <small>NIT: <?= esc($gestorCompany['nit']) ?></small>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-6">
        <div class="card stat-card bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Empresas en el Grupo</h6>
                    <h2 class="fw-bold mb-0"><?= $totalCompanies ?></h2>
                </div>
                <i class="fas fa-building icon"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card stat-card bg-success text-white">
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

<!-- Companies List -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-building text-primary me-2"></i>Empresas del Grupo</h5>
            </div>
            <div class="card-body">
                <?php if (empty($childCompanies)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-building fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No hay empresas registradas en el grupo.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($childCompanies as $company): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= esc($company['name']) ?></strong><br>
                                    <small class="text-muted">NIT: <?= esc($company['nit']) ?></small>
                                </div>
                                <span class="badge bg-<?= $company['type'] === 'gestor_multicompania' ? 'primary' : 'info' ?>">
                                    <?= $company['type'] === 'gestor_multicompania' ? 'Gestor' : 'Empresa' ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Services -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-clipboard-list text-primary me-2"></i>Servicios Recientes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentServices)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No hay servicios de bateria registrados aun.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentServices as $service): ?>
                            <div class="list-group-item service-card mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 fw-bold"><?= esc($service['service_name']) ?></h6>
                                        <p class="mb-1">
                                            <i class="fas fa-building text-muted me-2"></i>
                                            <strong><?= esc($service['company_name']) ?></strong>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($service['service_date'])) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $service['status'] === 'cerrado' ? 'success' : ($service['status'] === 'en_curso' ? 'warning' : 'secondary') ?> mb-2">
                                            <?= ucfirst(str_replace('_', ' ', $service['status'])) ?>
                                        </span>
                                        <br>
                                        <?php if ($service['status'] === 'cerrado'): ?>
                                            <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-sm btn-primary mt-2">
                                                <i class="fas fa-chart-bar me-1"></i>Ver Informes
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary mt-2" disabled>
                                                <i class="fas fa-clock me-1"></i>En Proceso
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
