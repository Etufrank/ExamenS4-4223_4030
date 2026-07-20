<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="login-container">
    <div class="login-card">
        <h3 class="text-center mb-4">Créer un compte</h3>
        <p class="text-muted text-center">Remplissez les champs ci-dessous.</p>
        <form action="<?= base_url('client/do-register') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="numero_telephone" class="form-label">Numéro de téléphone *</label>
                <input type="text" name="numero_telephone" id="numero_telephone" class="form-control form-control-custom" value="<?= old('numero_telephone', $numero) ?>" placeholder="0321234567" required>
                <div id="phoneError" class="text-danger small mt-1"></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom *</label>
                    <input type="text" name="nom" id="nom" class="form-control form-control-custom" value="<?= old('nom') ?>" placeholder="Dupont" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom *</label>
                    <input type="text" name="prenom" id="prenom" class="form-control form-control-custom" value="<?= old('prenom') ?>" placeholder="Jean" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email (optionnel)</label>
                <input type="email" name="email" id="email" class="form-control form-control-custom" value="<?= old('email') ?>" placeholder="exemple@mail.com">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe *</label>
                <input type="password" name="password" id="password" class="form-control form-control-custom" placeholder="Min 4 caractères" required>
                <div id="passwordError" class="text-danger small mt-1"></div>
            </div>
            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe *</label>
                <input type="password" name="password_confirm" id="password_confirm" class="form-control form-control-custom" placeholder="Retapez votre mot de passe" required>
                <div id="confirmError" class="text-danger small mt-1"></div>
            </div>
            <button type="submit" class="btn btn-primary-custom w-100">S'inscrire</button>
        </form>
        <p class="text-center text-muted mt-3" style="font-size: 13px;">
            Déjà un compte ? <a href="<?= base_url('client/login') ?>" style="color: var(--sky);">Se connecter</a>
        </p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const numInput = document.querySelector('input[name="numero_telephone"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmInput = document.querySelector('input[name="password_confirm"]');

    function isValidPhone(number) {
        const regex = /^(032|033|034|037|038)\d{7}$|^\+261(32|33|34|37|38)\d{7}$/;
        return regex.test(number.trim());
    }

    async function checkPhoneExists(number) {
        try {
            const response = await fetch('<?= base_url('client/check-phone') ?>?numero=' + encodeURIComponent(number));
            const data = await response.json();
            return data.exists;
        } catch (e) {
            console.error(e);
            return false;
        }
    }

    numInput.addEventListener('blur', async function() {
        const val = this.value.trim();
        const errorEl = document.getElementById('phoneError');
        if (!val) {
            errorEl.textContent = 'Le numéro est requis.';
            return;
        }
        if (!isValidPhone(val)) {
            errorEl.textContent = 'Format invalide. Ex: 0321234567 ou +261321234567';
            return;
        }
        const exists = await checkPhoneExists(val);
        if (exists) {
            errorEl.textContent = 'Ce numéro est déjà utilisé.';
            this.classList.add('is-invalid');
        } else {
            errorEl.textContent = '';
            this.classList.remove('is-invalid');
        }
    });

    passwordInput.addEventListener('input', function() {
        const errorEl = document.getElementById('passwordError');
        if (this.value.length > 0 && this.value.length < 4) {
            errorEl.textContent = 'Minimum 4 caractères.';
        } else {
            errorEl.textContent = '';
        }
    });

    confirmInput.addEventListener('input', function() {
        const errorEl = document.getElementById('confirmError');
        if (this.value !== passwordInput.value) {
            errorEl.textContent = 'Les mots de passe ne correspondent pas.';
        } else {
            errorEl.textContent = '';
        }
    });

    form.addEventListener('submit', async function(e) {
        const num = numInput.value.trim();
        if (!isValidPhone(num)) {
            e.preventDefault();
            alert('Numéro de téléphone invalide. Format attendu: 0321234567 ou +261321234567');
            return;
        }
        const exists = await checkPhoneExists(num);
        if (exists) {
            e.preventDefault();
            alert('Ce numéro est déjà utilisé.');
            return;
        }
        if (passwordInput.value !== confirmInput.value) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
            return;
        }
        if (passwordInput.value.length < 4) {
            e.preventDefault();
            alert('Le mot de passe doit contenir au moins 4 caractères.');
            return;
        }
    });
});
</script>
<?= $this->endSection() ?>