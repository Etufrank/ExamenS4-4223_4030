<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="login-container">
    <div class="login-card">
        <h3 class="text-center mb-4">Connexion client</h3>
        <p class="text-muted text-center mb-4">Entrez votre numéro de téléphone pour vous connecter automatiquement.</p>
        <form action="<?= base_url('client/do-login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="numero_telephone" class="form-label">Numéro de téléphone</label>
                <input type="text" name="numero_telephone" id="numero_telephone" 
                       class="form-control form-control-custom" 
                       placeholder="0331234567" required>
                <small class="text-muted">Ex: 033, 034, 037, 038...</small>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">Se connecter</button>
        </form>
        <p class="text-center text-muted mt-3" style="font-size:13px;">
            Pas encore de compte ? Il sera créé automatiquement lors de votre première connexion.
        </p>
    </div>
</div>
<?= $this->endSection() ?>