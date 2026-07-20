<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TransactionModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\UserModel;
use App\Models\PrefixeOperateurModel;

class ClientController extends BaseController
{
    protected $clientModel;
    protected $transactionModel;
    protected $typeModel;
    protected $baremeModel;
    protected $userModel;
    protected $prefixeModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->transactionModel = new TransactionModel();
        $this->typeModel = new TypeOperationModel();
        $this->baremeModel = new BaremeFraisModel();
        $this->userModel = new UserModel();
        $this->prefixeModel = new PrefixeOperateurModel();
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

        $client = $this->clientModel->findByNumero($numero);
        if (!$client) {
            return redirect()->back()->withInput()->with('error', 'Numéro inconnu.');
        }

        $user = $this->userModel->find($client['user_id']);
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
        $data['title'] = 'Mon compte';
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

        $data = [
            'reference'          => $this->transactionModel->generateReference(),
            'type_operation_id'  => $type['id'],
            'client_id'          => $clientId,
            'montant'            => $montant,
            'frais_appliques'    => $frais,
            'montant_total'      => $montantTotal,
            'sens'               => 'credit',
            'statut'             => 'effectuee',
            'description'        => 'Dépôt de ' . number_format($montant, 2) . ' Ar',
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
            'solde' => $client['solde'] + $montantTotal
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Dépôt effectué ! Vous avez reçu ' . number_format($montantTotal, 2) . ' Ar');
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
            'montant_total'      => $montantTotal,
            'sens'               => 'debit',
            'statut'             => 'effectuee',
            'description'        => 'Retrait de ' . number_format($montant, 2) . ' Ar',
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

        $destinataires = array_filter(array_map('trim', explode("\n", $destinatairesRaw)));
        if (empty($destinataires)) {
            return redirect()->back()->withInput()->with('error', 'Aucun destinataire valide.');
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/client/login')->with('error', 'Client introuvable.');
        }

        $destinatairesClients = [];
        foreach ($destinataires as $dest) {
            if ($dest === $client['numero_telephone']) {
                return redirect()->back()->withInput()->with('error', 'Vous ne pouvez pas vous transférer à vous-même.');
            }
            $destClient = $this->clientModel->findByNumero($dest);
            if (!$destClient) {
                return redirect()->back()->withInput()->with('error', 'Destinataire introuvable : ' . $dest);
            }
            $destinatairesClients[] = $destClient;
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

        $premierPrefixe = substr($destinatairesClients[0]['numero_telephone'], 0, 3);
        $prefixeInfo = $this->prefixeModel->where('prefixe', $premierPrefixe)->first();
        $estInterOperateur = ($prefixeInfo && $prefixeInfo['est_autre_operateur'] == 1) ? 1 : 0;

        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montantParDestinataire);
        if (!$bareme) {
            return redirect()->back()->withInput()->with('error', 'Aucun barème trouvé pour ce montant.');
        }

        $fraisParDestinataire = $bareme['frais_fixe'] + ($montantParDestinataire * $bareme['frais_pourcentage'] / 100);
        $montantEnvoye = $fraisInclus ? $montantParDestinataire - $fraisParDestinataire : $montantParDestinataire;
        $montantDebite = $fraisInclus ? $montantParDestinataire : $montantParDestinataire + $fraisParDestinataire;

        $totalADebiter = $montantDebite * $nbDestinataires;
        if ($client['solde'] < $totalADebiter) {
            return redirect()->back()->withInput()->with('error', 'Solde insuffisant. Solde: ' . number_format($client['solde'], 2) . ' Ar, Total à débiter: ' . number_format($totalADebiter, 2) . ' Ar');
        }

        foreach ($destinatairesClients as $index => $destClient) {
            $referenceExp = $this->transactionModel->generateReference();
            $this->transactionModel->insert([
                'reference' => $referenceExp,
                'type_operation_id' => $type['id'],
                'client_id' => $clientId,
                'montant' => $montantParDestinataire,
                'frais_appliques' => $fraisParDestinataire,
                'frais_inclus' => $fraisInclus ? 1 : 0,
                'montant_total' => $montantDebite,
                'sens' => 'debit',
                'statut' => 'effectuee',
                'description' => 'Transfert multiple ' . ($index + 1) . '/' . $nbDestinataires . ' vers ' . $destClient['numero_telephone'],
                'destinataire_original' => $destClient['numero_telephone'],
                'est_inter_operateur' => $estInterOperateur,
            ]);

            $referenceDest = $this->transactionModel->generateReference();
            $this->transactionModel->insert([
                'reference' => $referenceDest,
                'type_operation_id' => $type['id'],
                'client_id' => $destClient['id'],
                'montant' => $montantEnvoye,
                'frais_appliques' => 0,
                'frais_inclus' => 0,
                'montant_total' => $montantEnvoye,
                'sens' => 'credit',
                'statut' => 'effectuee',
                'description' => 'Réception de transfert multiple de ' . $client['numero_telephone'],
                'est_inter_operateur' => 0,
            ]);

            $this->clientModel->update($destClient['id'], [
                'solde' => $destClient['solde'] + $montantEnvoye
            ]);
        }

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
}