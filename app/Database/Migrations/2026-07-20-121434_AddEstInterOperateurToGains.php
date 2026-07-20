<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEstInterOperateurToGains extends Migration
{
    public function up()
    {
        $this->forge->addColumn('gains', [
            'est_inter_operateur' => [
                'type' => 'INTEGER',
                'constraint' => 1,
                'default' => 0,
                'after' => 'type_operation_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('gains', 'est_inter_operateur');
    }
}