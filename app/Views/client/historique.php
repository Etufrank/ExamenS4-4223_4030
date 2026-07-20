<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card-glass p-4">
    <h4 class="mb-3">Mon historique</h4>
    <?php if (!empty($transactions)): ?>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Réf.</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Frais</th>
                        <th>Total</th>
                        <th>Sens</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?= esc($t['reference']) ?></td>
                        <td><?= esc($t['type_nom']) ?></td>
                        <td><?= number_format($t['montant'], 2) ?> Ar</td>
                        <td><?= number_format($t['frais_appliques'], 2) ?> Ar</td>
                        <td><?= number_format($t['montant_total'], 2) ?> Ar</td>
                        <td>
                            <?php if ($t['sens'] === 'credit'): ?>
                                <span class="badge-status badge-success">Crédit</span>
                            <?php else: ?>
                                <span class="badge-status badge-danger">Débit</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($t['statut'] === 'effectuee'): ?>
                                <span class="badge-status badge-success">Effectuée</span>
                            <?php elseif ($t['statut'] === 'annulee'): ?>
                                <span class="badge-status badge-danger">Annulée</span>
                            <?php else: ?>
                                <span class="badge-status badge-warning">En attente</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($t['date_transaction'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucune transaction enregistrée.</p>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>