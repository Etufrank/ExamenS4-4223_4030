<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
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
            'reference' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'type_operation_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'client_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'montant' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'frais_appliques' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0,
            ],
            'montant_total' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'sens' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'statut' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'effectuee',
            ],
            'date_transaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('reference');
        $this->forge->addForeignKey('type_operation_id', 'types_operations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('client_id', 'clients', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('transactions');

        $this->db->query("CREATE TRIGGER check_transaction_sens BEFORE INSERT ON transactions BEGIN SELECT CASE WHEN NEW.sens NOT IN ('debit', 'credit') THEN RAISE(ABORT, 'Sens invalide') END; END;");
        $this->db->query("CREATE TRIGGER check_transaction_statut BEFORE INSERT ON transactions BEGIN SELECT CASE WHEN NEW.statut NOT IN ('effectuee', 'annulee', 'en_attente') THEN RAISE(ABORT, 'Statut invalide') END; END;");

        $this->db->query('CREATE INDEX idx_transactions_date ON transactions(date_transaction)');
        $this->db->query('CREATE INDEX idx_transactions_client ON transactions(client_id)');
        $this->db->query('CREATE INDEX idx_transactions_type ON transactions(type_operation_id)');
    }

    public function down()
    {
        $this->db->query('DROP TRIGGER IF EXISTS check_transaction_sens');
        $this->db->query('DROP TRIGGER IF EXISTS check_transaction_statut');
        $this->db->query('DROP INDEX IF EXISTS idx_transactions_date');
        $this->db->query('DROP INDEX IF EXISTS idx_transactions_client');
        $this->db->query('DROP INDEX IF EXISTS idx_transactions_type');
        $this->forge->dropTable('transactions', true);
    }
}