<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card-glass p-4">
    <h4 class="mb-3">Situation des gains</h4>
    
    <div class="row">
        <div class="col-md-6">
            <h5 class="text-muted">Gains sur opérations opérateur (même réseau)</h5>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Total frais perçus</th>
                            <th>Période</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gains_operateur as $g): ?>
                        <tr>
                            <td><?= esc($g['type_nom']) ?></td>
                            <td><strong><?= number_format($g['montant_total_frais'], 2) ?> Ar</strong></td>
                            <td><?= date('d/m/Y', strtotime($g['periode_debut'])) ?> → <?= date('d/m/Y', strtotime($g['periode_fin'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <h5 class="text-muted">Gains sur opérations autres opérateurs (inter-réseau)</h5>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Total frais perçus</th>
                            <th>Période</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gains_autres as $g): ?>
                        <tr>
                            <td><?= esc($g['type_nom']) ?></td>
                            <td><strong><?= number_format($g['montant_total_frais'], 2) ?> Ar</strong></td>
                            <td><?= date('d/m/Y', strtotime($g['periode_debut'])) ?> → <?= date('d/m/Y', strtotime($g['periode_fin'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <hr>
    
    <h5 class="text-muted">Montants à envoyer à chaque opérateur</h5>
    <div class="table-responsive">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Opérateur</th>
                    <th>Montant total à envoyer</th>
                    <th>Commission</th>
                    <th>Montant net</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($montants_operateurs)): ?>
                    <?php foreach ($montants_operateurs as $m): ?>
                    <tr>
                        <td><?= esc($m['prefixe'] ?? 'Inconnu') ?></td>
                        <td><?= number_format($m['total_montant'] ?? 0, 2) ?> Ar</td>
                        <td><?= number_format($m['commission'] ?? 0, 2) ?> Ar</td>
                        <td><?= number_format($m['montant_net'] ?? 0, 2) ?> Ar</td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">Aucune donnée pour cette période.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>