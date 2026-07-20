<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientsTable extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'numero_telephone' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'unique' => true,
            ],
            'nom' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'prenom' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'solde' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'date_creation' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'statut' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'actif',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('numero_telephone');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('clients');
    }

    public function down()
    {
        $this->forge->dropTable('clients', true);
    }
}