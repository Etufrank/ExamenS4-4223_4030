<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card-glass p-4">
    <h4 class="mb-3">Gestion des barèmes de frais</h4>
    <form action="<?= base_url('admin/ajouter-bareme') ?>" method="post" class="row g-3 mb-4">
        <?= csrf_field() ?>
        <div class="col-md-3">
            <label for="type_operation_id" class="form-label">Type</label>
            <select name="type_operation_id" id="type_operation_id" class="form-select form-control-custom" required>
                <option value="">Sélectionner</option>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= esc($t['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="montant_min" class="form-label">Min</label>
            <input type="number" name="montant_min" id="montant_min" class="form-control form-control-custom" 
                   step="0.01" required>
        </div>
        <div class="col-md-2">
            <label for="montant_max" class="form-label">Max</label>
            <input type="number" name="montant_max" id="montant_max" class="form-control form-control-custom" 
                   step="0.01" required>
        </div>
        <div class="col-md-2">
            <label for="frais_fixe" class="form-label">Frais fixe</label>
            <input type="number" name="frais_fixe" id="frais_fixe" class="form-control form-control-custom" 
                   step="0.01" value="0">
        </div>
        <div class="col-md-2">
            <label for="frais_pourcentage" class="form-label">%</label>
            <input type="number" name="frais_pourcentage" id="frais_pourcentage" class="form-control form-control-custom" 
                   step="0.01" value="0">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary-custom w-100">Ajouter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Frais fixe</th>
                    <th>%</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($baremes as $b): ?>
                <tr>
                    <td><?= esc($b['type_nom']) ?></td>
                    <td><?= number_format($b['montant_min'], 2) ?></td>
                    <td><?= number_format($b['montant_max'], 2) ?></td>
                    <td><?= number_format($b['frais_fixe'], 2) ?></td>
                    <td><?= number_format($b['frais_pourcentage'], 2) ?>%</td>
                    <td>
                        <a href="<?= base_url('admin/modifier-bareme/' . $b['id']) ?>" 
                           class="btn btn-sm btn-outline-custom">Modifier</a>
                        <a href="<?= base_url('admin/supprimer-bareme/' . $b['id']) ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Confirmer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>