<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-6">
        <div class="solde-card">
            <div class="solde-label">Mon solde</div>
            <div class="solde-amount"><?= number_format($client['solde'], 2) ?> <span>Ar</span></div>
            <p class="text-muted mt-2">Numéro : <strong><?= $client['numero_telephone'] ?></strong></p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row g-3">
            <div class="col-6">
                <a href="<?= base_url('client/depot') ?>" class="btn btn-primary-custom w-100 py-3">
                    <i class="bi bi-plus-circle"></i> Dépôt
                </a>
            </div>
            <div class="col-6">
                <a href="<?= base_url('client/retrait') ?>" class="btn btn-outline-custom w-100 py-3">
                    <i class="bi bi-cash"></i> Retrait
                </a>
            </div>
            <div class="col-12">
                <a href="<?= base_url('client/transfert') ?>" class="btn btn-outline-custom w-100 py-3">
                    <i class="bi bi-arrow-right-square"></i> Transfert
                </a>
            </div>
            <div class="col-12">
                <a href="<?= base_url('client/historique') ?>" class="btn btn-outline-custom w-100 py-3">
                    <i class="bi bi-clock-history"></i> Voir l'historique
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>