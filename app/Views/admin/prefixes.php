<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row">
    <!-- Formulaire d'ajout -->
    <div class="col-lg-5">
        <div class="card-glass p-4">
            <h4 class="mb-3">
                <i class="bi bi-plus-circle me-2"></i>Ajouter un préfixe
            </h4>
            <form action="<?= base_url('admin/ajouter-prefixe') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="prefixe" class="form-label fw-semibold">Préfixe</label>
                    <input type="text" name="prefixe" id="prefixe" class="form-control form-control-custom" 
                           placeholder="Ex: 033" required>
                    <small class="text-muted">Exemple : 033, 034, 032...</small>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <input type="text" name="description" id="description" class="form-control form-control-custom" 
                           placeholder="Ex: Opérateur Telma">
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="est_autre_operateur" id="est_autre_operateur" class="form-check-input" value="1">
                        <label for="est_autre_operateur" class="form-check-label fw-semibold">
                            <i class="bi bi-arrow-left-right me-1"></i>Autre opérateur
                        </label>
                        <div class="text-muted small">Cocher si ce préfixe appartient à un autre réseau mobile.</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="commission_pourcentage" class="form-label fw-semibold">
                        <i class="bi bi-percent me-1"></i>Commission (%)
                    </label>
                    <div class="input-group">
                        <input type="number" name="commission_pourcentage" id="commission_pourcentage" 
                               class="form-control form-control-custom" step="0.01" value="0" min="0">
                        <span class="input-group-text bg-dark text-light border-0">%</span>
                    </div>
                    <small class="text-muted">Commission à reverser à cet opérateur (uniquement pour les autres opérateurs).</small>
                </div>
                
                <button type="submit" class="btn btn-primary-custom w-100">
                    <i class="bi bi-plus-circle me-1"></i>Ajouter le préfixe
                </button>
            </form>
        </div>
    </div>

    <!-- Liste des préfixes -->
    <div class="col-lg-7">
        <div class="card-glass p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Liste des préfixes
                </h4>
                <span class="badge bg-secondary"><?= count($prefixes) ?> préfixe(s)</span>
            </div>
            
            <?php if (!empty($prefixes)): ?>
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Préfixe</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Commission</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prefixes as $p): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold"><?= esc($p['prefixe']) ?></span>
                                    </td>
                                    <td><?= esc($p['description'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($p['prefixe'] === '032'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Réseau principal
                                            </span>
                                        <?php elseif ($p['est_autre_operateur']): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-arrow-right-circle me-1"></i>Autre opérateur
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">
                                                <i class="bi bi-house me-1"></i>Même réseau
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['commission_pourcentage'] > 0): ?>
                                            <span class="badge bg-dark">
                                                <?= number_format($p['commission_pourcentage'], 2) ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">0%</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['prefixe'] === '032'): ?>
                                            <span class="text-muted small">
                                                <i class="bi bi-lock me-1"></i>Protégé
                                            </span>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/modifier-prefixe/' . $p['id']) ?>" 
                                               class="btn btn-sm btn-outline-custom me-1" 
                                               title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= base_url('admin/supprimer-prefixe/' . $p['id']) ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Confirmer la suppression du préfixe <?= esc($p['prefixe']) ?> ?')"
                                               title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    <p>Aucun préfixe enregistré.</p>
                    <small>Ajoutez un préfixe dans le formulaire à gauche.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>