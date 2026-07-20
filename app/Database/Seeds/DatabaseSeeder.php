<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->db->table('transactions')->truncate();
        $this->db->table('gains')->truncate();
        $this->db->table('baremes_frais')->truncate();
        $this->db->table('types_operations')->truncate();
        $this->db->table('clients')->truncate();
        $this->db->table('users')->truncate();

        $admins = [
            ['username' => 'frank', 'password' => password_hash('frank123', PASSWORD_DEFAULT), 'email' => 'frank@gmail.com', 'role' => 'admin'],
            ['username' => 'tahiry', 'password' => password_hash('tahiry123', PASSWORD_DEFAULT), 'email' => 'tahiry@gmail.com', 'role' => 'admin'],
        ];
        foreach ($admins as $a) {
            $this->db->table('users')->insert($a);
            $userId = $this->db->insertID();
            $this->db->table('clients')->insert([
                'user_id' => $userId,
                'numero_telephone' => $a['username'] === 'frank' ? '0330000001' : '0330000002',
                'nom' => $a['username'] === 'frank' ? 'Frank' : 'Tahiry',
                'prenom' => 'Admin',
                'solde' => 0,
                'date_creation' => date('Y-m-d H:i:s'),
                'statut' => 'actif',
            ]);
        }

        $clients = [
            ['0331234567', 'Jean', 'Dupont', 50000],
            ['0349876543', 'Marie', 'Martin', 30000],
            ['0371122334', 'Paul', 'Dubois', 75000],
            ['0385566778', 'Sophie', 'Lefevre', 120000],
        ];
        $clientIds = [];
        foreach ($clients as $c) {
            $this->db->table('users')->insert([
                'username' => $c[0],
                'password' => password_hash('1234', PASSWORD_DEFAULT),
                'email' => $c[0] . '@test.com',
                'role' => 'client',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $userId = $this->db->insertID();
            $this->db->table('clients')->insert([
                'user_id' => $userId,
                'numero_telephone' => $c[0],
                'nom' => $c[1],
                'prenom' => $c[2],
                'solde' => $c[3],
                'date_creation' => date('Y-m-d H:i:s'),
                'statut' => 'actif',
            ]);
            $clientIds[] = $this->db->insertID();
        }

        $types = [
            ['nom' => 'dépôt', 'code' => 'DEP', 'description' => 'Dépôt sur compte'],
            ['nom' => 'retrait', 'code' => 'RET', 'description' => 'Retrait depuis compte'],
            ['nom' => 'transfert', 'code' => 'TRANS', 'description' => 'Transfert entre comptes'],
        ];
        foreach ($types as $t) {
            $this->db->table('types_operations')->insert($t);
        }

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
        foreach ($baremesRetrait as $b) {
            $this->db->table('baremes_frais')->insert([
                'type_operation_id' => 2,
                'montant_min' => $b[0],
                'montant_max' => $b[1],
                'frais_fixe' => $b[2],
                'frais_pourcentage' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        foreach ($baremesRetrait as $b) {
            $this->db->table('baremes_frais')->insert([
                'type_operation_id' => 3,
                'montant_min' => $b[0],
                'montant_max' => $b[1],
                'frais_fixe' => $b[2],
                'frais_pourcentage' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $this->db->table('baremes_frais')->insert([
            'type_operation_id' => 1,
            'montant_min' => 0,
            'montant_max' => 999999999,
            'frais_fixe' => 0,
            'frais_pourcentage' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        foreach ($clientIds as $cid) {
            $montant = rand(1000, 20000);
            $this->db->table('transactions')->insert([
                'reference' => 'TXN-' . date('Ymd') . '-' . uniqid(),
                'type_operation_id' => 1,
                'client_id' => $cid,
                'montant' => $montant,
                'frais_appliques' => 0,
                'montant_total' => $montant,
                'sens' => 'credit',
                'statut' => 'effectuee',
                'date_transaction' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'description' => 'Dépôt test de ' . number_format($montant, 2) . ' Ar',
            ]);
            $montant = rand(5000, 30000);
            $bareme = $this->getBareme(2, $montant);
            if ($bareme) {
                $frais = $bareme['frais_fixe'];
                $total = $montant + $frais;
                $this->db->table('transactions')->insert([
                    'reference' => 'TXN-' . date('Ymd') . '-' . uniqid(),
                    'type_operation_id' => 2,
                    'client_id' => $cid,
                    'montant' => $montant,
                    'frais_appliques' => $frais,
                    'montant_total' => $total,
                    'sens' => 'debit',
                    'statut' => 'effectuee',
                    'date_transaction' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'description' => 'Retrait test de ' . number_format($montant, 2) . ' Ar (frais: ' . number_format($frais, 2) . ' Ar)',
                ]);
            }
            if ($cid !== $clientIds[0]) {
                $montant = rand(2000, 15000);
                $bareme = $this->getBareme(3, $montant);
                if ($bareme) {
                    $frais = $bareme['frais_fixe'];
                    $total = $montant + $frais;
                    $this->db->table('transactions')->insert([
                        'reference' => 'TXN-' . date('Ymd') . '-' . uniqid(),
                        'type_operation_id' => 3,
                        'client_id' => $cid,
                        'montant' => $montant,
                        'frais_appliques' => $frais,
                        'montant_total' => $total,
                        'sens' => 'debit',
                        'statut' => 'effectuee',
                        'date_transaction' => date('Y-m-d H:i:s'),
                        'description' => 'Transfert vers ' . $clientIds[0],
                    ]);
                    $this->db->table('transactions')->insert([
                        'reference' => 'TXN-' . date('Ymd') . '-' . uniqid(),
                        'type_operation_id' => 3,
                        'client_id' => $clientIds[0],
                        'montant' => $montant,
                        'frais_appliques' => 0,
                        'montant_total' => $montant,
                        'sens' => 'credit',
                        'statut' => 'effectuee',
                        'date_transaction' => date('Y-m-d H:i:s'),
                        'description' => 'Réception de transfert depuis ' . $cid,
                    ]);
                }
            }
        }

        $periodeDebut = date('Y-m-d 00:00:00', strtotime('first day of this month'));
        $periodeFin = date('Y-m-d 23:59:59', strtotime('last day of this month'));
        $fraisRetrait = $this->db->table('transactions')->select('SUM(frais_appliques) as total')->where('type_operation_id', 2)->where('statut', 'effectuee')->get()->getRowArray()['total'] ?? 0;
        if ($fraisRetrait > 0) {
            $this->db->table('gains')->insert([
                'type_operation_id' => 2,
                'montant_total_frais' => $fraisRetrait,
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        $fraisTransfert = $this->db->table('transactions')->select('SUM(frais_appliques) as total')->where('type_operation_id', 3)->where('statut', 'effectuee')->get()->getRowArray()['total'] ?? 0;
        if ($fraisTransfert > 0) {
            $this->db->table('gains')->insert([
                'type_operation_id' => 3,
                'montant_total_frais' => $fraisTransfert,
                'periode_debut' => $periodeDebut,
                'periode_fin' => $periodeFin,
                'created_at' => date('Y-m-d H:i:s'),
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