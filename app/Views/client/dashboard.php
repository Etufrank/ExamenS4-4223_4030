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
            <div class="stat-mini">
                <div class="label">💰 Solde épargne</div>
                <div class="value" style="font-size: 18px; color: #ffc107;">
                    <?= number_format($solde_epargne ?? 0, 0, ',', ' ') ?> <span style="font-size: 14px;">Ar</span>
                </div>
            </div>
            <div class="stat-mini">
                <div class="label">📊 Taux d'épargne</div>
                <div class="value" style="font-size: 16px;">
                    <?= number_format($epargne_pourcentage ?? 0, 2) ?>%
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-glass mt-4 p-4">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h6 class="mb-1">
                <i class="bi bi-piggy-bank me-2 text-warning"></i>Modifier le pourcentage d'épargne
            </h6>
            <small class="text-muted">
                Le pourcentage choisi sera automatiquement prélevé sur chaque dépôt et réception de transfert.
            </small>
        </div>
        <div class="col-lg-6">
            <form action="<?= base_url('client/set-epargne') ?>" method="post" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-8">
                    <div class="input-group">
                        <input type="number" name="epargne_pourcentage" 
                               class="form-control form-control-custom" 
                               step="0.01" min="0" max="100" 
                               value="<?= $epargne_pourcentage ?? 0 ?>" 
                               placeholder="Ex: 10" required>
                        <span class="input-group-text bg-dark text-light border-0">%</span>
                    </div>
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($transactions) && count($transactions) > 0): ?>
<div class="card-glass mt-4">
    <div class="card-header-custom">
        <h6>Transactions récentes</h6>
        <a href="<?= base_url('client/historique') ?>">Voir tout <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
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
                <?php $estEntrant = $t['sens'] === 'credit'; ?>
                <tr>
                    <td>
                        <div class="tx-icon <?= $estEntrant ? 'in' : 'out' ?>">
                            <i class="bi bi-arrow-<?= $estEntrant ? 'down-left' : 'up-right' ?>"></i>
                        </div>
                    </td>
                    <td><?= esc($t['description'] ?? ucfirst($t['type_nom'] ?? '')) ?></td>
                    <td class="text-muted"><?= date('d/m/Y H:i', strtotime($t['date_transaction'])) ?></td>
                    <td class="text-end fw-semibold <?= $estEntrant ? 'text-success' : 'text-danger' ?>">
                        <?= $estEntrant ? '+' : '-' ?><?= number_format($t['montant_total'], 0, ',', ' ') ?> Ar
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>