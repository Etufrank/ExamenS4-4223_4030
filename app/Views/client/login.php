<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="login-container">
    <div class="login-card">
        <h3 class="text-center mb-4">Connexion</h3>
        <p class="text-muted text-center">Entrez votre numéro pour vous connecter.</p>
        <form action="<?= base_url('client/do-login') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="numero_telephone" class="form-label">Numéro de téléphone</label>
                <input type="text" name="numero_telephone" id="numero_telephone" class="form-control form-control-custom" placeholder="0321234567" required>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">Se connecter</button>
        </form>
        <p class="text-center text-muted mt-3" style="font-size: 13px;">
            Pas encore de compte ? <a href="<?= base_url('client/register') ?>" style="color: var(--sky);">S'inscrire</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>