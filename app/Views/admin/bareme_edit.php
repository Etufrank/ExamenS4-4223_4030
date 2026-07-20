<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Modifier le barème</h4>
            <form action="<?= base_url('admin/mettre-a-jour-bareme/' . $bareme['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <input type="text" class="form-control" value="<?= esc($bareme['type_nom']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="montant_min" class="form-label">Min</label>
                    <input type="number" name="montant_min" id="montant_min" class="form-control form-control-custom" 
                           step="0.01" value="<?= $bareme['montant_min'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="montant_max" class="form-label">Max</label>
                    <input type="number" name="montant_max" id="montant_max" class="form-control form-control-custom" 
                           step="0.01" value="<?= $bareme['montant_max'] ?>" required>
                </div>
                <div class="mb-3">
                    <label for="frais_fixe" class="form-label">Frais fixe</label>
                    <input type="number" name="frais_fixe" id="frais_fixe" class="form-control form-control-custom" 
                           step="0.01" value="<?= $bareme['frais_fixe'] ?>">
                </div>
                <div class="mb-3">
                    <label for="frais_pourcentage" class="form-label">Pourcentage</label>
                    <input type="number" name="frais_pourcentage" id="frais_pourcentage" class="form-control form-control-custom" 
                           step="0.01" value="<?= $bareme['frais_pourcentage'] ?>">
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>