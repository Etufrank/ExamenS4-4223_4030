<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mobile Money - Smartstock Pro' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <span>Examen Final</span>4223_4030
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <?php if (session()->get('isLoggedIn')): ?>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('client/dashboard') ?>">Tableau de bord</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('client/historique') ?>">Historique</a>
                        </li>

                        <?php if (session()->get('role') === 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Admin
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown" style="background: var(--bg-card); border: 1px solid var(--border-dark);">
                                    <li><a class="dropdown-item" href="<?= base_url('admin/prefixes') ?>" style="color: var(--text-light);">Préfixes</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/types-operations') ?>" style="color: var(--text-light);">Types d'opérations</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/baremes') ?>" style="color: var(--text-light);">Barèmes de frais</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/gains') ?>" style="color: var(--text-light);">Situation des gains</a></li>
                                    <li><a class="dropdown-item" href="<?= base_url('admin/clients') ?>" style="color: var(--text-light);">Comptes clients</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link text-danger" href="<?= base_url('client/logout') ?>">Déconnexion</a>
                        </li>

                    <?php else: ?>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('client/login') ?>">Connexion</a>
                        </li>

                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show alert-custom alert-success-custom" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show alert-custom alert-danger-custom" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        <?php endif ?>

        <?= $this->renderSection('content') ?>
    </div>

    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>