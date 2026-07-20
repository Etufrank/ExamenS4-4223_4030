<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card-glass p-4">
            <h4 class="mb-3">Effectuer un transfert multiple</h4>
            <p class="text-muted">Le montant total sera divisé équitablement entre tous les destinataires.</p>
            <form action="<?= base_url('client/do-transfert') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label class="form-label">Destinataires (un par ligne)</label>
                    <textarea name="destinataires" id="destinataires" class="form-control form-control-custom" rows="3" placeholder="0331234567&#10;0349876543&#10;0371122334" required></textarea>
                    <small class="text-muted">Un numéro par ligne. Le montant sera divisé équitablement.</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Montant total (Ar)</label>
                    <input type="number" name="montant" id="montant" class="form-control form-control-custom" step="0.01" min="100" placeholder="Ex: 10000" required>
                    <small class="text-muted">Ce montant sera divisé entre tous les destinataires.</small>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="frais_inclus" id="frais_inclus" class="form-check-input" value="1">
                        <label class="form-check-label" for="frais_inclus">
                            Inclure les frais de retrait dans le montant
                        </label>
                        <small class="text-muted d-block">Si coché, les frais sont déduits du montant envoyé à chaque destinataire.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary-custom w-100">Transférer</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>