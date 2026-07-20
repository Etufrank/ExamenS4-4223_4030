<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Modifier le préfixe</h4>
            <form action="<?= base_url('admin/mettre-a-jour-prefixe/' . $prefixe['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="prefixe" class="form-label">Préfixe</label>
                    <input type="text" name="prefixe" id="prefixe" class="form-control form-control-custom" 
                           value="<?= esc($prefixe['prefixe']) ?>" readonly>
                    <small class="text-muted">Le préfixe ne peut pas être modifié.</small>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <input type="text" name="description" id="description" class="form-control form-control-custom" 
                           value="<?= esc($prefixe['description']) ?>">
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="est_autre_operateur" id="est_autre_operateur" class="form-check-input" value="1"
                               <?= $prefixe['est_autre_operateur'] ? 'checked' : '' ?>>
                        <label for="est_autre_operateur" class="form-check-label">Autre opérateur (inter-réseau)</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="commission_pourcentage" class="form-label">Commission (%) pour transferts vers cet opérateur</label>
                    <input type="number" name="commission_pourcentage" id="commission_pourcentage" class="form-control form-control-custom" 
                           step="0.01" value="<?= $prefixe['commission_pourcentage'] ?>">
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>