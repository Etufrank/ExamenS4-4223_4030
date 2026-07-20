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

    public function login()
{
    // Si déjà connecté, rediriger vers dashboard
    if (session()->get('isLoggedIn')) {
        return redirect()->to('/client/dashboard');
    }

    // Éviter une boucle si on est déjà sur login (sécurité)
    $currentUri = current_url();
    if (strpos($currentUri, 'client/login') !== false) {
        // On affiche simplement la vue
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
        $data['title'] = 'Faire un transfert';
        return view('client/transfert', $data);
    }

    public function doTransfert()
    {
        $clientId = session()->get('client_id');
        $montant = (float) $this->request->getPost('montant');
        $destinataire = $this->request->getPost('destinataire');

        if (!$montant || $montant <= 0) {
            return redirect()->back()->withInput()->with('error', 'Montant invalide.');
        }
        if (!$destinataire) {
            return redirect()->back()->withInput()->with('error', 'Destinataire requis.');
        }

        $client = $this->clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/client/login')->with('error', 'Client introuvable.');
        }
        if ($client['numero_telephone'] === $destinataire) {
            return redirect()->back()->withInput()->with('error', 'Transfert vers soi-même interdit.');
        }

        $type = $this->typeModel->getTypeByCode('TRANS');
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type "transfert" introuvable.');
        }

        $destinataireClient = $this->clientModel->findByNumero($destinataire);
        if (!$destinataireClient) {
            return redirect()->back()->withInput()->with('error', 'Destinataire introuvable.');
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

        $dataExp = [
            'reference'          => $this->transactionModel->generateReference(),
            'type_operation_id'  => $type['id'],
            'client_id'          => $clientId,
            'montant'            => $montant,
            'frais_appliques'    => $frais,
            'montant_total'      => $montantTotal,
            'sens'               => 'debit',
            'statut'             => 'effectuee',
            'description'        => 'Transfert à ' . $destinataire,
        ];

        $dataDest = [
            'reference'          => $this->transactionModel->generateReference(),
            'type_operation_id'  => $type['id'],
            'client_id'          => $destinataireClient['id'],
            'montant'            => $montant,
            'frais_appliques'    => 0,
            'montant_total'      => $montant,
            'sens'               => 'credit',
            'statut'             => 'effectuee',
            'description'        => 'Réception de transfert de ' . $client['numero_telephone'],
        ];

        try {
            if ($this->transactionModel->insert($dataExp) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur transfert (expéditeur).');
            }
            if ($this->transactionModel->insert($dataDest) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur transfert (destinataire).');
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur transfert : ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erreur technique.');
        }

        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] - $montantTotal
        ]);
        $this->clientModel->update($destinataireClient['id'], [
            'solde' => $destinataireClient['solde'] + $montant
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Transfert effectué !');
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