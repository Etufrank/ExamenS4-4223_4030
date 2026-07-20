<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. VIDER LES TABLES (ordre inversé des clés étrangères)
        $this->db->table('transactions')->truncate();
        $this->db->table('gains')->truncate();
        $this->db->table('baremes_frais')->truncate();
        $this->db->table('types_operations')->truncate();
        $this->db->table('clients')->truncate();
        $this->db->table('users')->truncate();
        $this->db->table('prefixes_operateur')->truncate();
        $this->db->table('envois_multiples')->truncate();

        // 2. ADMINISTRATEURS
        $admins = [
            [
                'username' => 'frank',
                'password' => password_hash('frank123', PASSWORD_DEFAULT),
                'email'    => 'frank@gmail.com',
                'role'     => 'admin',
            ],
            [
                'username' => 'tahiry',
                'password' => password_hash('tahiry123', PASSWORD_DEFAULT),
                'email'    => 'tahiry@gmail.com',
                'role'     => 'admin',
            ],
        ];

        foreach ($admins as $a) {
            $this->db->table('users')->insert($a);
            $userId = $this->db->insertID();
            $this->db->table('clients')->insert([
                'user_id'          => $userId,
                'numero_telephone' => $a['username'] === 'frank' ? '0330000001' : '0330000002',
                'nom'              => $a['username'] === 'frank' ? 'Frank' : 'Tahiry',
                'prenom'           => 'Admin',
                'solde'            => 0,
                'date_creation'    => date('Y-m-d H:i:s'),
                'statut'           => 'actif',
            ]);
        }

        // 3. CLIENTS DE TEST
        $clientsData = [
            ['0331234567', 'Jean', 'Dupont', 50000],
            ['0349876543', 'Marie', 'Martin', 30000],
            ['0371122334', 'Paul', 'Dubois', 75000],
            ['0385566778', 'Sophie', 'Lefevre', 120000],
        ];

        $clientIds = [];
        foreach ($clientsData as $c) {
            $this->db->table('users')->insert([
                'username'   => $c[0],
                'password'   => password_hash('1234', PASSWORD_DEFAULT),
                'email'      => $c[0] . '@test.com',
                'role'       => 'client',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $userId = $this->db->insertID();
            $this->db->table('clients')->insert([
                'user_id'          => $userId,
                'numero_telephone' => $c[0],
                'nom'              => $c[1],
                'prenom'           => $c[2],
                'solde'            => $c[3],
                'date_creation'    => date('Y-m-d H:i:s'),
                'statut'           => 'actif',
            ]);
            $clientIds[] = $this->db->insertID();
        }

        // 4. PRÉFIXES OPÉRATEUR (Version 2 : avec autres opérateurs et commissions)
        $prefixes = [
            ['prefixe' => '033', 'description' => 'Opérateur A (même réseau)', 'est_autre_operateur' => 0, 'commission_pourcentage' => 0],
            ['prefixe' => '034', 'description' => 'Opérateur B (même réseau)', 'est_autre_operateur' => 0, 'commission_pourcentage' => 0],
            ['prefixe' => '037', 'description' => 'Opérateur C (même réseau)', 'est_autre_operateur' => 0, 'commission_pourcentage' => 0],
            ['prefixe' => '038', 'description' => 'Opérateur D (même réseau)', 'est_autre_operateur' => 0, 'commission_pourcentage' => 0],
            ['prefixe' => '032', 'description' => 'Autre opérateur (Telma)', 'est_autre_operateur' => 1, 'commission_pourcentage' => 2.50],
            ['prefixe' => '031', 'description' => 'Autre opérateur (Orange)', 'est_autre_operateur' => 1, 'commission_pourcentage' => 3.00],
        ];

        foreach ($prefixes as $p) {
            $this->db->table('prefixes_operateur')->insert($p);
        }

        // 5. TYPES D'OPÉRATIONS
        $types = [
            ['nom' => 'dépôt', 'code' => 'DEP', 'description' => 'Dépôt sur compte'],
            ['nom' => 'retrait', 'code' => 'RET', 'description' => 'Retrait depuis compte'],
            ['nom' => 'transfert', 'code' => 'TRANS', 'description' => 'Transfert entre comptes'],
        ];
        foreach ($types as $t) {
            $this->db->table('types_operations')->insert($t);
        }

        // 6. RÉCUPÉRER LES IDs DES TYPES
        $depotId     = $this->db->table('types_operations')->where('code', 'DEP')->get()->getRow()->id;
        $retraitId   = $this->db->table('types_operations')->where('code', 'RET')->get()->getRow()->id;
        $transfertId = $this->db->table('types_operations')->where('code', 'TRANS')->get()->getRow()->id;

        // 7. BARÈMES DE FRAIS (pour retrait et transfert)
        $baremesRetrait = [
            [100, 1000, 50],
            [1001, 5000, 50],
            [5001, 10000, 100],
            [10001, 25000, 200],
            [25001, 50000, 400],
            [50001, 100000, 800],
            [100001, 250000, 1500],
            [250001, 500000, 1500],
            [500001, 1000000, 2500],
            [1000001, 2000000, 3000],
        ];

        // Pour retrait
        foreach ($baremesRetrait as $b) {
            $this->db->table('baremes_frais')->insert([
                'type_operation_id' => $retraitId,
                'montant_min'       => $b[0],
                'montant_max'       => $b[1],
                'frais_fixe'        => $b[2],
                'frais_pourcentage' => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        // Pour transfert (mêmes valeurs)
        foreach ($baremesRetrait as $b) {
            $this->db->table('baremes_frais')->insert([
                'type_operation_id' => $transfertId,
                'montant_min'       => $b[0],
                'montant_max'       => $b[1],
                'frais_fixe'        => $b[2],
                'frais_pourcentage' => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        // Dépôt : frais à 0 sur toute la plage
        $this->db->table('baremes_frais')->insert([
            'type_operation_id' => $depotId,
            'montant_min'       => 0,
            'montant_max'       => 999999999,
            'frais_fixe'        => 0,
            'frais_pourcentage' => 0,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        // 8. TRANSACTIONS DE TEST (incluant des transferts inter-opérateurs)
        // Pour chaque client, on crée : un dépôt, un retrait, un transfert vers un autre opérateur
        foreach ($clientIds as $index => $cid) {
            // Dépôt
            $montant = rand(1000, 20000);
            $this->db->table('transactions')->insert([
                'reference'          => 'TXN-' . date('Ymd') . '-' . uniqid(),
                'type_operation_id'  => $depotId,
                'client_id'          => $cid,
                'montant'            => $montant,
                'frais_appliques'    => 0,
                'frais_inclus'       => 0,
                'montant_total'      => $montant,
                'sens'               => 'credit',
                'statut'             => 'effectuee',
                'date_transaction'   => date('Y-m-d H:i:s', strtotime('-2 days')),
                'description'        => 'Dépôt test de ' . number_format($montant, 2) . ' Ar',
                'est_inter_operateur' => 0,
                'destinataire_original' => null,
            ]);

            // Retrait
            $montant = rand(5000, 30000);
            $bareme = $this->getBareme($retraitId, $montant);
            if ($bareme) {
                $frais = $bareme['frais_fixe'];
                $total = $montant + $frais;
                $this->db->table('transactions')->insert([
                    'reference'          => 'TXN-' . date('Ymd') . '-' . uniqid(),
                    'type_operation_id'  => $retraitId,
                    'client_id'          => $cid,
                    'montant'            => $montant,
                    'frais_appliques'    => $frais,
                    'frais_inclus'       => 0,
                    'montant_total'      => $total,
                    'sens'               => 'debit',
                    'statut'             => 'effectuee',
                    'date_transaction'   => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'description'        => 'Retrait test de ' . number_format($montant, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)',
                    'est_inter_operateur' => 0,
                    'destinataire_original' => null,
                ]);
            }

            // Transfert vers un autre opérateur (pour le premier client on le fait vers un autre)
            if ($index === 0) {
                // Transfert inter-opérateur (vers un numéro avec préfixe 032)
                $destNum = '0327654321';
                $destClient = $this->db->table('clients')->where('numero_telephone', $destNum)->get()->getRow();
                if (!$destClient) {
                    // Créer un destinataire inter-opérateur s'il n'existe pas
                    $this->db->table('users')->insert([
                        'username'   => $destNum,
                        'password'   => password_hash('1234', PASSWORD_DEFAULT),
                        'email'      => 'inter@test.com',
                        'role'       => 'client',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $destUserId = $this->db->insertID();
                    $this->db->table('clients')->insert([
                        'user_id'          => $destUserId,
                        'numero_telephone' => $destNum,
                        'nom'              => 'Inter',
                        'prenom'           => 'Opérateur',
                        'solde'            => 10000,
                        'date_creation'    => date('Y-m-d H:i:s'),
                        'statut'           => 'actif',
                    ]);
                    $destClientId = $this->db->insertID();
                } else {
                    $destClientId = $destClient->id;
                }

                $montant = rand(2000, 10000);
                $bareme = $this->getBareme($transfertId, $montant);
                if ($bareme) {
                    $frais = $bareme['frais_fixe'];
                    $total = $montant + $frais;
                    // Expéditeur
                    $this->db->table('transactions')->insert([
                        'reference'          => 'TXN-' . date('Ymd') . '-' . uniqid(),
                        'type_operation_id'  => $transfertId,
                        'client_id'          => $cid,
                        'montant'            => $montant,
                        'frais_appliques'    => $frais,
                        'frais_inclus'       => 0,
                        'montant_total'      => $total,
                        'sens'               => 'debit',
                        'statut'             => 'effectuee',
                        'date_transaction'   => date('Y-m-d H:i:s'),
                        'description'        => 'Transfert inter-opérateur vers ' . $destNum . ' (frais: ' . number_format($frais, 2) . ' Ar)',
                        'est_inter_operateur' => 1,
                        'destinataire_original' => $destNum,
                    ]);
                    // Destinataire
                    $this->db->table('transactions')->insert([
                        'reference'          => 'TXN-' . date('Ymd') . '-' . uniqid(),
                        'type_operation_id'  => $transfertId,
                        'client_id'          => $destClientId,
                        'montant'            => $montant,
                        'frais_appliques'    => 0,
                        'frais_inclus'       => 0,
                        'montant_total'      => $montant,
                        'sens'               => 'credit',
                        'statut'             => 'effectuee',
                        'date_transaction'   => date('Y-m-d H:i:s'),
                        'description'        => 'Réception de transfert inter-opérateur de ' . $clientIds[0],
                        'est_inter_operateur' => 0,
                        'destinataire_original' => null,
                    ]);
                }
            }
        }

        // 9. GAINS (SITUATION DES FRAIS)
        $periodeDebut = date('Y-m-d 00:00:00', strtotime('first day of this month'));
        $periodeFin   = date('Y-m-d 23:59:59', strtotime('last day of this month'));

        // Gains pour retrait (opérateur)
        $fraisRetraitOperateur = $this->db->table('transactions')
            ->select('SUM(frais_appliques) as total')
            ->where('type_operation_id', $retraitId)
            ->where('statut', 'effectuee')
            ->where('est_inter_operateur', 0)
            ->get()->getRowArray()['total'] ?? 0;

        if ($fraisRetraitOperateur > 0) {
            $this->db->table('gains')->insert([
                'type_operation_id'   => $retraitId,
                'montant_total_frais' => $fraisRetraitOperateur,
                'periode_debut'       => $periodeDebut,
                'periode_fin'         => $periodeFin,
                'est_inter_operateur' => 0,
                'created_at'          => date('Y-m-d H:i:s'),
            ]);
        }

        // Gains pour retrait (inter-opérateur)
        $fraisRetraitInter = $this->db->table('transactions')
            ->select('SUM(frais_appliques) as total')
            ->where('type_operation_id', $retraitId)
            ->where('statut', 'effectuee')
            ->where('est_inter_operateur', 1)
            ->get()->getRowArray()['total'] ?? 0;

        if ($fraisRetraitInter > 0) {
            $this->db->table('gains')->insert([
                'type_operation_id'   => $retraitId,
                'montant_total_frais' => $fraisRetraitInter,
                'periode_debut'       => $periodeDebut,
                'periode_fin'         => $periodeFin,
                'est_inter_operateur' => 1,
                'created_at'          => date('Y-m-d H:i:s'),
            ]);
        }

        // Gains pour transfert (opérateur)
        $fraisTransfertOperateur = $this->db->table('transactions')
            ->select('SUM(frais_appliques) as total')
            ->where('type_operation_id', $transfertId)
            ->where('statut', 'effectuee')
            ->where('est_inter_operateur', 0)
            ->get()->getRowArray()['total'] ?? 0;

        if ($fraisTransfertOperateur > 0) {
            $this->db->table('gains')->insert([
                'type_operation_id'   => $transfertId,
                'montant_total_frais' => $fraisTransfertOperateur,
                'periode_debut'       => $periodeDebut,
                'periode_fin'         => $periodeFin,
                'est_inter_operateur' => 0,
                'created_at'          => date('Y-m-d H:i:s'),
            ]);
        }

        // Gains pour transfert (inter-opérateur)
        $fraisTransfertInter = $this->db->table('transactions')
            ->select('SUM(frais_appliques) as total')
            ->where('type_operation_id', $transfertId)
            ->where('statut', 'effectuee')
            ->where('est_inter_operateur', 1)
            ->get()->getRowArray()['total'] ?? 0;

        if ($fraisTransfertInter > 0) {
            $this->db->table('gains')->insert([
                'type_operation_id'   => $transfertId,
                'montant_total_frais' => $fraisTransfertInter,
                'periode_debut'       => $periodeDebut,
                'periode_fin'         => $periodeFin,
                'est_inter_operateur' => 1,
                'created_at'          => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function getBareme($typeId, $montant)
    {
        return $this->db->table('baremes_frais')
            ->where('type_operation_id', $typeId)
            ->where('montant_min <=', $montant)
            ->where('montant_max >=', $montant)
            ->get()
            ->getRowArray();
    }
}