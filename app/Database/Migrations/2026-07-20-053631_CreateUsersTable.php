<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
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
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'client',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('username');
        $this->forge->createTable('users');

        $this->db->query("CREATE TRIGGER check_user_role BEFORE INSERT ON users BEGIN SELECT CASE WHEN NEW.role NOT IN ('admin', 'client') THEN RAISE(ABORT, 'Role invalide') END; END;");
    }

    public function down()
    {
        $this->db->query('DROP TRIGGER IF EXISTS check_user_role');
        $this->forge->dropTable('users', true);
    }
}