<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="solde-card h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="solde-label">Mon solde</div>
                <div class="solde-amount"><?= number_format($client['solde'], 0, ',', ' ') ?> <span>Ar</span></div>
            </div>
            <div class="row g-3 mt-4">
                <div class="col-3">
                    <a href="<?= base_url('client/depot') ?>" class="quick-action">
                        <i class="bi bi-plus-circle"></i>
                        <span>Dépôt</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="<?= base_url('client/retrait') ?>" class="quick-action">
                        <i class="bi bi-cash"></i>
                        <span>Retrait</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="<?= base_url('client/transfert') ?>" class="quick-action">
                        <i class="bi bi-arrow-right-square"></i>
                        <span>Transfert</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="<?= base_url('client/historique') ?>" class="quick-action">
                        <i class="bi bi-clock-history"></i>
                        <span>Historique</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="d-flex flex-column gap-3 h-100">
            <div class="stat-mini">
                <div class="label">Numéro de compte</div>
                <div class="value" style="font-size: 16px;"><?= esc($client['numero_telephone']) ?></div>
            </div>
            <?php if (isset($client['date_creation'])): ?>
            <div class="stat-mini">
                <div class="label">Client depuis</div>
                <div class="value" style="font-size: 16px;"><?= date('d/m/Y', strtotime($client['date_creation'])) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($transactions) && count($transactions) > 0): ?>
<div class="card-glass mt-4">
    <div class="card-header-custom">
        <h6>Transactions récentes</h6>
        <a href="<?= base_url('client/historique') ?>">Voir tout <i class="bi bi-arrow-right"></i></a>
    </div>
    <table class="table table-custom mb-0">
        <thead>
            <tr>
                <th></th>
                <th>Détails</th>
                <th>Date</th>
                <th class="text-end">Montant</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($transactions, 0, 5) as $t): ?>
            <?php $estEntrant = in_array($t['type'], ['depot', 'transfert_recu']); ?>
            <tr>
                <td>
                    <div class="tx-icon <?= $estEntrant ? 'in' : 'out' ?>">
                        <i class="bi bi-arrow-<?= $estEntrant ? 'down-left' : 'up-right' ?>"></i>
                    </div>
                </td>
                <td><?= esc($t['description'] ?? ucfirst($t['type'])) ?></td>
                <td class="text-muted"><?= date('d/m/Y H:i', strtotime($t['date'])) ?></td>
                <td class="text-end fw-semibold <?= $estEntrant ? 'text-success' : 'text-danger' ?>">
                    <?= $estEntrant ? '+' : '-' ?><?= number_format($t['montant'], 0, ',', ' ') ?> Ar
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?= $this->endSection() ?>