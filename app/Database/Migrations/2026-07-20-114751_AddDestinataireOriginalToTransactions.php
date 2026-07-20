<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDestinataireOriginalToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'destinataire_original' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'description',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', 'destinataire_original');
    }
}