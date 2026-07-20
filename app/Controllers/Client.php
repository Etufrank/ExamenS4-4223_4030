<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TransactionModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\UserModel;

class ClientController extends BaseController
{
    protected $clientModel;
    protected $transactionModel;
    protected $typeModel;
    protected $baremeModel;
    protected $userModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->transactionModel = new TransactionModel();
        $this->typeModel = new TypeOperationModel();
        $this->baremeModel = new BaremeFraisModel();
        $this->userModel = new UserModel();
    }

    // ==================== LOGIN ====================
    public function login()
    {
        $data['title'] = 'Connexion client';
        return view('client/login', $data);
    }

    public function doLogin()
    {
        $numero = $this->request->getPost('numero_telephone');

        if (!$numero) {
            return redirect()->back()->with('error', 'Veuillez entrer votre numéro de téléphone');
        }

        // Vérifier si le client existe
        $client = $this->clientModel->findByNumero($numero);

        if (!$client) {
            // Créer automatiquement un compte client
            $user = $this->userModel->insert([
                'username' => $numero,
                'password' => password_hash($numero, PASSWORD_DEFAULT),
                'role' => 'client',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $clientId = $this->clientModel->insert([
                'user_id' => $user,
                'numero_telephone' => $numero,
                'nom' => 'Client',
                'prenom' => 'Auto',
                'solde' => 0,
                'date_creation' => date('Y-m-d H:i:s'),
                'statut' => 'actif',
            ]);

            $client = $this->clientModel->find($clientId);
        }

        // Démarrer la session
        session()->set([
            'client_id' => $client['id'],
            'numero_telephone' => $client['numero_telephone'],
            'nom' => $client['nom'] . ' ' . $client['prenom'],
            'isLoggedIn' => true
        ]);

        return redirect()->to('/client/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/client/login');
    }

    // ==================== DASHBOARD / SOLDE ====================
    public function dashboard()
    {
        $clientId = session()->get('client_id');
        $client = $this->clientModel->find($clientId);

        if (!$client) {
            return redirect()->to('/client/login')->with('error', 'Veuillez vous reconnecter');
        }

        $data['client'] = $client;
        $data['title'] = 'Mon compte';
        return view('client/dashboard', $data);
    }

    // ==================== DÉPÔT ====================
    public function depot()
    {
        $data['title'] = 'Faire un dépôt';
        return view('client/depot', $data);
    }

    public function doDepot()
    {
        $clientId = session()->get('client_id');
        $montant = $this->request->getPost('montant');

        if (!$montant || $montant <= 0) {
            return redirect()->back()->with('error', 'Montant invalide');
        }

        // Vérifier le type "dépôt"
        $type = $this->typeModel->getTypeByCode('DEP');
        if (!$type) {
            return redirect()->back()->with('error', 'Type d\'opération "dépôt" introuvable');
        }

        // Calcul des frais (normalement 0 pour dépôt)
        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montant);
        $frais = $bareme ? ($bareme['frais_fixe'] + ($montant * $bareme['frais_pourcentage'] / 100)) : 0;

        // Montant total crédité
        $montantTotal = $montant - $frais;

        // Créer la transaction
        $reference = $this->transactionModel->generateReference();
        $this->transactionModel->save([
            'reference' => $reference,
            'type_operation_id' => $type['id'],
            'client_id' => $clientId,
            'montant' => $montant,
            'frais_appliques' => $frais,
            'montant_total' => $montantTotal,
            'sens' => 'credit',
            'statut' => 'effectuee',
            'description' => 'Dépôt automatique de ' . number_format($montant, 2) . ' Ar',
        ]);

        // Mettre à jour le solde du client
        $client = $this->clientModel->find($clientId);
        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] + $montantTotal
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Dépôt effectué avec succès ! Vous avez reçu ' . number_format($montantTotal, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)');
    }

    // ==================== RETRAIT ====================
    public function retrait()
    {
        $data['title'] = 'Faire un retrait';
        return view('client/retrait', $data);
    }

    public function doRetrait()
    {
        $clientId = session()->get('client_id');
        $montant = $this->request->getPost('montant');

        if (!$montant || $montant <= 0) {
            return redirect()->back()->with('error', 'Montant invalide');
        }

        // Vérifier le type "retrait"
        $type = $this->typeModel->getTypeByCode('RET');
        if (!$type) {
            return redirect()->back()->with('error', 'Type d\'opération "retrait" introuvable');
        }

        // Calcul des frais
        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montant);
        if (!$bareme) {
            return redirect()->back()->with('error', 'Aucun barème trouvé pour ce montant');
        }

        $frais = $bareme['frais_fixe'] + ($montant * $bareme['frais_pourcentage'] / 100);
        $montantTotal = $montant + $frais; // Le client paie le montant + les frais

        // Vérifier le solde
        $client = $this->clientModel->find($clientId);
        if ($client['solde'] < $montantTotal) {
            return redirect()->back()->with('error', 'Solde insuffisant. Solde: ' . number_format($client['solde'], 2) . ' Ar, Total à débiter: ' . number_format($montantTotal, 2) . ' Ar');
        }

        // Créer la transaction
        $reference = $this->transactionModel->generateReference();
        $this->transactionModel->save([
            'reference' => $reference,
            'type_operation_id' => $type['id'],
            'client_id' => $clientId,
            'montant' => $montant,
            'frais_appliques' => $frais,
            'montant_total' => $montantTotal,
            'sens' => 'debit',
            'statut' => 'effectuee',
            'description' => 'Retrait de ' . number_format($montant, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)',
        ]);

        // Mettre à jour le solde
        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] - $montantTotal
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Retrait effectué avec succès ! Montant: ' . number_format($montant, 2) . ' Ar, Frais: ' . number_format($frais, 2) . ' Ar, Total débité: ' . number_format($montantTotal, 2) . ' Ar');
    }

    // ==================== TRANSFERT ====================
    public function transfert()
    {
        $data['title'] = 'Faire un transfert';
        return view('client/transfert', $data);
    }

    public function doTransfert()
    {
        $clientId = session()->get('client_id');
        $montant = $this->request->getPost('montant');
        $destinataire = $this->request->getPost('destinataire');

        if (!$montant || $montant <= 0) {
            return redirect()->back()->with('error', 'Montant invalide');
        }

        if (!$destinataire) {
            return redirect()->back()->with('error', 'Numéro du destinataire requis');
        }

        // Vérifier que le destinataire n'est pas soi-même
        $client = $this->clientModel->find($clientId);
        if ($client['numero_telephone'] === $destinataire) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous transférer à vous-même');
        }

        // Vérifier le type "transfert"
        $type = $this->typeModel->getTypeByCode('TRANS');
        if (!$type) {
            return redirect()->back()->with('error', 'Type d\'opération "transfert" introuvable');
        }

        // Vérifier que le destinataire existe
        $destinataireClient = $this->clientModel->findByNumero($destinataire);
        if (!$destinataireClient) {
            return redirect()->back()->with('error', 'Destinataire introuvable');
        }

        // Calcul des frais
        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montant);
        if (!$bareme) {
            return redirect()->back()->with('error', 'Aucun barème trouvé pour ce montant');
        }

        $frais = $bareme['frais_fixe'] + ($montant * $bareme['frais_pourcentage'] / 100);
        $montantTotal = $montant + $frais; // L'expéditeur paie le montant + les frais

        // Vérifier le solde de l'expéditeur
        if ($client['solde'] < $montantTotal) {
            return redirect()->back()->with('error', 'Solde insuffisant. Solde: ' . number_format($client['solde'], 2) . ' Ar, Total à débiter: ' . number_format($montantTotal, 2) . ' Ar');
        }

        // Créer la transaction pour l'expéditeur (débit)
        $referenceExp = $this->transactionModel->generateReference();
        $this->transactionModel->save([
            'reference' => $referenceExp,
            'type_operation_id' => $type['id'],
            'client_id' => $clientId,
            'montant' => $montant,
            'frais_appliques' => $frais,
            'montant_total' => $montantTotal,
            'sens' => 'debit',
            'statut' => 'effectuee',
            'description' => 'Transfert à ' . $destinataire . ' - Montant: ' . number_format($montant, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)',
        ]);

        // Créer la transaction pour le destinataire (crédit)
        $referenceDest = $this->transactionModel->generateReference();
        $this->transactionModel->save([
            'reference' => $referenceDest,
            'type_operation_id' => $type['id'],
            'client_id' => $destinataireClient['id'],
            'montant' => $montant,
            'frais_appliques' => 0, // Le destinataire ne paie pas de frais
            'montant_total' => $montant,
            'sens' => 'credit',
            'statut' => 'effectuee',
            'description' => 'Réception de transfert de ' . $client['numero_telephone'] . ' - Montant: ' . number_format($montant, 2) . ' Ar',
        ]);

        // Mettre à jour les soldes
        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] - $montantTotal
        ]);

        $this->clientModel->update($destinataireClient['id'], [
            'solde' => $destinataireClient['solde'] + $montant
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Transfert effectué avec succès ! Montant: ' . number_format($montant, 2) . ' Ar, Frais: ' . number_format($frais, 2) . ' Ar, Total débité: ' . number_format($montantTotal, 2) . ' Ar');
    }

    // ==================== HISTORIQUE ====================
    public function historique()
    {
        $clientId = session()->get('client_id');
        $transactions = $this->transactionModel->getTransactionsByClient($clientId);

        $data['transactions'] = $transactions;
        $data['title'] = 'Mon historique';
        return view('client/historique', $data);
    }
}