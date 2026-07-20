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

        // 2. ADMINISTRATEURS (avec numéros 032...)
        $admins = [
            [
                'username' => '0320408683',
                'password' => password_hash('admin032', PASSWORD_DEFAULT),
                'email'    => 'admin1@mobilemoney.com',
                'role'     => 'admin',
                'nom'      => 'Admin',
                'prenom'   => 'Principal',
                'tel'      => '0320408683',
            ],
            [
                'username' => '0320000001',
                'password' => password_hash('admin032', PASSWORD_DEFAULT),
                'email'    => 'admin2@mobilemoney.com',
                'role'     => 'admin',
                'nom'      => 'Admin',
                'prenom'   => 'Second',
                'tel'      => '0320000001',
            ],
        ];

        foreach ($admins as $a) {
            $this->db->table('users')->insert([
                'username'   => $a['username'],
                'password'   => $a['password'],
                'email'      => $a['email'],
                'role'       => $a['role'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $userId = $this->db->insertID();
            $this->db->table('clients')->insert([
                'user_id'          => $userId,
                'numero_telephone' => $a['tel'],
                'nom'              => $a['nom'],
                'prenom'           => $a['prenom'],
                'solde'            => 0,
                'date_creation'    => date('Y-m-d H:i:s'),
                'statut'           => 'actif',
            ]);
        }

        // 3. CLIENTS DE TEST (UNIQUEMENT 032)
        $clientsData = [
            ['0321234567', 'Jean', 'Dupont', 50000],
            ['0322345678', 'Marie', 'Martin', 30000],
            ['0323456789', 'Paul', 'Dubois', 75000],
            ['0324567890', 'Sophie', 'Lefevre', 120000],
            ['0325678901', 'Lucas', 'Moreau', 20000],
            ['0326789012', 'Emma', 'Petit', 60000],
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

        // 4. PRÉFIXES OPÉRATEUR (CORRECTION : 032 = réseau principal)
        $prefixes = [
            ['prefixe' => '032', 'description' => 'Réseau principal', 'est_autre_operateur' => 0, 'commission_pourcentage' => 0],
            ['prefixe' => '031', 'description' => 'Autre opérateur (Orange)', 'est_autre_operateur' => 1, 'commission_pourcentage' => 3.00],
            ['prefixe' => '033', 'description' => 'Autre opérateur (A)', 'est_autre_operateur' => 1, 'commission_pourcentage' => 2.50],
            ['prefixe' => '034', 'description' => 'Autre opérateur (B)', 'est_autre_operateur' => 1, 'commission_pourcentage' => 2.50],
            ['prefixe' => '037', 'description' => 'Autre opérateur (C)', 'est_autre_operateur' => 1, 'commission_pourcentage' => 2.50],
            ['prefixe' => '038', 'description' => 'Autre opérateur (D)', 'est_autre_operateur' => 1, 'commission_pourcentage' => 2.50],
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

        // Récupérer les IDs des types
        $depotId     = $this->db->table('types_operations')->where('code', 'DEP')->get()->getRow()->id;
        $retraitId   = $this->db->table('types_operations')->where('code', 'RET')->get()->getRow()->id;
        $transfertId = $this->db->table('types_operations')->where('code', 'TRANS')->get()->getRow()->id;

        // 6. BARÈMES DE FRAIS (inchangés)
        $baremes = [
            ['type' => 'RET', 'min' => 100, 'max' => 1000, 'frais' => 50],
            ['type' => 'RET', 'min' => 1001, 'max' => 5000, 'frais' => 50],
            ['type' => 'RET', 'min' => 5001, 'max' => 10000, 'frais' => 100],
            ['type' => 'RET', 'min' => 10001, 'max' => 25000, 'frais' => 200],
            ['type' => 'RET', 'min' => 25001, 'max' => 50000, 'frais' => 400],
            ['type' => 'RET', 'min' => 50001, 'max' => 100000, 'frais' => 800],
            ['type' => 'RET', 'min' => 100001, 'max' => 250000, 'frais' => 1500],
            ['type' => 'RET', 'min' => 250001, 'max' => 500000, 'frais' => 1500],
            ['type' => 'RET', 'min' => 500001, 'max' => 1000000, 'frais' => 2500],
            ['type' => 'RET', 'min' => 1000001, 'max' => 2000000, 'frais' => 3000],
        ];

        foreach ($baremes as $b) {
            $typeId = ($b['type'] === 'RET') ? $retraitId : $transfertId;
            $this->db->table('baremes_frais')->insert([
                'type_operation_id' => $typeId,
                'montant_min'       => $b['min'],
                'montant_max'       => $b['max'],
                'frais_fixe'        => $b['frais'],
                'frais_pourcentage' => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        // Barème pour le dépôt (frais = 0)
        $this->db->table('baremes_frais')->insert([
            'type_operation_id' => $depotId,
            'montant_min'       => 0,
            'montant_max'       => 999999999,
            'frais_fixe'        => 0,
            'frais_pourcentage' => 0,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        // 7. CLIENT INTER-OPÉRATEUR (pour tester les transferts vers d'autres réseaux)
        // Utilisons le préfixe 031 (autre opérateur)
        $interNum = '0311234567';
        $this->db->table('users')->insert([
            'username'   => $interNum,
            'password'   => password_hash('1234', PASSWORD_DEFAULT),
            'email'      => 'inter@test.com',
            'role'       => 'client',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $interUserId = $this->db->insertID();
        $this->db->table('clients')->insert([
            'user_id'          => $interUserId,
            'numero_telephone' => $interNum,
            'nom'              => 'Inter',
            'prenom'           => 'Opérateur',
            'solde'            => 10000,
            'date_creation'    => date('Y-m-d H:i:s'),
            'statut'           => 'actif',
        ]);
        $interClientId = $this->db->insertID();

        // 8. TRANSACTIONS DE TEST (pour chaque client 032, on crée dépôt, retrait, transfert)
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
                'date_transaction'   => date('Y-m-d H:i:s', strtotime('-'.($index+1).' days')),
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
                    'date_transaction'   => date('Y-m-d H:i:s', strtotime('-'.($index).' days')),
                    'description'        => 'Retrait test de ' . number_format($montant, 2) . ' Ar',
                    'est_inter_operateur' => 0,
                    'destinataire_original' => null,
                ]);
            }

            // Transfert inter-opérateur (seulement pour le premier client)
            if ($index === 0) {
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
                        'description'        => 'Transfert inter-opérateur vers ' . $interNum,
                        'est_inter_operateur' => 1,
                        'destinataire_original' => $interNum,
                    ]);
                    // Destinataire
                    $this->db->table('transactions')->insert([
                        'reference'          => 'TXN-' . date('Ymd') . '-' . uniqid(),
                        'type_operation_id'  => $transfertId,
                        'client_id'          => $interClientId,
                        'montant'            => $montant,
                        'frais_appliques'    => 0,
                        'frais_inclus'       => 0,
                        'montant_total'      => $montant,
                        'sens'               => 'credit',
                        'statut'             => 'effectuee',
                        'date_transaction'   => date('Y-m-d H:i:s'),
                        'description'        => 'Réception de transfert inter-opérateur de ' . $clientsData[0][0],
                        'est_inter_operateur' => 0,
                        'destinataire_original' => null,
                    ]);
                }
            }
        }

        // 9. GAINS (SITUATION DES FRAIS)
        $periodeDebut = date('Y-m-d 00:00:00', strtotime('first day of this month'));
        $periodeFin   = date('Y-m-d 23:59:59', strtotime('last day of this month'));

        // Gains pour retrait (réseau principal)
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

        // Gains pour transfert (réseau principal)
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