<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGainsTable extends Migration
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
            'type_operation_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'montant_total_frais' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'periode_debut' => [
                'type' => 'DATETIME',
            ],
            'periode_fin' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('type_operation_id', 'types_operations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('gains');
    }

    public function down()
    {
        $this->forge->dropTable('gains', true);
    }
}