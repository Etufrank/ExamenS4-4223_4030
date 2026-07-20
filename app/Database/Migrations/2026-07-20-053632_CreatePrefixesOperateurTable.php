<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrefixesOperateurTable extends Migration
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
            'prefixe' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'unique' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('prefixe');
        $this->forge->createTable('prefixes_operateur');
    }

    public function down()
    {
        $this->forge->dropTable('prefixes_operateur', true);
    }
}