<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Ajouter un préfixe</h4>
            <form action="<?= base_url('admin/ajouter-prefixe') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="prefixe" class="form-label">Préfixe</label>
                    <input type="text" name="prefixe" id="prefixe" class="form-control form-control-custom" 
                           placeholder="Ex: 033" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" name="description" id="description" class="form-control form-control-custom" 
                           placeholder="Opérateur A">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="est_autre_operateur" id="est_autre_operateur" class="form-check-input" value="1">
                        <label for="est_autre_operateur" class="form-check-label">Autre opérateur (inter-réseau)</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="commission_pourcentage" class="form-label">Commission (%)</label>
                    <input type="number" name="commission_pourcentage" id="commission_pourcentage" class="form-control form-control-custom" 
                           step="0.01" value="0">
                    <small class="text-muted">Pourcentage de commission à reverser à cet opérateur (uniquement pour les autres opérateurs).</small>
                </div>
                <button type="submit" class="btn btn-primary-custom">Ajouter</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Liste des préfixes</h4>
            <ul class="list-group">
                <?php foreach ($prefixes as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center" 
                        style="background:var(--bg-card);color:var(--text-light);border-color:var(--border-dark);">
                        <?= esc($p['prefixe']) ?> - <?= esc($p['description']) ?>
                        <div>
                            <a href="<?= base_url('admin/modifier-prefixe/' . $p['id']) ?>" 
                               class="btn btn-sm btn-outline-custom">Modifier</a>
                            <a href="<?= base_url('admin/supprimer-prefixe/' . $p['id']) ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Confirmer la suppression ?')">✕</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?= $this->endSection() ?>