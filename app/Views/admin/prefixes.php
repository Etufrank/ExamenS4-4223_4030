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
                    <label for="commission_pourcentage" class="form-label">Commission (%) pour transferts vers cet opérateur</label>
                    <input type="number" name="commission_pourcentage" id="commission_pourcentage" class="form-control form-control-custom" 
                           step="0.01" value="0">
                </div>
                <button type="submit" class="btn btn-primary-custom">Ajouter</button>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Liste des préfixes</h4>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Préfixe</th>
                            <th>Description</th>
                            <th>Autre opérateur</th>
                            <th>Commission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prefixes as $p): ?>
                        <tr>
                            <td><?= esc($p['prefixe']) ?></td>
                            <td><?= esc($p['description']) ?></td>
                            <td><?= $p['est_autre_operateur'] ? 'Oui' : 'Non' ?></td>
                            <td><?= number_format($p['commission_pourcentage'], 2) ?>%</td>
                            <td>
                                <a href="<?= base_url('admin/modifier-prefixe/' . $p['id']) ?>" class="btn btn-sm btn-outline-custom">Modifier</a>
                                <a href="<?= base_url('admin/supprimer-prefixe/' . $p['id']) ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Confirmer la suppression ?')">✕</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
