<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEnvoisMultiplesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'transaction_reference' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'montant_total' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'nombre_destinataires' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('envois_multiples');
    }

    public function down()
    {
        $this->forge->dropTable('envois_multiples', true);
    }
}