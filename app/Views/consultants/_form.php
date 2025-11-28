<?php $consultant = $consultant ?? []; ?>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="nombre_completo" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= isset($errors['nombre_completo']) ? 'is-invalid' : '' ?>"
                   id="nombre_completo" name="nombre_completo"
                   value="<?= old('nombre_completo', $consultant['nombre_completo'] ?? '') ?>" required>
            <?php if (isset($errors['nombre_completo'])): ?>
            <div class="invalid-feedback"><?= $errors['nombre_completo'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                <option value="CC" <?= old('tipo_documento', $consultant['tipo_documento'] ?? '') == 'CC' ? 'selected' : '' ?>>CC</option>
                <option value="CE" <?= old('tipo_documento', $consultant['tipo_documento'] ?? '') == 'CE' ? 'selected' : '' ?>>CE</option>
                <option value="PAS" <?= old('tipo_documento', $consultant['tipo_documento'] ?? '') == 'PAS' ? 'selected' : '' ?>>Pasaporte</option>
                <option value="OTRO" <?= old('tipo_documento', $consultant['tipo_documento'] ?? '') == 'OTRO' ? 'selected' : '' ?>>Otro</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label for="numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
            <input type="text" class="form-control <?= isset($errors['numero_documento']) ? 'is-invalid' : '' ?>"
                   id="numero_documento" name="numero_documento"
                   value="<?= old('numero_documento', $consultant['numero_documento'] ?? '') ?>" required>
            <?php if (isset($errors['numero_documento'])): ?>
            <div class="invalid-feedback"><?= $errors['numero_documento'] ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <input type="text" class="form-control" id="cargo" name="cargo"
                   value="<?= old('cargo', $consultant['cargo'] ?? 'Psicólogo Especialista SST') ?>"
                   placeholder="Ej: Psicólogo Especialista SST">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="licencia_sst" class="form-label">Licencia SST</label>
            <input type="text" class="form-control" id="licencia_sst" name="licencia_sst"
                   value="<?= old('licencia_sst', $consultant['licencia_sst'] ?? '') ?>"
                   placeholder="Número de licencia">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                   id="email" name="email"
                   value="<?= old('email', $consultant['email'] ?? '') ?>">
            <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?= $errors['email'] ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono"
                   value="<?= old('telefono', $consultant['telefono'] ?? '') ?>">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="website" class="form-label">Sitio Web</label>
            <input type="url" class="form-control" id="website" name="website"
                   value="<?= old('website', $consultant['website'] ?? '') ?>"
                   placeholder="https://ejemplo.com">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="linkedin" class="form-label">LinkedIn</label>
            <input type="url" class="form-control" id="linkedin" name="linkedin"
                   value="<?= old('linkedin', $consultant['linkedin'] ?? '') ?>"
                   placeholder="https://linkedin.com/in/usuario">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="firma" class="form-label">Imagen de Firma</label>
            <?php if (!empty($consultant['firma_path'])): ?>
            <div class="mb-2">
                <img src="<?= base_url($consultant['firma_path']) ?>" alt="Firma actual" class="img-thumbnail" style="max-height: 100px;">
                <small class="d-block text-muted">Firma actual</small>
            </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="firma" name="firma" accept="image/*">
            <small class="text-muted">Formatos: JPG, PNG, GIF. Tamaño recomendado: 300x100px</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label d-block">Estado</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                       <?= old('activo', $consultant['activo'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="activo">Consultor activo</label>
            </div>
        </div>
    </div>
</div>
