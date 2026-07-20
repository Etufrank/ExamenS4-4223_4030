<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="card-glass p-4">
    <h4 class="mb-3">Situation des comptes clients</h4>
    <?php if (!empty($clients)): ?>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Numéro</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Solde</th>
                        <th>Statut</th>
                        <th>Date création</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= esc($c['numero_telephone']) ?></td>
                        <td><?= esc($c['nom']) ?></td>
                        <td><?= esc($c['prenom']) ?></td>
                        <td><strong><?= number_format($c['solde'], 2) ?> Ar</strong></td>
                        <td>
                            <?php if ($c['statut'] === 'actif'): ?>
                                <span class="badge-status badge-success">Actif</span>
                            <?php else: ?>
                                <span class="badge-status badge-danger">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($c['date_creation'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">Aucun client enregistré.</p>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>