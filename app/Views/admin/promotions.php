<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-5">
        <div class="card-glass p-4">
            <h4 class="mb-3"><i class="bi bi-plus-circle me-2"></i>Ajouter une promotion</h4>
            <form action="<?= base_url('admin/ajouter-promotion') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="type_operation_id" class="form-label fw-semibold">Type d'opération</label>
                    <select name="type_operation_id" id="type_operation_id" class="form-select form-control-custom" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="reduction_pourcentage" class="form-label fw-semibold">Réduction (%)</label>
                    <div class="input-group">
                        <input type="number" name="reduction_pourcentage" id="reduction_pourcentage" class="form-control form-control-custom" step="0.01" min="0" max="100" placeholder="Ex: 20" required>
                        <span class="input-group-text bg-dark text-light border-0">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="date_debut" class="form-label fw-semibold">Date de début</label>
                    <input type="datetime-local" name="date_debut" id="date_debut" class="form-control form-control-custom" required>
                </div>
                <div class="mb-3">
                    <label for="date_fin" class="form-label fw-semibold">Date de fin</label>
                    <input type="datetime-local" name="date_fin" id="date_fin" class="form-control form-control-custom" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100"><i class="bi bi-plus-circle me-1"></i>Ajouter</button>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card-glass p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0"><i class="bi bi-list-ul me-2"></i>Promotions</h4>
                <span class="badge bg-secondary"><?= count($promotions) ?></span>
            </div>
            <?php if (!empty($promotions)): ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Réduction</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($promotions as $p): ?>
                                <?php
                                $now = time();
                                $debut = strtotime($p['date_debut']);
                                $fin = strtotime($p['date_fin']);
                                $active = ($now >= $debut && $now <= $fin);
                                ?>
                                <tr>
                                    <td><?= esc($p['type_nom']) ?></td>
                                    <td><span class="badge bg-success"><?= number_format($p['reduction_pourcentage'], 2) ?>%</span></td>
                                    <td><?= date('d/m/Y H:i', $debut) ?></td>
                                    <td><?= date('d/m/Y H:i', $fin) ?></td>
                                    <td>
                                        <?php if ($active): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('admin/modifier-promotion/' . $p['id']) ?>" class="btn btn-sm btn-outline-custom me-1"><i class="bi bi-pencil"></i></a>
                                        <a href="<?= base_url('admin/supprimer-promotion/' . $p['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Confirmer ?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted"><p>Aucune promotion.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>