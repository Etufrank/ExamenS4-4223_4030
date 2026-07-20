<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBaremesFraisTable extends Migration
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
            'montant_min' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'montant_max' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'frais_fixe' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'frais_pourcentage' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('type_operation_id', 'types_operations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('baremes_frais');
    }

    public function down()
    {
        $this->forge->dropTable('baremes_frais', true);
    }
}