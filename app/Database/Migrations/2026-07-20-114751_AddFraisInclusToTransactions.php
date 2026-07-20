<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFraisInclusToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'frais_inclus' => [
                'type' => 'INTEGER',
                'constraint' => 1,
                'default' => 0,
                'after' => 'frais_appliques',
            ],
            'est_inter_operateur' => [
                'type' => 'INTEGER',
                'constraint' => 1,
                'default' => 0,
                'after' => 'frais_inclus',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'frais_inclus');
        $this->forge->dropColumn('transactions', 'est_inter_operateur');
    }
}