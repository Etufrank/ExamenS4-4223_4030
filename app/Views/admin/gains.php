<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card-glass p-4">
    <h4 class="mb-3">Situation des gains</h4>
    <?php if (!empty($gains)): ?>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Total frais perçus</th>
                        <th>Période</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gains as $g): ?>
                    <tr>
                        <td><?= esc($g['type_nom']) ?></td>
                        <td><strong><?= number_format($g['montant_total_frais'], 2) ?> Ar</strong></td>
                        <td><?= date('d/m/Y', strtotime($g['periode_debut'])) ?> → <?= date('d/m/Y', strtotime($g['periode_fin'])) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($g['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucun gain enregistré pour le moment.</p>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>