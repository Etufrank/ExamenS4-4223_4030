<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Effectuer un dépôt</h4>
            <form action="<?= base_url('client/do-depot') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="montant" class="form-label">Montant (Ar)</label>
                    <input type="number" name="montant" id="montant" class="form-control form-control-custom" 
                           step="0.01" min="100" placeholder="Ex: 1000" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Déposer</button>
            </form>
            <p class="text-muted mt-3 text-center" style="font-size:13px;">Le dépôt est automatique, les frais sont calculés selon le barème.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>