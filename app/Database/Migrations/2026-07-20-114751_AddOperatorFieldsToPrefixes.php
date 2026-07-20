<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOperatorFieldsToPrefixes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('prefixes_operateur', [
            'est_autre_operateur' => [
                'type' => 'INTEGER',
                'constraint' => 1,
                'default' => 0,
                'after' => 'description',
            ],
            'commission_pourcentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
                'after' => 'est_autre_operateur',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('prefixes_operateur', 'est_autre_operateur');
        $this->forge->dropColumn('prefixes_operateur', 'commission_pourcentage');
    }
}