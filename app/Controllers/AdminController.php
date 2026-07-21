<?php

namespace App\Controllers;

use App\Models\PrefixeOperateurModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\ClientModel;
use App\Models\TransactionModel;
use App\Models\GainModel;
use App\Models\UserModel;
use App\Models\PromotionModel;

class AdminController extends BaseController
{
    protected $prefixeModel;
    protected $typeModel;
    protected $baremeModel;
    protected $clientModel;
    protected $transactionModel;
    protected $gainModel;
    protected $userModel;
    protected $promotionModel;

    public function __construct()
    {
        $this->prefixeModel = new PrefixeOperateurModel();
        $this->typeModel = new TypeOperationModel();
        $this->baremeModel = new BaremeFraisModel();
        $this->clientModel = new ClientModel();
        $this->transactionModel = new TransactionModel();
        $this->gainModel = new GainModel();
        $this->userModel = new UserModel();
        $this->promotionModel = new PromotionModel();
    }

    public function prefixes()
    {
        $data['prefixes'] = $this->prefixeModel->findAll();
        $data['title'] = 'Gestion des préfixes';
        return view('admin/prefixes', $data);
    }

    public function ajouterPrefixe()
    {
        $rules = [
            'prefixe' => 'required|is_unique[prefixes_operateur.prefixe]|min_length[2]|max_length[10]',
            'description' => 'permit_empty|max_length[255]',
            'est_autre_operateur' => 'permit_empty|integer',
            'commission_pourcentage' => 'permit_empty|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'prefixe'    => trim($this->request->getPost('prefixe')),
            'description' => trim($this->request->getPost('description')),
            'est_autre_operateur' => (int) $this->request->getPost('est_autre_operateur'),
            'commission_pourcentage' => (float) ($this->request->getPost('commission_pourcentage') ?: 0),
        ];

        try {
            if ($this->prefixeModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion : ' . implode(', ', $this->prefixeModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur insertion préfixe : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        return redirect()->to('/admin/prefixes')->with('success', 'Préfixe ajouté.');
    }

    public function modifierPrefixe($id)
    {
        $prefixe = $this->prefixeModel->find($id);
        if (!$prefixe) {
            return redirect()->to('/admin/prefixes')->with('error', 'Préfixe introuvable.');
        }
        if ($prefixe['prefixe'] === '032') {
            return redirect()->to('/admin/prefixes')->with('error', 'Le préfixe 032 est le réseau principal et ne peut pas être modifié.');
        }
        $data['prefixe'] = $prefixe;
        $data['title'] = 'Modifier un préfixe';
        return view('admin/prefixe_edit', $data);
    }

    public function mettreAJourPrefixe($id)
    {
        $prefixe = $this->prefixeModel->find($id);
        if (!$prefixe) {
            return redirect()->to('/admin/prefixes')->with('error', 'Préfixe introuvable.');
        }
        if ($prefixe['prefixe'] === '032') {
            return redirect()->to('/admin/prefixes')->with('error', 'Le préfixe 032 est le réseau principal et ne peut pas être modifié.');
        }

        $rules = [
            'prefixe' => 'required|min_length[2]|max_length[10]',
            'description' => 'permit_empty|max_length[255]',
            'est_autre_operateur' => 'permit_empty|integer',
            'commission_pourcentage' => 'permit_empty|numeric|greater_than_equal_to[0]',
        ];

        if ($this->request->getPost('prefixe') !== $prefixe['prefixe']) {
            $rules['prefixe'] .= '|is_unique[prefixes_operateur.prefixe]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'prefixe'    => trim($this->request->getPost('prefixe')),
            'description' => trim($this->request->getPost('description')),
            'est_autre_operateur' => (int) $this->request->getPost('est_autre_operateur'),
            'commission_pourcentage' => (float) ($this->request->getPost('commission_pourcentage') ?: 0),
        ];

        try {
            if ($this->prefixeModel->update($id, $data) === false) {
                $errors = $this->prefixeModel->errors();
                $errorMsg = !empty($errors) ? implode(', ', $errors) : 'Erreur inconnue.';
                return redirect()->back()->withInput()->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur mise à jour préfixe : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        return redirect()->to('/admin/prefixes')->with('success', 'Préfixe mis à jour.');
    }

    public function supprimerPrefixe($id)
    {
        $prefixe = $this->prefixeModel->find($id);
        if ($prefixe && $prefixe['prefixe'] === '032') {
            return redirect()->to('/admin/prefixes')->with('error', 'Le préfixe 032 est le réseau principal et ne peut pas être supprimé.');
        }
        $this->prefixeModel->delete($id);
        return redirect()->to('/admin/prefixes')->with('success', 'Préfixe supprimé');
    }

    public function typesOperations()
    {
        $data['types'] = $this->typeModel->findAll();
        $data['title'] = 'Gestion des types d\'opérations';
        return view('admin/types_operations', $data);
    }

    public function ajouterType()
    {
        $rules = [
            'nom'  => 'required|is_unique[types_operations.nom]|max_length[50]',
            'code' => 'required|is_unique[types_operations.code]|min_length[2]|max_length[10]',
            'description' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'nom'         => trim($this->request->getPost('nom')),
            'code'        => strtoupper(trim($this->request->getPost('code'))),
            'description' => trim($this->request->getPost('description')),
        ];

        try {
            if ($this->typeModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion type : ' . implode(', ', $this->typeModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur insertion type : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        return redirect()->to('/admin/types-operations')->with('success', 'Type ajouté.');
    }

    public function supprimerType($id)
    {
        $this->typeModel->delete($id);
        return redirect()->to('/admin/types-operations')->with('success', 'Type supprimé');
    }

    public function baremes()
    {
        $data['baremes'] = $this->baremeModel->getBaremesWithType();
        $data['types']   = $this->typeModel->findAll();
        $data['title']   = 'Gestion des barèmes de frais';
        return view('admin/baremes', $data);
    }

    public function ajouterBareme()
    {
        $rules = [
            'type_operation_id'  => 'required|is_natural_no_zero',
            'montant_min'        => 'required|numeric|greater_than_equal_to[0]',
            'montant_max'        => 'required|numeric|greater_than_equal_to[0]',
            'frais_fixe'         => 'permit_empty|numeric|greater_than_equal_to[0]',
            'frais_pourcentage'  => 'permit_empty|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $min = (float) $this->request->getPost('montant_min');
        $max = (float) $this->request->getPost('montant_max');
        if ($min > $max) {
            return redirect()->back()->withInput()->with('error', 'Le montant minimum ne peut pas être supérieur au montant maximum.');
        }

        $data = [
            'type_operation_id' => (int) $this->request->getPost('type_operation_id'),
            'montant_min'       => $min,
            'montant_max'       => $max,
            'frais_fixe'        => (float) ($this->request->getPost('frais_fixe') ?: 0),
            'frais_pourcentage' => (float) ($this->request->getPost('frais_pourcentage') ?: 0),
        ];

        try {
            if ($this->baremeModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion barème : ' . implode(', ', $this->baremeModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur insertion barème : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        return redirect()->to('/admin/baremes')->with('success', 'Barème ajouté.');
    }

    public function modifierBareme($id)
    {
        $bareme = $this->baremeModel->find($id);
        if (!$bareme) {
            return redirect()->to('/admin/baremes')->with('error', 'Barème introuvable');
        }
        $data['bareme'] = $bareme;
        $data['types']  = $this->typeModel->findAll();
        $data['title']  = 'Modifier un barème';
        return view('admin/bareme_edit', $data);
    }

    public function mettreAJourBareme($id)
    {
        $rules = [
            'montant_min'        => 'required|numeric|greater_than_equal_to[0]',
            'montant_max'        => 'required|numeric|greater_than_equal_to[0]',
            'frais_fixe'         => 'permit_empty|numeric|greater_than_equal_to[0]',
            'frais_pourcentage'  => 'permit_empty|numeric|greater_than_equal_to[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $min = (float) $this->request->getPost('montant_min');
        $max = (float) $this->request->getPost('montant_max');
        if ($min > $max) {
            return redirect()->back()->withInput()->with('error', 'Le montant minimum ne peut pas être supérieur au montant maximum.');
        }

        $data = [
            'montant_min'       => $min,
            'montant_max'       => $max,
            'frais_fixe'        => (float) ($this->request->getPost('frais_fixe') ?: 0),
            'frais_pourcentage' => (float) ($this->request->getPost('frais_pourcentage') ?: 0),
        ];

        try {
            if ($this->baremeModel->update($id, $data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur mise à jour barème : ' . implode(', ', $this->baremeModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur mise à jour barème : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        return redirect()->to('/admin/baremes')->with('success', 'Barème mis à jour.');
    }

    public function supprimerBareme($id)
    {
        $this->baremeModel->delete($id);
        return redirect()->to('/admin/baremes')->with('success', 'Barème supprimé');
    }

    public function gains()
    {
        $periodeDebut = date('Y-m-d 00:00:00', strtotime('first day of this month'));
        $periodeFin   = date('Y-m-d 23:59:59', strtotime('last day of this month'));

        $retraitId = $this->typeModel->where('code', 'RET')->first()['id'] ?? 0;
        $transfertId = $this->typeModel->where('code', 'TRANS')->first()['id'] ?? 0;

        $gainsOperateur = [];
        if ($retraitId) {
            $gainsOperateur[] = [
                'type_nom' => 'Retrait',
                'montant_total_frais' => $this->transactionModel->getGainsByType($retraitId, $periodeDebut, $periodeFin, 0),
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
            ];
        }
        if ($transfertId) {
            $gainsOperateur[] = [
                'type_nom' => 'Transfert',
                'montant_total_frais' => $this->transactionModel->getGainsByType($transfertId, $periodeDebut, $periodeFin, 0),
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
            ];
        }

        $gainsAutres = [];
        if ($retraitId) {
            $gainsAutres[] = [
                'type_nom' => 'Retrait',
                'montant_total_frais' => $this->transactionModel->getGainsByType($retraitId, $periodeDebut, $periodeFin, 1),
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
            ];
        }
        if ($transfertId) {
            $gainsAutres[] = [
                'type_nom' => 'Transfert',
                'montant_total_frais' => $this->transactionModel->getGainsByType($transfertId, $periodeDebut, $periodeFin, 1),
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
            ];
        }

        $montantsOperateurs = $this->transactionModel->getMontantsParOperateur($periodeDebut, $periodeFin);

        foreach ($montantsOperateurs as &$m) {
            $commission = ($m['total_montant'] ?? 0) * (($m['commission_pourcentage'] ?? 0) / 100);
            $m['commission'] = $commission;
            $m['montant_net'] = ($m['total_montant'] ?? 0) - $commission;
        }

        $data = [
            'gains_operateur' => $gainsOperateur,
            'gains_autres' => $gainsAutres,
            'montants_operateurs' => $montantsOperateurs,
            'title' => 'Situation des gains - V2',
        ];

        return view('admin/gains', $data);
    }

    public function clients()
    {
        $data['clients'] = $this->clientModel->findAll();
        $data['title']   = 'Situation des comptes clients';
        return view('admin/clients', $data);
    }

    public function promotions()
    {
        $data['promotions'] = $this->promotionModel->getPromotionsWithType();
        $data['types']      = $this->typeModel->findAll();
        $data['title']      = 'Gestion des promotions';
        return view('admin/promotions', $data);
    }

    public function ajouterPromotion()
    {
        $rules = [
            'type_operation_id'    => 'required|is_natural_no_zero',
            'reduction_pourcentage'=> 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'date_debut'           => 'required|valid_date',
            'date_fin'             => 'required|valid_date|after[date_debut]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'type_operation_id'    => (int) $this->request->getPost('type_operation_id'),
            'operateur_prefixe'    => '032',
            'reduction_pourcentage'=> (float) $this->request->getPost('reduction_pourcentage'),
            'date_debut'           => $this->request->getPost('date_debut'),
            'date_fin'             => $this->request->getPost('date_fin'),
        ];

        try {
            if ($this->promotionModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion : ' . implode(', ', $this->promotionModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur insertion promotion : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        return redirect()->to('/admin/promotions')->with('success', 'Promotion ajoutée.');
    }

    public function modifierPromotion($id)
    {
        $promotion = $this->promotionModel->find($id);
        if (!$promotion) {
            return redirect()->to('/admin/promotions')->with('error', 'Promotion introuvable.');
        }
        $data['promotion'] = $promotion;
        $data['types']     = $this->typeModel->findAll();
        $data['title']     = 'Modifier une promotion';
        return view('admin/promotion_edit', $data);
    }

    public function mettreAJourPromotion($id)
    {
        $rules = [
            'reduction_pourcentage'=> 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'date_debut'           => 'required|valid_date',
            'date_fin'             => 'required|valid_date|after[date_debut]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'reduction_pourcentage'=> (float) $this->request->getPost('reduction_pourcentage'),
            'date_debut'           => $this->request->getPost('date_debut'),
            'date_fin'             => $this->request->getPost('date_fin'),
        ];

        try {
            if ($this->promotionModel->update($id, $data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur mise à jour.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur mise à jour promotion : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        return redirect()->to('/admin/promotions')->with('success', 'Promotion mise à jour.');
    }

    public function supprimerPromotion($id)
    {
        $this->promotionModel->delete($id);
        return redirect()->to('/admin/promotions')->with('success', 'Promotion supprimée.');
    }

    public function resetDatabasePage()
    {
        $data['title'] = 'Réinitialisation de la base';
        return view('admin/reset_database', $data);
    }

    public function resetDatabase()
    {
        $confirm = $this->request->getPost('confirm');
        if (strtoupper($confirm) !== 'RESET') {
            return redirect()->back()->with('error', 'Confirmation incorrecte.');
        }

        $this->db->table('transactions')->truncate();
        $this->db->table('gains')->truncate();
        $this->db->table('envois_multiples')->truncate();

        $adminUsers = $this->userModel->whereIn('username', ['0320408683', '0320000001'])->findAll();
        $adminIds = array_column($adminUsers, 'id');

        $this->db->table('clients')->whereNotIn('user_id', $adminIds)->delete();
        $this->db->table('users')->whereNotIn('id', $adminIds)->delete();
        $this->db->table('clients')->whereIn('user_id', $adminIds)->update(['solde' => 0, 'solde_epargne' => 0]);

        return redirect()->to('/admin/prefixes')->with('success', 'Base réinitialisée.');
    }
}