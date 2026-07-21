<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TransactionModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\UserModel;
use App\Models\PrefixeOperateurModel;
use App\Models\PromotionModel;

class ClientController extends BaseController
{
    protected $clientModel;
    protected $transactionModel;
    protected $typeModel;
    protected $baremeModel;
    protected $userModel;
    protected $prefixeModel;
    protected $promotionModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->transactionModel = new TransactionModel();
        $this->typeModel = new TypeOperationModel();
        $this->baremeModel = new BaremeFraisModel();
        $this->userModel = new UserModel();
        $this->prefixeModel = new PrefixeOperateurModel();
        $this->promotionModel = new PromotionModel();
    }

    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/client/dashboard');
        }
        $data['title'] = 'Connexion client';
        return view('client/login', $data);
    }

    public function doLogin()
    {
        $numero = $this->request->getPost('numero_telephone');
        if (!$numero) {
            return redirect()->back()->withInput()->with('error', 'Veuillez entrer votre numéro.');
        }

        $user = $this->userModel->where('username', $numero)->first();

        if ($user && $user['role'] === 'admin') {
        } else {
            if (substr($numero, 0, 3) !== '032') {
                return redirect()->back()->withInput()->with('error', 'Seuls les numéros 032 peuvent se connecter.');
            }
        }

        $client = $this->clientModel->findByNumero($numero);

        if (!$client) {
            $userId = $this->userModel->insert([
                'username'   => $numero,
                'password'   => password_hash($numero, PASSWORD_DEFAULT),
                'role'       => 'client',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $clientId = $this->clientModel->insert([
                'user_id'          => $userId,
                'numero_telephone' => $numero,
                'nom'              => 'Client',
                'prenom'           => 'Auto',
                'solde'            => 0,
                'date_creation'    => date('Y-m-d H:i:s'),
                'statut'           => 'actif',
                'epargne_pourcentage' => 0,
                'solde_epargne'    => 0,
            ]);
            $client = $this->clientModel->find($clientId);
        }

        session()->set([
            'client_id'        => $client['id'],
            'numero_telephone' => $client['numero_telephone'],
            'nom'              => $client['nom'] . ' ' . $client['prenom'],
            'role'             => $user['role'] ?? 'client',
            'isLoggedIn'       => true
        ]);

        return redirect()->to('/client/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/client/login');
    }

    public function dashboard()
    {
        $clientId = session()->get('client_id');
        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/client/login')->with('error', 'Veuillez vous reconnecter.');
        }
        $data['client'] = $client;
        $data['solde_epargne'] = $client['solde_epargne'] ?? 0;
        $data['epargne_pourcentage'] = $client['epargne_pourcentage'] ?? 0;
        $data['title'] = 'Mon compte';
        $data['transactions'] = $this->transactionModel->getTransactionsByClient($clientId, 5);
        return view('client/dashboard', $data);
    }

    public function depot()
    {
        $data['title'] = 'Faire un dépôt';
        return view('client/depot', $data);
    }

    public function doDepot()
    {
        $clientId = session()->get('client_id');
        $montant = (float) $this->request->getPost('montant');

        if (!$montant || $montant <= 0) {
            return redirect()->back()->withInput()->with('error', 'Montant invalide.');
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/client/login')->with('error', 'Client introuvable.');
        }

        $type = $this->typeModel->getTypeByCode('DEP');
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type "dépôt" introuvable.');
        }

        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montant);
        $frais = $bareme ? ($bareme['frais_fixe'] + ($montant * $bareme['frais_pourcentage'] / 100)) : 0;
        $montantTotal = $montant - $frais;

        $epargnePct = $client['epargne_pourcentage'] ?? 0;
        $montantEpargne = $montantTotal * ($epargnePct / 100);
        $montantDisponible = $montantTotal - $montantEpargne;

        $data = [
            'reference'          => $this->transactionModel->generateReference(),
            'type_operation_id'  => $type['id'],
            'client_id'          => $clientId,
            'montant'            => $montant,
            'frais_appliques'    => $frais,
            'frais_inclus'       => 0,
            'montant_total'      => $montantTotal,
            'sens'               => 'credit',
            'statut'             => 'effectuee',
            'description'        => 'Dépôt de ' . number_format($montant, 2) . ' Ar',
            'destinataire_original' => null,
            'est_inter_operateur' => 0,
        ];

        try {
            if ($this->transactionModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion dépôt.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur dépôt : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] + $montantDisponible,
            'solde_epargne' => $client['solde_epargne'] + $montantEpargne
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Dépôt effectué ! Solde disponible: ' . number_format($montantDisponible, 2) . ' Ar, Épargne: ' . number_format($montantEpargne, 2) . ' Ar');
    }

    public function retrait()
    {
        $data['title'] = 'Faire un retrait';
        return view('client/retrait', $data);
    }

    public function doRetrait()
    {
        $clientId = session()->get('client_id');
        $montant = (float) $this->request->getPost('montant');

        if (!$montant || $montant <= 0) {
            return redirect()->back()->withInput()->with('error', 'Montant invalide.');
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/client/login')->with('error', 'Client introuvable.');
        }

        $type = $this->typeModel->getTypeByCode('RET');
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type "retrait" introuvable.');
        }

        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montant);
        if (!$bareme) {
            return redirect()->back()->withInput()->with('error', 'Aucun barème pour ce montant.');
        }

        $frais = $bareme['frais_fixe'] + ($montant * $bareme['frais_pourcentage'] / 100);
        $montantTotal = $montant + $frais;

        if ($client['solde'] < $montantTotal) {
            return redirect()->back()->withInput()->with('error', 'Solde insuffisant.');
        }

        $data = [
            'reference'          => $this->transactionModel->generateReference(),
            'type_operation_id'  => $type['id'],
            'client_id'          => $clientId,
            'montant'            => $montant,
            'frais_appliques'    => $frais,
            'frais_inclus'       => 0,
            'montant_total'      => $montantTotal,
            'sens'               => 'debit',
            'statut'             => 'effectuee',
            'description'        => 'Retrait de ' . number_format($montant, 2) . ' Ar',
            'destinataire_original' => null,
            'est_inter_operateur' => 0,
        ];

        try {
            if ($this->transactionModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion retrait.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur retrait : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] - $montantTotal
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Retrait effectué !');
    }

    public function transfert()
    {
        $data['title'] = 'Faire un transfert multiple';
        return view('client/transfert', $data);
    }

    public function doTransfert()
    {
        $clientId = session()->get('client_id');
        $montantTotal = (float) $this->request->getPost('montant');
        $destinatairesRaw = $this->request->getPost('destinataires');
        $fraisInclus = (int) $this->request->getPost('frais_inclus') === 1;

        if (!$montantTotal || $montantTotal <= 0) {
            return redirect()->back()->withInput()->with('error', 'Montant total invalide.');
        }
        if (!$destinatairesRaw) {
            return redirect()->back()->withInput()->with('error', 'Veuillez entrer au moins un destinataire.');
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/client/login')->with('error', 'Client introuvable.');
        }

        $user = $this->userModel->find($client['user_id']);
        $estAdmin = ($user && $user['role'] === 'admin');
        if (!$estAdmin && substr($client['numero_telephone'], 0, 3) !== '032') {
            return redirect()->back()->withInput()->with('error', 'Seuls les numéros 032 peuvent effectuer des transferts.');
        }

        $destinataires = array_filter(array_map('trim', explode("\n", $destinatairesRaw)));
        if (empty($destinataires)) {
            return redirect()->back()->withInput()->with('error', 'Aucun destinataire valide.');
        }

        $destinatairesClients = [];
        $estInterOperateur = 0;

        foreach ($destinataires as $dest) {
            if ($dest === $client['numero_telephone']) {
                return redirect()->back()->withInput()->with('error', 'Vous ne pouvez pas vous transférer à vous-même.');
            }

            $prefixeDest = substr($dest, 0, 3);
            $prefixeInfo = $this->prefixeModel->where('prefixe', $prefixeDest)->first();
            if (!$prefixeInfo) {
                return redirect()->back()->withInput()->with('error', 'Préfixe invalide : ' . $prefixeDest);
            }

            if ($prefixeDest !== '032') {
                $estInterOperateur = 1;
            }

            $destClient = $this->clientModel->findByNumero($dest);
            if ($destClient) {
                $destinatairesClients[] = $destClient;
            } else {
                $destinatairesClients[] = [
                    'id' => null,
                    'numero_telephone' => $dest,
                    'exists' => false,
                    'solde' => 0,
                    'epargne_pourcentage' => 0,
                    'solde_epargne' => 0
                ];
            }
        }

        $nbDestinataires = count($destinatairesClients);
        $montantParDestinataire = $montantTotal / $nbDestinataires;

        if ($montantParDestinataire < 100) {
            return redirect()->back()->withInput()->with('error', 'Le montant par destinataire est inférieur à 100 Ar.');
        }

        $type = $this->typeModel->getTypeByCode('TRANS');
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type "transfert" introuvable.');
        }

        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montantParDestinataire);
        if (!$bareme) {
            return redirect()->back()->withInput()->with('error', 'Aucun barème trouvé pour ce montant.');
        }

        $fraisBase = $bareme['frais_fixe'] + ($montantParDestinataire * $bareme['frais_pourcentage'] / 100);

        foreach ($destinatairesClients as $index => $destData) {
            $isExisting = isset($destData['exists']) ? $destData['exists'] : true;
            $destNum = $isExisting ? $destData['numero_telephone'] : $destData['numero_telephone'];

            $prefixeDest = substr($destNum, 0, 3);
            $estInterOp = ($prefixeDest !== '032') ? 1 : 0;

            $fraisReel = $fraisBase;
            if ($estInterOp === 0) {
                $promotion = $this->promotionModel->getActivePromotion($type['id'], '032');
                if ($promotion) {
                    $reduction = (float) $promotion['reduction_pourcentage'];
                    $fraisReel = $fraisReel * (1 - ($reduction / 100));
                    $fraisReel = round($fraisReel, 2);
                }
            }

            $montantNetEnvoye = $fraisInclus ? $montantParDestinataire - $fraisReel : $montantParDestinataire;
            $montantDebiteFinal = $fraisInclus ? $montantParDestinataire : $montantParDestinataire + $fraisReel;

            $referenceExp = $this->transactionModel->generateReference();
            $this->transactionModel->insert([
                'reference' => $referenceExp,
                'type_operation_id' => $type['id'],
                'client_id' => $clientId,
                'montant' => $montantParDestinataire,
                'frais_appliques' => $fraisReel,
                'frais_inclus' => $fraisInclus ? 1 : 0,
                'montant_total' => $montantDebiteFinal,
                'sens' => 'debit',
                'statut' => 'effectuee',
                'description' => 'Transfert multiple ' . ($index + 1) . '/' . $nbDestinataires . ' vers ' . $destNum,
                'destinataire_original' => $destNum,
                'est_inter_operateur' => $estInterOp,
            ]);

            if ($isExisting) {
                $destClient = $this->clientModel->find($destData['id']);
                $epargnePctDest = $destClient['epargne_pourcentage'] ?? 0;
                $montantEpargneDest = $montantNetEnvoye * ($epargnePctDest / 100);
                $montantDisponibleDest = $montantNetEnvoye - $montantEpargneDest;

                $referenceDest = $this->transactionModel->generateReference();
                $this->transactionModel->insert([
                    'reference' => $referenceDest,
                    'type_operation_id' => $type['id'],
                    'client_id' => $destData['id'],
                    'montant' => $montantNetEnvoye,
                    'frais_appliques' => 0,
                    'frais_inclus' => 0,
                    'montant_total' => $montantNetEnvoye,
                    'sens' => 'credit',
                    'statut' => 'effectuee',
                    'description' => 'Réception de transfert multiple de ' . $client['numero_telephone'],
                    'destinataire_original' => null,
                    'est_inter_operateur' => 0,
                ]);

                $this->clientModel->update($destData['id'], [
                    'solde' => $destClient['solde'] + $montantDisponibleDest,
                    'solde_epargne' => $destClient['solde_epargne'] + $montantEpargneDest
                ]);
            }
        }

        $totalADebiter = $montantDebiteFinal * $nbDestinataires;
        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] - $totalADebiter
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Transfert multiple effectué avec succès ! ' . $nbDestinataires . ' destinataires servis. Montant total: ' . number_format($montantTotal, 2) . ' Ar');
    }

    public function historique()
    {
        $clientId = session()->get('client_id');
        $transactions = $this->transactionModel->getTransactionsByClient($clientId);
        $data['transactions'] = $transactions;
        $data['title'] = 'Mon historique';
        return view('client/historique', $data);
    }

    public function setEpargne()
    {
        $clientId = session()->get('client_id');
        $pourcentage = (float) $this->request->getPost('epargne_pourcentage');

        if ($pourcentage < 0 || $pourcentage > 100) {
            return redirect()->back()->with('error', 'Le pourcentage doit être compris entre 0 et 100.');
        }

        $this->clientModel->update($clientId, ['epargne_pourcentage' => $pourcentage]);

        return redirect()->to('/client/dashboard')->with('success', 'Pourcentage d\'épargne mis à jour.');
    }
}