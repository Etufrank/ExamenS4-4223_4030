<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Smartstock Pro' ?></title>

    <!-- ASSETS (dynamiques) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.css') ?>">
</head>
<body>

    <!-- NAVBAR (liens statiques) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">Smartstock Pro</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/register">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="/login">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="/products">Produits</a></li>
                    <li class="nav-item"><a class="nav-link" href="/categories">Catégories</a></li>
                    <?php if (session()->get('user_role') === 'admin') : ?>
                        <li class="nav-item"><a class="nav-link" href="/users">Utilisateurs</a></li>
                    <?php endif ?>
                    <li class="nav-item"><a class="nav-link" href="/suppliers">Fournisseurs</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENU PRINCIPAL -->
    <div class="container mt-4">

        <!-- Messages flash -->
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif ?>

        <!-- Section principale (remplacée par les vues enfants) -->
        <?= $this->renderSection('content') ?>
    </div>

    <!-- SCRIPTS (dynamiques) -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>