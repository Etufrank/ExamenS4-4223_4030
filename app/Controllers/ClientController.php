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
        $data['title'] = 'Connexion client';
        return view('client/login', $data);
    }

    public function doLogin()
    {
        $numero = $this->request->getPost('numero_telephone');
        if (!$numero) {
            return redirect()->back()->withInput()->with('error', 'Veuillez entrer votre numéro de téléphone.');
        }

        $client = $this->clientModel->findByNumero($numero);

        if (!$client) {
            $user = $this->userModel->insert([
                'username'   => $numero,
                'password'   => password_hash($numero, PASSWORD_DEFAULT),
                'role'       => 'client',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $clientId = $this->clientModel->insert([
                'user_id'          => $user,
                'numero_telephone' => $numero,
                'nom'              => 'Client',
                'prenom'           => 'Auto',
                'solde'            => 0,
                'date_creation'    => date('Y-m-d H:i:s'),
                'statut'           => 'actif',
            ]);
            $client = $this->clientModel->find($clientId);
        }

        session()->set([
            'client_id'        => $client['id'],
            'numero_telephone' => $client['numero_telephone'],
            'nom'              => $client['nom'] . ' ' . $client['prenom'],
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
        $montant = $this->request->getPost('montant');

        if (!$montant || $montant <= 0) {
            return redirect()->back()->withInput()->with('error', 'Montant invalide.');
        }

        $type = $this->typeModel->getTypeByCode('DEP');
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type d\'opération "dépôt" introuvable.');
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
            'description'        => 'Dépôt automatique de ' . number_format($montant, 2) . ' Ar',
        ];

        try {
            if ($this->transactionModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion dépôt : ' . implode(', ', $this->transactionModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur insertion dépôt : ' . $e->getMessage());
            $lastQuery = $this->transactionModel->db->getLastQuery();
            if ($lastQuery) {
                log_message('error', 'Dernière requête : ' . $lastQuery);
            }
            return redirect()->back()->withInput()->with('error', 'Erreur technique. Voir les logs.');
        }

        $client = $this->clientModel->find($clientId);
        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] + $montantTotal
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Dépôt effectué avec succès ! Vous avez reçu ' . number_format($montantTotal, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)');
    }

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
            return redirect()->back()->withInput()->with('error', 'Montant invalide.');
        }

        $type = $this->typeModel->getTypeByCode('RET');
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type d\'opération "retrait" introuvable.');
        }

        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montant);
        if (!$bareme) {
            return redirect()->back()->withInput()->with('error', 'Aucun barème trouvé pour ce montant.');
        }

        $frais = $bareme['frais_fixe'] + ($montant * $bareme['frais_pourcentage'] / 100);
        $montantTotal = $montant + $frais;

        $client = $this->clientModel->find($clientId);
        if ($client['solde'] < $montantTotal) {
            return redirect()->back()->withInput()->with('error', 'Solde insuffisant. Solde: ' . number_format($client['solde'], 2) . ' Ar, Total à débiter: ' . number_format($montantTotal, 2) . ' Ar');
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
            'description'        => 'Retrait de ' . number_format($montant, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)',
        ];

        try {
            if ($this->transactionModel->insert($data) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion retrait : ' . implode(', ', $this->transactionModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur insertion retrait : ' . $e->getMessage());
            $lastQuery = $this->transactionModel->db->getLastQuery();
            if ($lastQuery) {
                log_message('error', 'Dernière requête : ' . $lastQuery);
            }
            return redirect()->back()->withInput()->with('error', 'Erreur technique. Voir les logs.');
        }

        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] - $montantTotal
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Retrait effectué avec succès ! Montant: ' . number_format($montant, 2) . ' Ar, Frais: ' . number_format($frais, 2) . ' Ar, Total débité: ' . number_format($montantTotal, 2) . ' Ar');
    }

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
            return redirect()->back()->withInput()->with('error', 'Montant invalide.');
        }
        if (!$destinataire) {
            return redirect()->back()->withInput()->with('error', 'Numéro du destinataire requis.');
        }

        $client = $this->clientModel->find($clientId);
        if ($client['numero_telephone'] === $destinataire) {
            return redirect()->back()->withInput()->with('error', 'Vous ne pouvez pas vous transférer à vous-même.');
        }

        $type = $this->typeModel->getTypeByCode('TRANS');
        if (!$type) {
            return redirect()->back()->withInput()->with('error', 'Type d\'opération "transfert" introuvable.');
        }

        $destinataireClient = $this->clientModel->findByNumero($destinataire);
        if (!$destinataireClient) {
            return redirect()->back()->withInput()->with('error', 'Destinataire introuvable.');
        }

        $bareme = $this->baremeModel->getBaremeByTypeAndMontant($type['id'], $montant);
        if (!$bareme) {
            return redirect()->back()->withInput()->with('error', 'Aucun barème trouvé pour ce montant.');
        }

        $frais = $bareme['frais_fixe'] + ($montant * $bareme['frais_pourcentage'] / 100);
        $montantTotal = $montant + $frais;

        if ($client['solde'] < $montantTotal) {
            return redirect()->back()->withInput()->with('error', 'Solde insuffisant. Solde: ' . number_format($client['solde'], 2) . ' Ar, Total à débiter: ' . number_format($montantTotal, 2) . ' Ar');
        }

        // Transaction pour l'expéditeur (débit)
        $dataExp = [
            'reference'          => $this->transactionModel->generateReference(),
            'type_operation_id'  => $type['id'],
            'client_id'          => $clientId,
            'montant'            => $montant,
            'frais_appliques'    => $frais,
            'montant_total'      => $montantTotal,
            'sens'               => 'debit',
            'statut'             => 'effectuee',
            'description'        => 'Transfert à ' . $destinataire . ' - Montant: ' . number_format($montant, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)',
        ];

        // Transaction pour le destinataire (crédit)
        $dataDest = [
            'reference'          => $this->transactionModel->generateReference(),
            'type_operation_id'  => $type['id'],
            'client_id'          => $destinataireClient['id'],
            'montant'            => $montant,
            'frais_appliques'    => 0,
            'montant_total'      => $montant,
            'sens'               => 'credit',
            'statut'             => 'effectuee',
            'description'        => 'Réception de transfert de ' . $client['numero_telephone'] . ' - Montant: ' . number_format($montant, 2) . ' Ar',
        ];

        try {
            if ($this->transactionModel->insert($dataExp) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion transfert (expéditeur) : ' . implode(', ', $this->transactionModel->errors()));
            }
            if ($this->transactionModel->insert($dataDest) === false) {
                return redirect()->back()->withInput()->with('error', 'Erreur insertion transfert (destinataire) : ' . implode(', ', $this->transactionModel->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erreur insertion transfert : ' . $e->getMessage());
            $lastQuery = $this->transactionModel->db->getLastQuery();
            if ($lastQuery) {
                log_message('error', 'Dernière requête : ' . $lastQuery);
            }
            return redirect()->back()->withInput()->with('error', 'Erreur technique. Voir les logs.');
        }

        
        $this->clientModel->update($clientId, [
            'solde' => $client['solde'] - $montantTotal
        ]);
        $this->clientModel->update($destinataireClient['id'], [
            'solde' => $destinataireClient['solde'] + $montant
        ]);

        return redirect()->to('/client/dashboard')->with('success', 'Transfert effectué avec succès ! Montant: ' . number_format($montant, 2) . ' Ar, Frais: ' . number_format($frais, 2) . ' Ar, Total débité: ' . number_format($montantTotal, 2) . ' Ar');
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