<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3"><i class="bi bi-pencil me-2"></i>Modifier la promotion</h4>
            <form action="<?= base_url('admin/mettre-a-jour-promotion/' . $promotion['id']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type</label>
                    <input type="text" class="form-control" value="<?= esc($promotion['type_nom']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label for="reduction_pourcentage" class="form-label fw-semibold">Réduction (%)</label>
                    <div class="input-group">
                        <input type="number" name="reduction_pourcentage" id="reduction_pourcentage" class="form-control form-control-custom" step="0.01" min="0" max="100" value="<?= $promotion['reduction_pourcentage'] ?>" required>
                        <span class="input-group-text bg-dark text-light border-0">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="date_debut" class="form-label fw-semibold">Date début</label>
                    <input type="datetime-local" name="date_debut" id="date_debut" class="form-control form-control-custom" value="<?= date('Y-m-d\TH:i', strtotime($promotion['date_debut'])) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="date_fin" class="form-label fw-semibold">Date fin</label>
                    <input type="datetime-local" name="date_fin" id="date_fin" class="form-control form-control-custom" value="<?= date('Y-m-d\TH:i', strtotime($promotion['date_fin'])) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary-custom w-100">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>