<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card-glass p-4 border border-danger">
            <div class="text-center mb-4">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                <h2 class="text-danger mt-2">⚠️ Réinitialisation</h2>
                <p class="text-muted">Cette action est irréversible. Soyez extrêmement prudent.</p>
            </div>

            <div class="alert alert-danger">
                <h5 class="fw-bold">Ce qui sera <u>supprimé</u> :</h5>
                <ul class="mb-0">
                    <li>Tous les clients (sauf les administrateurs)</li>
                    <li>Toutes les transactions</li>
                    <li>Tous les gains enregistrés</li>
                    <li>Tous les envois multiples</li>
                </ul>
            </div>

            <div class="alert alert-success">
                <h5 class="fw-bold">Ce qui sera <u>conservé</u> :</h5>
                <ul class="mb-0">
                    <li><strong>Administrateurs</strong> : 0320408683 et 0320000001</li>
                    <li><strong>Barèmes de frais</strong> (tranches et montants)</li>
                    <li><strong>Types d'opérations</strong> (dépôt, retrait, transfert)</li>
                    <li><strong>Préfixes opérateur</strong> avec leurs commissions</li>
                </ul>
            </div>

            <div class="alert alert-warning mt-3">
                <i class="bi bi-info-circle me-1"></i>
                Les soldes des administrateurs seront remis à <strong>0</strong>.
            </div>

            <form action="<?= base_url('admin/reset-database') ?>" method="post" id="resetForm">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="confirm" class="form-label fw-bold">
                        Tapez <span class="text-danger">RESET</span> pour confirmer
                    </label>
                    <input type="text" name="confirm" id="confirm" class="form-control form-control-custom" 
                           placeholder="Tapez RESET ici" required>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2" id="resetBtn" disabled>
                    <i class="bi bi-arrow-counterclockwise me-2"></i>Réinitialiser la base
                </button>
            </form>

            <div class="mt-3 text-center">
                <a href="<?= base_url('admin/prefixes') ?>" class="btn btn-outline-custom">
                    <i class="bi bi-arrow-left me-1"></i>Annuler et revenir
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    const confirmInput = document.getElementById('confirm');
    const resetBtn = document.getElementById('resetBtn');

    confirmInput.addEventListener('input', function() {
        resetBtn.disabled = this.value.toUpperCase() !== 'RESET';
        if (this.value.toUpperCase() === 'RESET') {
            resetBtn.classList.remove('btn-secondary');
            resetBtn.classList.add('btn-danger');
        } else {
            resetBtn.classList.remove('btn-danger');
            resetBtn.classList.add('btn-secondary');
        }
    });
</script>

<style>
    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
        cursor: not-allowed;
        opacity: 0.65;
    }
    .btn-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }
</style>

<?= $this->endSection() ?>