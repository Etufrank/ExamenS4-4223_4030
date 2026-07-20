<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Ajouter un type d'opération</h4>
            <form action="<?= base_url('admin/ajouter-type') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control form-control-custom" 
                           placeholder="Ex: dépôt" required>
                </div>
                <div class="mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" name="code" id="code" class="form-control form-control-custom" 
                           placeholder="Ex: DEP" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" name="description" id="description" class="form-control form-control-custom" 
                           placeholder="Opération de dépôt">
                </div>
                <button type="submit" class="btn btn-primary-custom">Ajouter</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Types d'opérations</h4>
            <ul class="list-group">
                <?php foreach ($types as $t): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center" 
                        style="background:var(--bg-card);color:var(--text-light);border-color:var(--border-dark);">
                        <?= esc($t['nom']) ?> (<?= esc($t['code']) ?>)
                        <a href="<?= base_url('admin/supprimer-type/' . $t['id']) ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Confirmer la suppression ?')">✕</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?= $this->endSection() ?>