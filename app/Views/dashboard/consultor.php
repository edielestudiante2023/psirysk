<?= $this->extend('layouts/main') ?>

<?= $this->section('sidebar_menu') ?>
<a class="nav-link active" href="<?= base_url('dashboard') ?>">
    <i class="fas fa-home me-2"></i> Dashboard
</a>
<a class="nav-link" href="<?= base_url('companies') ?>" target="_blank">
    <i class="fas fa-building me-2"></i> Clientes
</a>
<a class="nav-link" href="<?= base_url('battery-services') ?>" target="_blank">
    <i class="fas fa-clipboard-check me-2"></i> Servicios de Bateria
</a>
<a class="nav-link" href="<?= base_url('consultants') ?>" target="_blank">
    <i class="fas fa-id-card me-2"></i> Consultores
</a>
<a class="nav-link" href="<?= base_url('client-users') ?>" target="_blank">
    <i class="fas fa-user-tie me-2"></i> Usuarios de Cliente
</a>
<a class="nav-link" href="<?= base_url('workers') ?>" target="_blank">
    <i class="fas fa-users me-2"></i> Trabajadores
</a>
<a class="nav-link" href="<?= base_url('csv-import') ?>" target="_blank">
    <i class="fas fa-file-csv me-2"></i> Importar CSV
</a>
<a class="nav-link" href="<?= base_url('reports') ?>" target="_blank">
    <i class="fas fa-chart-bar me-2"></i> Informes
</a>
<a class="nav-link" href="<?= base_url('satisfaction/dashboard') ?>" target="_blank">
    <i class="fas fa-star me-2"></i> Encuestas Satisfaccion
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
<!-- Action Buttons -->
<div class="mb-4">
    <a href="<?= base_url('companies/create') ?>" class="btn btn-primary me-2" target="_blank">
        <i class="fas fa-plus me-2"></i>Nuevo Cliente
    </a>
    <a href="<?= base_url('battery-services/create') ?>" class="btn btn-success" target="_blank">
        <i class="fas fa-plus me-2"></i>Nueva Bateria
    </a>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-6">
        <div class="card stat-card bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Clientes</h6>
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
                    <h6 class="text-uppercase mb-1">Servicios Activos</h6>
                    <h2 class="fw-bold mb-0"><?= $totalServices ?></h2>
                </div>
                <i class="fas fa-clipboard-check icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Services -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list text-primary me-2"></i>Servicios Recientes</h5>
                    <a href="<?= base_url('battery-services') ?>" class="btn btn-sm btn-outline-primary" target="_blank">Ver Todos</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($recentServices)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No hay servicios de bateria registrados aun.</p>
                        <a href="<?= base_url('battery-services/create') ?>" class="btn btn-success" target="_blank">
                            <i class="fas fa-plus me-2"></i>Crear Primer Servicio
                        </a>
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
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-users me-1"></i>
                                            <strong><?= $service['worker_count'] ?? 0 ?></strong> trabajadores
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $service['status'] === 'finalizado' ? 'success' : ($service['status'] === 'en_curso' ? 'warning' : 'secondary') ?> mb-2">
                                            <?= ucfirst($service['status']) ?>
                                        </span>
                                        <br>
                                        <a href="<?= base_url('battery-services/' . $service['id']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-eye me-1"></i>Ver Detalles
                                        </a>
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
